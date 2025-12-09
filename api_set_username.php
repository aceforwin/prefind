<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum kapalı.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));

    // Basit validasyon
    if (strlen($username) < 3 || strlen($username) > 16) {
        echo json_encode(['status' => 'error', 'message' => 'Kullanıcı adı 3-16 karakter olmalı.']);
        exit;
    }

    try {
        // Benzersizlik kontrolü ve güncelleme
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        
        // Session'ı güncelle
        $_SESSION['username'] = $username;
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry hatası
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı zaten alınmış.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
        }
    }
}
?>