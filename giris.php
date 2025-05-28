<?php
session_start();
include 'veritabani.php';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}
$max_attempts = 5;
$lockout_time = 300; 

if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lockout_time) {
    $remaining = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    die("<p style='color:red;'>Çok fazla başarısız deneme. Lütfen $remaining saniye sonra tekrar deneyin.</p>");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Geçersiz CSRF token.");
    }

    $email = htmlspecialchars(trim($_POST["email"]));
    $sifre = htmlspecialchars(trim($_POST["sifre"]));
    $remember = isset($_POST["remember"]);

    $hatalar = [];

    if (empty($email)) {
        $hatalar[] = "E-posta boş bırakılamaz.";
    } elseif (strlen($email) > 50) {
        $hatalar[] = "E-posta en fazla 50 karakter olabilir.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = "Geçersiz e-posta formatı.";
    }

    if (empty($sifre)) {
        $hatalar[] = "Şifre boş bırakılamaz.";
    } elseif (strlen($sifre) > 50) {
        $hatalar[] = "Şifre en fazla 50 karakter olabilir.";
    }

    if (!empty($hatalar)) {
        foreach ($hatalar as $hata) {
            echo "<p style='color:red;'>$hata</p>";
        }
    } else {
        $query = $pdo->prepare("SELECT * FROM uyeler WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($sifre, $user["sifre"])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['user_id'] = $user["id"];
            session_regenerate_id(true); 

            if ($remember) {
                $token = bin2hex(random_bytes(16));
                setcookie('remember_token', $token, time() + (86400 * 30), "/", "", isset($_SERVER["HTTPS"]), true); 
                $query = $pdo->prepare("UPDATE uyeler SET remember_token = ? WHERE id = ?");
                $query->execute([$token, $user["id"]]);
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();

            echo "<p style='color:red;'>Geçersiz email veya şifre!</p>";
        }
    }
}
?>

