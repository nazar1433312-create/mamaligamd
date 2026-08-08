<div class="max-w-lg mx-auto">
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm text-center">
        <h1 class="text-xl font-bold mb-1">🐍 {{ __('Змейка') }}</h1>
        <p class="text-sm text-gray-500 mb-4">{{ __('Скучно? Разомнись, пока ждёшь отклики.') }}</p>

        <div class="flex items-center justify-center gap-6 mb-3 text-sm">
            <div>{{ __('Счёт') }}: <span id="game-score" class="font-bold text-indigo-600">0</span></div>
            <div>{{ __('Рекорд') }}: <span id="game-best" class="font-bold text-gray-700">0</span></div>
        </div>

        <div class="relative inline-block">
            <canvas id="snake-canvas" width="360" height="360" class="rounded-lg border border-gray-200 bg-gray-50 touch-none"></canvas>

            <div id="game-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 rounded-lg">
                <p id="game-overlay-text" class="font-semibold mb-3">{{ __('Нажми, чтобы начать') }}</p>
                <button id="game-start-btn" type="button" class="bg-indigo-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                    ▶️ {{ __('Играть') }}
                </button>
            </div>
        </div>

        <p class="text-xs text-gray-400 mt-3">{{ __('Стрелки / WASD на компьютере, свайпы на телефоне.') }}</p>

        <div class="grid grid-cols-3 gap-2 max-w-[180px] mx-auto mt-4 sm:hidden">
            <div></div>
            <button type="button" data-dir="up" class="game-pad-btn bg-gray-100 rounded-md py-3 text-lg active:bg-gray-200">⬆️</button>
            <div></div>
            <button type="button" data-dir="left" class="game-pad-btn bg-gray-100 rounded-md py-3 text-lg active:bg-gray-200">⬅️</button>
            <button type="button" data-dir="down" class="game-pad-btn bg-gray-100 rounded-md py-3 text-lg active:bg-gray-200">⬇️</button>
            <button type="button" data-dir="right" class="game-pad-btn bg-gray-100 rounded-md py-3 text-lg active:bg-gray-200">➡️</button>
        </div>
    </div>
</div>

<script>
(function () {
    const canvas = document.getElementById('snake-canvas');
    const ctx = canvas.getContext('2d');
    const cell = 20;
    const cols = canvas.width / cell;
    const rows = canvas.height / cell;

    const scoreEl = document.getElementById('game-score');
    const bestEl = document.getElementById('game-best');
    const overlay = document.getElementById('game-overlay');
    const overlayText = document.getElementById('game-overlay-text');
    const startBtn = document.getElementById('game-start-btn');

    const BEST_KEY = 'mamaliga-snake-best';
    let best = parseInt(localStorage.getItem(BEST_KEY) || '0', 10);
    bestEl.textContent = best;

    let snake, direction, nextDirection, food, score, running, loopId;

    function reset() {
        snake = [{ x: Math.floor(cols / 2), y: Math.floor(rows / 2) }];
        direction = { x: 1, y: 0 };
        nextDirection = direction;
        score = 0;
        scoreEl.textContent = '0';
        placeFood();
        draw();
    }

    function placeFood() {
        do {
            food = {
                x: Math.floor(Math.random() * cols),
                y: Math.floor(Math.random() * rows),
            };
        } while (snake.some(s => s.x === food.x && s.y === food.y));
    }

    function tick() {
        direction = nextDirection;
        const head = { x: snake[0].x + direction.x, y: snake[0].y + direction.y };

        if (head.x < 0 || head.y < 0 || head.x >= cols || head.y >= rows || snake.some(s => s.x === head.x && s.y === head.y)) {
            gameOver();
            return;
        }

        snake.unshift(head);

        if (head.x === food.x && head.y === food.y) {
            score += 10;
            scoreEl.textContent = score;
            placeFood();
        } else {
            snake.pop();
        }

        draw();
    }

    function draw() {
        ctx.fillStyle = '#f9fafb';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#f59e0b';
        ctx.fillRect(food.x * cell + 2, food.y * cell + 2, cell - 4, cell - 4);

        snake.forEach((s, i) => {
            ctx.fillStyle = i === 0 ? '#4338ca' : '#6366f1';
            ctx.fillRect(s.x * cell + 1, s.y * cell + 1, cell - 2, cell - 2);
        });
    }

    function start() {
        reset();
        running = true;
        overlay.classList.add('hidden');
        clearInterval(loopId);
        loopId = setInterval(tick, 120);
    }

    function gameOver() {
        running = false;
        clearInterval(loopId);
        if (score > best) {
            best = score;
            localStorage.setItem(BEST_KEY, String(best));
            bestEl.textContent = best;
        }
        overlayText.textContent = '{{ __('Игра окончена') }} — {{ __('Счёт') }}: ' + score;
        startBtn.textContent = '🔁 {{ __('Играть снова') }}';
        overlay.classList.remove('hidden');
    }

    function setDirection(x, y) {
        if (direction.x === -x && direction.y === -y) return; // no 180° turns
        nextDirection = { x, y };
    }

    startBtn.addEventListener('click', start);

    document.addEventListener('keydown', (e) => {
        if (!running && (e.key === 'Enter' || e.key === ' ')) { start(); return; }
        switch (e.key) {
            case 'ArrowUp': case 'w': case 'W': setDirection(0, -1); e.preventDefault(); break;
            case 'ArrowDown': case 's': case 'S': setDirection(0, 1); e.preventDefault(); break;
            case 'ArrowLeft': case 'a': case 'A': setDirection(-1, 0); e.preventDefault(); break;
            case 'ArrowRight': case 'd': case 'D': setDirection(1, 0); e.preventDefault(); break;
        }
    });

    document.querySelectorAll('.game-pad-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const map = { up: [0, -1], down: [0, 1], left: [-1, 0], right: [1, 0] };
            const [x, y] = map[btn.dataset.dir];
            setDirection(x, y);
        });
    });

    let touchStart = null;
    canvas.addEventListener('touchstart', (e) => {
        touchStart = { x: e.touches[0].clientX, y: e.touches[0].clientY };
    }, { passive: true });
    canvas.addEventListener('touchend', (e) => {
        if (!touchStart) return;
        const dx = e.changedTouches[0].clientX - touchStart.x;
        const dy = e.changedTouches[0].clientY - touchStart.y;
        if (Math.abs(dx) > Math.abs(dy)) {
            setDirection(dx > 0 ? 1 : -1, 0);
        } else if (Math.abs(dy) > 10) {
            setDirection(0, dy > 0 ? 1 : -1);
        }
        touchStart = null;
    }, { passive: true });

    reset();
})();
</script>
