<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Kullanıcı adı veya Gerçek isme göre ara (Limit 5)
    $stmt = $pdo->prepare("SELECT id, username, name, avatar FROM users WHERE username LIKE :q OR name LIKE :q LIMIT 5");
    $stmt->execute([':q' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Avatar yoksa varsayılan ata (Frontend'de kırık resim olmasın)
    foreach ($results as &$user) {
        if (empty($user['avatar'])) {
            $user['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($user['name']);
        }
        // Görünen isim belirle
        $user['display_name'] = !empty($user['username']) ? $user['username'] : $user['name'];
    }

    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>