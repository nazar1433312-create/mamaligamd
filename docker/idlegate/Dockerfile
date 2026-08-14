FROM golang:1.22-alpine AS build
WORKDIR /src
COPY go.mod main.go waiting.html ./
RUN CGO_ENABLED=0 GOOS=linux go build -o /idlegate .

FROM scratch
COPY --from=build /idlegate /idlegate
# Runs as root: it needs read/write access to the mounted docker.sock, which
# (unlike a container's internal UID) is already root-equivalent on the host
# regardless — the real containment here is the tiny, single-purpose,
# dependency-free codebase, not the process UID.
ENTRYPOINT ["/idlegate"]
