<?php
session_start();
require 'functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: main_menu.php");
    exit;
}

$error = null;

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Semua field harus diisi!";
    } else {
        if (loginUser($email, $password)) {
            // Cek Role
            if ($_SESSION['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: main_menu.php");
            }
            exit;
        } else {
            $error = "Email atau Password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container form-container">
        <h2 style="font-family: 'Cinzel', serif; margin-bottom: 30px; letter-spacing: 2px;">LOGIN</h2>

        <form action="" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required
                    placeholder="Enter your email...">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required
                    placeholder="Enter your password...">
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <button type="submit" name="login" class="btn-cyber" style="width: 100%; margin-top: 20px;">Enter</button>
        </form>

        <a href="main_menu.php" class="back-link">Back to Menu</a>
    </div>
</body>

</html>