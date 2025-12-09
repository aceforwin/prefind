<?php
// api_add_listing.php
require_once 'config.php';

header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// --- COOLDOWN KONTROLÜ (Backend) ---
// Kullanıcının son attığı ilanın zamanını çek
$stmtLast = $pdo->prepare("SELECT created_at FROM listings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmtLast->execute([$user_id]);
$lastPost = $stmtLast->fetch();

if ($lastPost) {
    // Şu anki zaman ile son ilan zamanı arasındaki farkı hesapla
    $secondsSinceLast = time() - strtotime($lastPost['created_at']);
    
    // Eğer 120 saniyeden (2 dakika) azsa hata döndür
    if ($secondsSinceLast < 120) { 
        $remaining = 120 - $secondsSinceLast;
        echo json_encode(['status' => 'error', 'message' => "Çok hızlı gidiyorsun! Yeni ilan için $remaining saniye beklemelisin."]);
        exit;
    }
}

// İlan Ekleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gelen verileri temizle
    $riot_id = htmlspecialchars(trim($_POST['riot_id']));
    $min_rank = htmlspecialchars(trim($_POST['min_rank']));
    $max_rank = htmlspecialchars(trim($_POST['max_rank']));
    $lobby_code = isset($_POST['lobby_code']) ? htmlspecialchars(trim($_POST['lobby_code'])) : null;
    $note = htmlspecialchars(trim($_POST['note']));

    // Zorunlu alan kontrolü
    if (empty($riot_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Riot ID zorunludur.']);
        exit;
    }

    try {
        // Veritabanına ekle
        $stmt = $pdo->prepare("INSERT INTO listings (user_id, riot_id, min_rank, max_rank, lobby_code, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $riot_id, $min_rank, $max_rank, $lobby_code, $note]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası oluştu.']);
    }
}
?>