<?php

require 'konek.php';

function registerUser($username, $email, $password)
{
    global $conn;

    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $role = 'player';

    $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";

    if (mysqli_query($conn, $query)) {
        $user_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO game_saves (user_id, level) VALUES ('$user_id', 1)");
        return true;
    } else {
        return false;
    }
}


function loginUser($email, $password)
{
    global $conn;

    $email = mysqli_real_escape_string($conn, $email);
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function getGameProgress($user_id)
{
    global $conn;
    $query = "SELECT * FROM game_saves WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function saveGame($user_id, $level, $score)
{
    global $conn;

    $user_id = intval($user_id);
    $level = intval($level);
    $score = intval($score);

    $check_query = "SELECT id FROM game_saves WHERE user_id = $user_id";
    $check = mysqli_query($conn, $check_query);

    if ($check && mysqli_num_rows($check) > 0) {
        $query = "UPDATE game_saves 
                  SET level = $level, score = $score 
                  WHERE user_id = $user_id";
    } else {
        $query = "INSERT INTO game_saves (user_id, level, score) VALUES ($user_id, $level, $score)";
    }

    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        echo " | Error: " . mysqli_error($conn) . " | ";
        return false;
    }
}


function deleteAccount($user_id)
{
    global $conn;

    mysqli_query($conn, "DELETE FROM game_saves WHERE user_id = $user_id");

    $query = "DELETE FROM users WHERE id = $user_id";

    return mysqli_query($conn, $query);
}
?>