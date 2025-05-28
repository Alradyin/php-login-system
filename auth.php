<?php
session_start();
include 'veritabani.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $remember_token = $_COOKIE['remember_token'];

    $query = $pdo->prepare("SELECT * FROM uyeler WHERE remember_token = ?");
    $query->execute([$remember_token]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        session_regenerate_id(true);
    } else {
        setcookie('remember_token', '', time() - 3600, "/");
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function getCurrentUser() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $query = $pdo->prepare("SELECT * FROM uyeler WHERE id = ?");
    $query->execute([$_SESSION['user_id']]);
    return $query->fetch(PDO::FETCH_ASSOC);
}
