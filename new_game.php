<?php
session_start();
// This page is accessible always, or strictly for logged in?
// Usually New Game implies a signed in user for saves, but for guest it might redirect to login later.
// For now, I'll allow access but checks might happen on click.
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
            <!-- New Game Card -->
            <!-- Linking to actual game view now -->
            <a href="game.php?mode=new" class="mode-card">
                <span>NEW GAME</span>
            </a>

            <!-- Load Game Card -->
            <a href="saves.php" class="mode-card">
                <span>LOAD GAME</span>
            </a>
        </div>

        <a href="main_menu.php" class="back-link-bottom">Back to Menu</a>
    </div>
</body>

</html>