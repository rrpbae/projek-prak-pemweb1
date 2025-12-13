<?php
require 'functions.php';

$error = null;
$success = null;

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        if (registerUser($username, $email, $password)) {
            $success = "Registrasi Berhasil! Silakan Login.";
        } else {
            $error = "Registrasi Gagal! Email mungkin sudah terdaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JeRaDar - Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container form-container">
        <h2 style="font-family: 'Cinzel', serif; margin-bottom: 30px; letter-spacing: 2px;">REGISTER</h2>

        <?php if ($success): ?>
            <div style="color: #4caf50; margin-bottom: 20px;"><?php echo $success; ?></div>
            <a href="login.php" class="btn-cyber" style="width: 100%;">Go to Login</a>
        <?php else: ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required
                        placeholder="Choose a username...">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required
                        placeholder="Enter your email...">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required
                        placeholder="Create a password (min 6 chars)...">
                </div>

                <?php if ($error): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <button type="submit" name="register" class="btn-cyber" style="width: 100%; margin-top: 20px;">Sign
                    Up</button>
            </form>
        <?php endif; ?>

        <a href="main_menu.php" class="back-link">Back to Menu</a>
    </div>
</body>

</html>