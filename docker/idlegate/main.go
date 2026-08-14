// idlegate is a tiny, single-purpose sidecar: it starts the "app" container
// on the first request after it was stopped, and stops it again after a
// period with no traffic. It talks to the Docker Engine API over the
// mounted docker.sock — that's the only privileged thing in this whole
// setup, kept intentionally small and auditable (a few hundred lines,
// zero third-party dependencies) rather than giving that access to nginx
// itself or a larger general-purpose tool.
package main

import (
	"context"
	"encoding/json"
	"fmt"
	_ "embed"
	"io"
	"log"
	"net"
	"net/http"
	"net/url"
	"os"
	"sync"
	"time"
)

//go:embed waiting.html
var waitingPage []byte

var (
	dockerSock    = getenv("DOCKER_SOCK", "/var/run/docker.sock")
	serviceLabel  = getenv("SERVICE_LABEL", "com.docker.compose.service=app")
	heartbeatPath = getenv("HEARTBEAT_PATH", "/var/run/idle/heartbeat.log")
	listenAddr    = getenv("LISTEN_ADDR", ":8080")
	idleTimeout   = getDuration("IDLE_TIMEOUT", 10*time.Minute)
	checkInterval = getDuration("CHECK_INTERVAL", 30*time.Second)
	startCooldown = getDuration("START_COOLDOWN", 15*time.Second)

	mu          sync.Mutex
	lastStartAt time.Time
)

func getenv(key, def string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return def
}

func getDuration(key string, def time.Duration) time.Duration {
	if v := os.Getenv(key); v != "" {
		if d, err := time.ParseDuration(v); err == nil {
			return d
		}
		log.Printf("invalid duration for %s=%q, using default %s", key, v, def)
	}
	return def
}

var dockerClient = &http.Client{
	Transport: &http.Transport{
		DialContext: func(ctx context.Context, _, _ string) (net.Conn, error) {
			var d net.Dialer
			return d.DialContext(ctx, "unix", dockerSock)
		},
	},
	Timeout: 5 * time.Second,
}

func dockerRequest(method, path string) (*http.Response, error) {
	req, err := http.NewRequest(method, "http://docker"+path, nil)
	if err != nil {
		return nil, err
	}
	return dockerClient.Do(req)
}

type containerInfo struct {
	ID    string `json:"Id"`
	State string `json:"State"`
}

// findContainer locates the target container by its docker-compose service
// label rather than a hardcoded name, so this keeps working even if the
// compose project directory (and therefore the generated container name)
// ever changes.
func findContainer() (containerInfo, error) {
	filters := fmt.Sprintf(`{"label":["%s"]}`, serviceLabel)
	q := url.Values{}
	q.Set("all", "true")
	q.Set("filters", filters)

	resp, err := dockerRequest("GET", "/containers/json?"+q.Encode())
	if err != nil {
		return containerInfo{}, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		body, _ := io.ReadAll(resp.Body)
		return containerInfo{}, fmt.Errorf("docker API /containers/json: %s: %s", resp.Status, body)
	}

	var containers []containerInfo
	if err := json.NewDecoder(resp.Body).Decode(&containers); err != nil {
		return containerInfo{}, err
	}
	if len(containers) == 0 {
		return containerInfo{}, fmt.Errorf("no container found with label %s", serviceLabel)
	}
	return containers[0], nil
}

func startContainer(id string) error {
	resp, err := dockerRequest("POST", "/containers/"+id+"/start")
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	// 204 = started, 304 = already running — both fine.
	if resp.StatusCode != http.StatusNoContent && resp.StatusCode != http.StatusNotModified {
		body, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("docker API start: %s: %s", resp.Status, body)
	}
	return nil
}

func stopContainer(id string) error {
	resp, err := dockerRequest("POST", "/containers/"+id+"/stop?t=10")
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	if resp.StatusCode != http.StatusNoContent && resp.StatusCode != http.StatusNotModified {
		body, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("docker API stop: %s: %s", resp.Status, body)
	}
	return nil
}

// waitingHandler is hit via nginx's error_page redirect whenever the app
// upstream is unreachable (i.e. the container is asleep). It kicks off an
// async start and immediately returns the waiting page with a 503 — a
// non-2xx status is important here, not just cosmetic: it tells well-behaved
// clients (browsers AND Telegram's webhook delivery) "try again shortly"
// instead of "request handled", so e.g. an incoming Telegram update isn't
// silently swallowed by this placeholder response.
func waitingHandler(w http.ResponseWriter, r *http.Request) {
	go wake()

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.Header().Set("Retry-After", "3")
	w.Header().Set("Cache-Control", "no-store")
	w.WriteHeader(http.StatusServiceUnavailable)
	_, _ = w.Write(waitingPage)
}

func wake() {
	mu.Lock()
	if time.Since(lastStartAt) < startCooldown {
		mu.Unlock()
		return
	}
	lastStartAt = time.Now()
	mu.Unlock()

	c, err := findContainer()
	if err != nil {
		log.Printf("wake: %v", err)
		return
	}
	if c.State == "running" {
		return
	}
	if err := startContainer(c.ID); err != nil {
		log.Printf("wake: start failed: %v", err)
		return
	}
	log.Printf("wake: started container %s", c.ID)
}

// idleLoop periodically checks how long it's been since the last real
// request (tracked via the nginx heartbeat log's mtime — see default.conf)
// and stops the app container once it's been idle past the timeout.
func idleLoop() {
	ticker := time.NewTicker(checkInterval)
	defer ticker.Stop()

	for range ticker.C {
		info, err := os.Stat(heartbeatPath)
		if err != nil {
			// No traffic yet since boot, or log not created — leave it running.
			continue
		}

		if time.Since(info.ModTime()) < idleTimeout {
			continue
		}

		c, err := findContainer()
		if err != nil {
			log.Printf("idleLoop: %v", err)
			continue
		}
		if c.State != "running" {
			continue
		}

		if err := stopContainer(c.ID); err != nil {
			log.Printf("idleLoop: stop failed: %v", err)
			continue
		}
		log.Printf("idleLoop: stopped container %s after %s idle", c.ID, idleTimeout)
	}
}

func main() {
	log.Printf("idlegate starting: label=%s idle_timeout=%s check_interval=%s heartbeat=%s",
		serviceLabel, idleTimeout, checkInterval, heartbeatPath)

	go idleLoop()

	http.HandleFunc("/", waitingHandler)

	if err := http.ListenAndServe(listenAddr, nil); err != nil {
		log.Fatal(err)
	}
}
