<?php
session_start();
require 'konek.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: main_menu.php");
    exit;
}

$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 5px;
            overflow: hidden;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ccc;
        }

        th {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 2px;
            font-family: 'Cinzel', serif;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container" style="width: 90%; max-width: 1000px;">
        <h2 style="font-family: 'Cinzel', serif; margin-bottom: 30px; letter-spacing: 2px;">ADMIN CONTROL</h2>

        <div style="background: rgba(0,0,0,0.5); padding: 20px; border: 1px solid rgba(255,255,255,0.1);">
            <h3 style="font-family: 'Cinzel', serif; color: #ddd;">User Database</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo $row['role']; ?></td>
                            <td>
                                <?php if ($row['role'] !== 'admin'): ?>
                                    <a href="game_action.php?action=delete_user&id=<?php echo $row['id']; ?>"
                                        onclick="confirmAction(event, 'game_action.php?action=delete_user&id=<?php echo $row['id']; ?>', 'WARNING: Ini akan menghapus akun player <?php echo htmlspecialchars($row['username']); ?> beserta SAVE GAME mereka. Tindakan ini tidak dapat dibatalkan.');"
                                        style="color: #ff6b6b; text-decoration: none; font-size: 0.9rem;">
                                        [DELETE]
                                    </a>
                                <?php else: ?>
                                    <span style="color: #666;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px;">
            <a href="main_menu.php" class="back-link" style="font-size: 1rem;">Back to Main Menu</a>
            <a href="logout.php" class="back-link" style="font-size: 1rem; color: #ff6b6b;"
                onclick="confirmAction(event, 'logout.php', 'Apakah anda yakin ingin Logout?');">Logout</a>
        </div>
    </div>

    <div id="confirmation-modal" class="modal-overlay">
        <div class="custom-modal">
            <div class="modal-title">WARNING</div>
            <div class="modal-message" id="modal-msg-text">Are you sure?</div>
            <div class="modal-actions">
                <button class="modal-btn" onclick="closeModal()">CANCEL</button>
                <button class="modal-btn confirm" id="modal-confirm-btn">EXECUTE</button>
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