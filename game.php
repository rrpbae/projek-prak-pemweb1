<?php
require 'functions.php';

// Enforce Login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Harap login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if starting a new game
if (isset($_GET['mode']) && $_GET['mode'] == 'new') {
    $level = 1;
    $score = 0;
} else {
    // Load existing save
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
        /* Additional gradient for the health bar as per image hint */
        #health-bar .bar-fill {
            background: linear-gradient(90deg, #999, #eee);
        }

        #stamina-bar .bar-fill {
            background: linear-gradient(90deg, #555, #999);
            width: 70%;
            /* Initial state from image */
        }

        /* Save Status Notification */
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

    <!-- Limbo-like Game Canvas -->
    <canvas id="gameCanvas" style="position: absolute; top:0; left:0; z-index: 0;"></canvas>

    <div class="game-ui-container">
        <!-- Top Bar -->
        <div class="top-bar">
            <!-- Status Section (Top Left) -->
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

            <!-- Controls Section (Top Right) -->
            <div class="controls-section">
                <!-- Removed simulation toggles as they are not relevant to active gameplay anymore -->
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

        <!-- Bottom Bar -->
        <div class="bottom-bar">
            <div class="pause-instruction" id="instruction-text">Space / Click to Jump | ESC to Pause</div>
            <a href="main_menu.php" class="demo-menu-btn">[Kembali Ke Menu]</a>
        </div>
    </div>

    <script>
        // Game State from PHP
        let currentLevel = <?php echo $level; ?>;
        let currentScore = <?php echo $score; ?>;

        // Visual Elements
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const healthBar = document.querySelector('#health-bar .bar-fill');
        const staminaBar = document.querySelector('#stamina-bar .bar-fill');
        const healthText = document.querySelector('.health-val');
        const instructionText = document.getElementById('instruction-text');

        // Game Variables
        let gameRunning = true;
        let health = 100;
        let stamina = 100;
        let score = currentScore;
        let gameSpeed = 5;
        let gravity = 0.6;
        let frame = 0;

        // Player Object
        const player = {
            x: 150,
            y: 0,
            width: 30,
            height: 40, // Taller player
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

        // Parallax Scenery Init
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

        // Input
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
                createParticles(player.x + 15, player.y + 40, 5, '#555'); // Dust jump
                updateUI();
            }
        }

        function togglePause() {
            if (health <= 0) return; // Prevent unpausing if dead

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

        // Game Loop
        function loop() {
            if (!gameRunning) return;

            frame++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 1. Draw Background (Parallax) - LIGHTER
            const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
            gradient.addColorStop(0, '#2d3748'); // Brighter dark/grey
            gradient.addColorStop(1, '#1a202c');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Draw Distant Fog - BRIGHTER
            ctx.fillStyle = 'rgba(255,255,255,0.05)';
            ctx.beginPath();
            ctx.arc(canvas.width / 2, canvas.height / 2, 400, 0, Math.PI * 2);
            ctx.fill();

            // Draw Scenery (Trees/Pillars in background)
            ctx.fillStyle = '#171923'; // Visible silhouette
            scenery.forEach(item => {
                item.x -= item.speed;
                if (item.x + item.w < 0) item.x = canvas.width + Math.random() * 200;

                // Draw spooky tree/pillar
                ctx.fillRect(item.x, item.y, item.w, item.h);
                // Branches
                if (item.type === 'tree' && Math.random() > 0.99) {
                    // small detail
                }
            });

            // 2. Draw Ground
            const groundY = canvas.height - 100;
            ctx.fillStyle = '#000'; // Pure black foreground
            ctx.fillRect(0, groundY, canvas.width, 100);

            // 3. Player Logic
            player.dy += gravity;
            player.y += player.dy;

            // Ground Collision
            if (player.y + player.height > groundY) {
                player.y = groundY - player.height;
                player.dy = 0;
                player.grounded = true;
                // Running particles
                if (frame % 10 === 0) createParticles(player.x, player.y + 40, 1, '#333');
            }

            // Draw Player
            ctx.fillStyle = '#111'; // Almost black
            ctx.fillRect(player.x, player.y, player.width, player.height);

            // Eyes (The Soul)
            ctx.fillStyle = '#fff';
            ctx.shadowBlur = 10;
            ctx.shadowColor = '#fff';
            if (Math.random() > 0.05) {
                ctx.fillRect(player.x + 20, player.y + 10, 3, 3);
                ctx.fillRect(player.x + 25, player.y + 10, 3, 3);
            }
            ctx.shadowBlur = 0;

            // 4. Obstacle Logic
            // Spawn Rate Logic
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
                    obs.y = groundY - 80; // Flying hazard
                }

                obstacles.push(obs);
            }

            for (let i = 0; i < obstacles.length; i++) {
                let obs = obstacles[i];
                obs.x -= gameSpeed;

                ctx.fillStyle = '#000'; // Obstacles are also silhouettes
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

                // Collision
                if (
                    player.x < obs.x + obs.width &&
                    player.x + player.width > obs.x &&
                    player.y < obs.y + obs.height &&
                    player.y + player.height > obs.y
                ) {
                    health -= 34; // 3 hits effectively
                    createParticles(player.x, player.y, 20, '#ff0000'); // Blood/Spark
                    obstacles.splice(i, 1);
                    i--;
                    updateUI();

                    // Camera Shake
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

                    // Speed up slightly over time
                    if (score % 500 === 0) gameSpeed += 0.5;
                }
            }

            // 5. Particles Logic
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

            // Stamina Regen
            if (stamina < 100 && frame % 5 === 0) {
                stamina += 0.5; // Slower regen
                updateUI();
            }

            // UI Text
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

        // Update HTML UI
        function updateUI() {
            healthBar.style.width = health + '%';
            healthText.innerText = Math.floor(health) + '%';
            staminaBar.style.width = stamina + '%';
        }

        // Start
        loop();

        // AJAX Save Function
        function saveProgress() {
            const statusDiv = document.getElementById('save-status');
            statusDiv.style.display = 'block';
            statusDiv.innerText = 'Saving...';
            statusDiv.style.background = 'rgba(0, 50, 0, 0.8)';

            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('level', currentLevel);
            formData.append('score', score); // Use live game score

            fetch('game_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    statusDiv.innerText = data; // Show server response
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