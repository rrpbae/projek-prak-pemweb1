<?php
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Harap login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['mode']) && $_GET['mode'] == 'new') {
    $level = 1;
    $score = 0;
} else {
    $progress = getGameProgress($user_id);
    $level = isset($progress['level']) ? $progress['level'] : 1;
    $score = isset($progress['score']) ? $progress['score'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - In Game</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #health-bar .bar-fill {
            background: linear-gradient(90deg, #999, #eee);
        }

        #stamina-bar .bar-fill {
            background: linear-gradient(90deg, #555, #999);
            width: 70%;
        }

        #save-status {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            background: rgba(0, 50, 0, 0.8);
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            font-family: 'Cinzel', serif;
            z-index: 100;
        }
    </style>
</head>

<body>
    <div id="save-status">Saving...</div>

    <canvas id="gameCanvas" style="position: absolute; top:0; left:0; z-index: 0;"></canvas>

    <div class="game-ui-container">
        <div class="top-bar">
            <div class="status-section">
                <div class="status-label">Health</div>
                <div class="bar-container" id="health-bar">
                    <div class="bar-fill" style="width: 100%;"></div>
                </div>

                <div class="health-val">100%</div>

                <div class="status-label">Stamina</div>
                <div class="bar-container" id="stamina-bar">
                    <div class="bar-fill" style="width: 100%;"></div>
                </div>
            </div>
            <div class="controls-section">
                <div class="control-group" onclick="saveProgress()">
                    <div class="control-btn" title="Save Game" style="font-size: 0.8rem;">💾</div>
                    <div class="control-label">SAVE</div>
                </div>
                <div class="control-group" onclick="togglePause()">
                    <div class="control-btn" title="Menu">≡</div>
                    <div class="control-label">PAUSE</div>
                </div>
            </div>
        </div>
        <div class="bottom-bar">
            <div class="pause-instruction" id="instruction-text">Space / Click to Jump | ESC to Pause</div>
            <a href="main_menu.php" class="demo-menu-btn">[Kembali Ke Menu]</a>
        </div>
    </div>

    <script>
        let currentLevel = <?php echo $level; ?>;
        let currentScore = <?php echo $score; ?>;

        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const healthBar = document.querySelector('#health-bar .bar-fill');
        const staminaBar = document.querySelector('#stamina-bar .bar-fill');
        const healthText = document.querySelector('.health-val');
        const instructionText = document.getElementById('instruction-text');


        let gameRunning = true;
        let health = 100;
        let stamina = 100;
        let score = currentScore;
        let gameSpeed = 5;
        let gravity = 0.6;
        let frame = 0;


        const player = {
            x: 150,
            y: 0,
            width: 30,
            height: 40,
            dy: 0,
            jumpPower: -13,
            grounded: false,
            color: '#111'
        };

        let obstacles = [];
        let particles = [];
        let scenery = [];

        // Resize Canvas
        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            player.y = canvas.height - 150;
        }
        window.addEventListener('resize', resize);
        resize();


        for (let i = 0; i < 20; i++) {
            scenery.push({
                x: Math.random() * canvas.width,
                y: canvas.height - 100 - Math.random() * 50,
                w: Math.random() * 10 + 2,
                h: Math.random() * 100 + 50,
                speed: Math.random() * 0.5 + 0.1, // Slower than foreground
                type: 'tree'
            });
        }


        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space') jump();
            if (e.code === 'Escape') togglePause();
        });
        document.addEventListener('touchstart', jump);
        document.addEventListener('mousedown', jump);

        function jump() {
            if (!gameRunning) return;
            if (player.grounded && stamina > 15) {
                player.dy = player.jumpPower;
                player.grounded = false;
                stamina -= 15;
                createParticles(player.x + 15, player.y + 40, 5, '#555');
                updateUI();
            }
        }

        function togglePause() {
            if (health <= 0) return;

            gameRunning = !gameRunning;
            if (gameRunning) {
                instructionText.innerText = "Space / Click to Jump | ESC to Pause";
                loop();
            } else {
                instructionText.innerText = "paused";
                ctx.fillStyle = "rgba(0,0,0,0.7)";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = "#fff";
                ctx.font = "30px Cinzel";
                ctx.textAlign = "center";
                ctx.fillText("PAUSED", canvas.width / 2, canvas.height / 2);
            }
        }

        function createParticles(x, y, amount, color) {
            for (let i = 0; i < amount; i++) {
                particles.push({
                    x: x,
                    y: y,
                    vx: (Math.random() - 0.5) * 4,
                    vy: (Math.random() - 0.5) * 4,
                    life: 1.0,
                    color: color
                });
            }
        }


        function loop() {
            if (!gameRunning) return;

            frame++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);


            const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
            gradient.addColorStop(0, '#2d3748');
            gradient.addColorStop(1, '#1a202c');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);


            ctx.fillStyle = 'rgba(255,255,255,0.05)';
            ctx.beginPath();
            ctx.arc(canvas.width / 2, canvas.height / 2, 400, 0, Math.PI * 2);
            ctx.fill();


            ctx.fillStyle = '#171923';
            scenery.forEach(item => {
                item.x -= item.speed;
                if (item.x + item.w < 0) item.x = canvas.width + Math.random() * 200;
                ctx.fillRect(item.x, item.y, item.w, item.h);
                if (item.type === 'tree' && Math.random() > 0.99) {

                }
            });


            const groundY = canvas.height - 100;
            ctx.fillStyle = '#000';
            ctx.fillRect(0, groundY, canvas.width, 100);


            player.dy += gravity;
            player.y += player.dy;


            if (player.y + player.height > groundY) {
                player.y = groundY - player.height;
                player.dy = 0;
                player.grounded = true;
                if (frame % 10 === 0) createParticles(player.x, player.y + 40, 1, '#333');
            }


            ctx.fillStyle = '#111';
            ctx.fillRect(player.x, player.y, player.width, player.height);

            ctx.fillStyle = '#fff';
            ctx.shadowBlur = 10;
            ctx.shadowColor = '#fff';
            if (Math.random() > 0.05) {
                ctx.fillRect(player.x + 20, player.y + 10, 3, 3);
                ctx.fillRect(player.x + 25, player.y + 10, 3, 3);
            }
            ctx.shadowBlur = 0;
            let spawnRate = 120;
            if (score > 500) spawnRate = 90;
            if (score > 1500) spawnRate = 60;

            if (frame % spawnRate === 0) {
                let type = Math.random();
                let obs = {
                    x: canvas.width,
                    y: groundY - 40,
                    width: 40,
                    height: 40,
                    type: 'box',
                    rotation: 0
                };

                if (type > 0.6) {
                    obs.type = 'spike';
                    obs.y = groundY - 30;
                    obs.width = 30;
                } else if (type > 0.3) {
                    obs.type = 'blade';
                    obs.y = groundY - 80;
                }

                obstacles.push(obs);
            }

            for (let i = 0; i < obstacles.length; i++) {
                let obs = obstacles[i];
                obs.x -= gameSpeed;

                ctx.fillStyle = '#000';
                ctx.strokeStyle = '#333';
                ctx.lineWidth = 1;

                if (obs.type === 'spike') {
                    ctx.beginPath();
                    ctx.moveTo(obs.x, obs.y + 30);
                    ctx.lineTo(obs.x + 15, obs.y);
                    ctx.lineTo(obs.x + 30, obs.y + 30);
                    ctx.fill();
                    ctx.stroke();
                } else if (obs.type === 'blade') {
                    obs.rotation += 0.1;
                    ctx.save();
                    ctx.translate(obs.x + 20, obs.y + 20);
                    ctx.rotate(obs.rotation);
                    ctx.fillRect(-20, -5, 40, 10);
                    ctx.fillRect(-5, -20, 10, 40);
                    ctx.restore();
                } else {
                    ctx.fillRect(obs.x, obs.y, obs.width, obs.height);
                    ctx.strokeRect(obs.x, obs.y, obs.width, obs.height);
                }

                if (
                    player.x < obs.x + obs.width &&
                    player.x + player.width > obs.x &&
                    player.y < obs.y + obs.height &&
                    player.y + player.height > obs.y
                ) {
                    health -= 34;
                    createParticles(player.x, player.y, 20, '#ff0000');
                    obstacles.splice(i, 1);
                    i--;
                    updateUI();

                    canvas.style.transform = `translate(${Math.random() * 20 - 10}px, ${Math.random() * 20 - 10}px)`;
                    setTimeout(() => canvas.style.transform = 'translate(0,0)', 100);

                    if (health <= 0) {
                        endGame();
                        return;
                    }
                }

                if (obs.x + obs.width < 0) {
                    obstacles.splice(i, 1);
                    i--;
                    score += 10;
                    currentScore = score;
                    if (score % 500 === 0) gameSpeed += 0.5;
                }
            }
            for (let i = 0; i < particles.length; i++) {
                let p = particles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.life -= 0.05;

                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.life;
                ctx.fillRect(p.x, p.y, 3, 3);
                ctx.globalAlpha = 1.0;

                if (p.life <= 0) {
                    particles.splice(i, 1);
                    i--;
                }
            }

            if (stamina < 100 && frame % 5 === 0) {
                stamina += 0.5;
                updateUI();
            }


            ctx.fillStyle = '#666';
            ctx.font = '20px Cinzel';
            ctx.fillText('Score: ' + score, 40, canvas.height - 40);

            requestAnimationFrame(loop);
        }

        function endGame() {
            gameRunning = false;
            ctx.fillStyle = "rgba(0,0,0,0.9)";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = "#fff";
            ctx.font = "60px Cinzel";
            ctx.textAlign = "center";
            ctx.shadowBlur = 20;
            ctx.shadowColor = "#fff";
            ctx.fillText("WASTED", canvas.width / 2, canvas.height / 2);
            ctx.shadowBlur = 0;

            ctx.font = "20px Cinzel";
            ctx.fillStyle = "#aaa";
            ctx.fillText("Final Score: " + score, canvas.width / 2, canvas.height / 2 + 60);
            ctx.fillText("Click to Restart", canvas.width / 2, canvas.height / 2 + 100);

            canvas.addEventListener('click', () => location.reload(), { once: true });
        }


        function updateUI() {
            healthBar.style.width = health + '%';
            healthText.innerText = Math.floor(health) + '%';
            staminaBar.style.width = stamina + '%';
        }


        loop();

        function saveProgress() {
            const statusDiv = document.getElementById('save-status');
            statusDiv.style.display = 'block';
            statusDiv.innerText = 'Saving...';
            statusDiv.style.background = 'rgba(0, 50, 0, 0.8)';

            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('level', currentLevel);
            formData.append('score', score);

            fetch('game_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    statusDiv.innerText = data;
                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.innerText = 'Error Saving!';
                    statusDiv.style.background = 'rgba(50, 0, 0, 0.8)';
                });
        }
    </script>
</body>

</html>