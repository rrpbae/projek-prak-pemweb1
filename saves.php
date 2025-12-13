<?php
session_start();
require 'konek.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM game_saves WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Load Game</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="width: 80%; max-width: 800px;">
        <h2 style="font-family: 'Cinzel', serif; margin-bottom: 30px; letter-spacing: 2px;">SAVED GAMES</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Save ID</th>
                        <th>Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>Level <?php echo $row['level']; ?></td>
                            <td>
                                <a href="game.php" class="btn-cyber"
                                    style="width: auto; display: inline-block; padding: 5px 15px; font-size: 0.8rem; margin: 0;">Load</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #aaa;">No saved games found.</p>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="main_menu.php" class="back-link" style="font-size: 1rem;">Back to Menu</a>
        </div>
    </div>
</body>

</html>