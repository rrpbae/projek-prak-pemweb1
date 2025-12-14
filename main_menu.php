<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Menu</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div
            style="position: absolute; top: 20px; left: 20px; color: #8b9bb4; font-family: 'Cinzel', serif; font-size: 1rem; letter-spacing: 2px; z-index: 100;">
            PLAYER: <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
    <?php endif; ?>
    <div class="container">
        <h1 class="game-title">JeRaDar</h1>

        <div class="menu-items">
            <a href="new_game.php" class="btn-cyber">New Game</a>
            <a href="saves.php" class="btn-cyber">Load Game</a>

            <?php if (isset($_SESSION['user_id'])): ?>
              
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn-cyber">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-cyber"
                    onclick="confirmAction(event, 'logout.php', 'Apakah anda yakin ingin Logout?');">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-cyber">Login</a>
                <a href="register.php" class="btn-cyber">Register</a>
            <?php endif; ?>

            <a href="index.php" class="btn-cyber"
                onclick="confirmAction(event, 'index.php', 'Apakah anda yakin ingin Keluar dari permainan?');">Exit</a>
        </div>
    </div>
    <div id="confirmation-modal" class="modal-overlay">
        <div class="custom-modal">
            <div class="modal-title">CONFIRMATION</div>
            <div class="modal-message" id="modal-msg-text">Are you sure?</div>
            <div class="modal-actions">
                <button class="modal-btn" onclick="closeModal()">CANCEL</button>
                <button class="modal-btn confirm" id="modal-confirm-btn">YES</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('confirmation-modal');
        const msgText = document.getElementById('modal-msg-text');
        const confirmBtn = document.getElementById('modal-confirm-btn');

        function confirmAction(e, url, message) {
            e.preventDefault();
            msgText.innerText = message;

            confirmBtn.onclick = function () {
                window.location.href = url;
            };

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }
    </script>
</body>

</html>