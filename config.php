<?php
// config.php
session_start();

// Veritabanı Ayarları
$host = 'localhost';
$db   = 'prefind';
$user = 'root';
$pass = ''; // XAMPP varsayılan şifresi boştur
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Google API Ayarları
// BURAYA ALDIĞIN KODLARI YAPIŞTIR
define('GOOGLE_CLIENT_ID', '877365127962-7keoio4mhomusogn5rj7muo3udpbfaf5.apps.googleusercontent.com'); 
define('GOOGLE_CLIENT_SECRET', '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'); 
define('GOOGLE_REDIRECT_URL', 'http://localhost/prefind/google_login.php');

?>