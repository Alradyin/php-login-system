<?php
try {
    $baglanti = new PDO("mysql:host=localhost;dbname=veritabaniadi;charset=utf8", "kullaniciadi", "sifre");
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
