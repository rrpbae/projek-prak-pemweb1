<?php
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $current_level = $_POST['level'];
    $current_score = $_POST['score'];

    if (saveGame($user_id, $current_level, $current_score)) {
        echo "Saved! Lv: " . $current_level . " | Sc: " . $current_score;
    } else {
        echo "Save Failed!";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_user') {
    // Cek apakah yang melakukan request adalah admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Hanya admin yang bisa menghapus akun.");
    }

    if (isset($_GET['id'])) {
        $target_id = intval($_GET['id']);

        // Mencegah admin menghapus diri sendiri melalui link ini (opsional)
        if ($target_id == $_SESSION['user_id']) {
            echo "<script>alert('Anda tidak bisa menghapus akun admin yang sedang login melalui menu ini.'); window.location='admin_dashboard.php';</script>";
            exit;
        }

        if (deleteAccount($target_id)) {
            echo "<script>alert('Akun player dan save game berhasil dihapus.'); window.location='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus akun.'); window.location='admin_dashboard.php';</script>";
        }
    }
}
?>