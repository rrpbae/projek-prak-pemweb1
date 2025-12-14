<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Choose Mode</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1 class="game-title" style="font-size: 4rem; letter-spacing: 0.8rem;">CHOOSE MODE</h1>

        <div class="mode-selection">
            <a href="game.php?mode=new" class="mode-card"
                onclick="confirmAction(event, 'game.php?mode=new', 'WARNING: Starting a new game will overwrite your existing save. Continue?');">
                <span>NEW GAME</span>
            </a>

            <a href="saves.php" class="mode-card">
                <span>LOAD GAME</span>
            </a>
        </div>

        <a href="main_menu.php" class="back-link-bottom">Back to Menu</a>
    </div>
    <div id="confirmation-modal" class="modal-overlay">
        <div class="custom-modal">
            <div class="modal-title">NEW GAME</div>
            <div class="modal-message" id="modal-msg-text">Start a fresh journey? Your previous progress will be reset.
            </div>
            <div class="modal-actions">
                <button class="modal-btn" onclick="closeModal()">CANCEL</button>
                <button class="modal-btn confirm" id="modal-confirm-btn">START</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('confirmation-modal');
        const msgText = document.getElementById('modal-msg-text');
        const confirmBtn = document.getElementById('modal-confirm-btn');
        const modalTitle = document.querySelector('.modal-title');
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        function confirmAction(e, url, message) {
            e.preventDefault();

            if (!isLoggedIn) {
                modalTitle.innerText = "ACCESS DENIED";
                msgText.innerText = "You must be logged in to enter the game world.";
                confirmBtn.innerText = "LOGIN";
                confirmBtn.onclick = function () {
                    window.location.href = 'login.php';
                };
            } else {
                modalTitle.innerText = "NEW GAME";
                msgText.innerText = message;
                confirmBtn.innerText = "START";
                confirmBtn.onclick = function () {
                    window.location.href = url;
                };
            }

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