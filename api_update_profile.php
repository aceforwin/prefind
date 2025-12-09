<?php
require_once 'config.php';
header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum kapalı.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // Formdan gelen metin verilerini al
    $tracker = filter_input(INPUT_POST, 'tracker_link', FILTER_SANITIZE_URL);
    $discord = htmlspecialchars(trim($_POST['discord_username']));
    
    // Kullanıcının mevcut avatarını çek
    $stmtUser = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $currentUser = $stmtUser->fetch();
    $avatarPath = $currentUser['avatar'];

    // --- DOSYA YÜKLEME İŞLEMİ ---
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar_file'];
        
        // İzin verilen MIME tipleri
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/pjpeg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Sadece JPG, PNG ve JFIF formatları kabul edilir.']);
            exit;
        }

        // Boyut kontrolü (2MB)
        if ($file['size'] > 2 * 1024 * 1024) { 
            echo json_encode(['status' => 'error', 'message' => 'Dosya boyutu 2MB\'ı geçmemeli.']);
            exit;
        }

        // Klasör Tanımlamaları - TAM YOL KULLANIMI
        $relativeDir = 'uploads/'; 
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR; 
        
        // Klasör kontrolü (Yoksa oluştur)
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Uploads klasörü oluşturulamadı. Lütfen manuel olarak "uploads" klasörü oluşturun.']);
                exit;
            }
        }

        // Klasör yazılabilir mi kontrolü ve İZİN DÜZELTME
        if (!is_writable($uploadDir)) {
             // PHP ile izinleri düzeltmeyi dene
             @chmod($uploadDir, 0777);
             
             // Tekrar kontrol et, hala yazamıyorsa kullanıcıya komutu ver
             if (!is_writable($uploadDir)) {
                 echo json_encode([
                     'status' => 'error', 
                     'message' => 'Mac izni gerekiyor! Terminali açıp şunu yapıştırın: sudo chmod -R 777 ' . $uploadDir
                 ]);
                 exit;
             }
        }

        // Dosya uzantısını al
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'jfif'];
        
        if (!in_array($ext, $allowedExts)) {
             echo json_encode(['status' => 'error', 'message' => 'Geçersiz dosya uzantısı.']);
             exit;
        }

        // Benzersiz dosya adı
        $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        
        $serverPath = $uploadDir . $filename; // Sunucudaki tam yol
        $webPath = $relativeDir . $filename;  // Web sitesinde görünecek yol (uploads/resim.jpg)

        // Dosyayı taşı
        if (move_uploaded_file($file['tmp_name'], $serverPath)) {
            $avatarPath = $webPath; // Veritabanına web yolunu kaydet
        } else {
            $error = error_get_last();
            echo json_encode([
                'status' => 'error', 
                'message' => 'Dosya taşınamadı. Klasör izinlerini kontrol edin.',
                'debug_source' => $file['tmp_name'],
                'debug_dest' => $serverPath,
                'php_error' => $error
            ]);
            exit;
        }
    }

    // --- VERİTABANI GÜNCELLEME ---
    try {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ?, tracker_link = ?, discord_username = ? WHERE id = ?");
        $stmt->execute([$avatarPath, $tracker, $discord, $userId]);
        
        $_SESSION['avatar'] = $avatarPath;
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı güncelleme hatası.']);
    }
}
?>