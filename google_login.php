<?php
// google_login.php
require_once 'vendor/autoload.php';
require_once 'config.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            die("Google Token Hatası: " . $token['error']);
        }

        $client->setAccessToken($token['access_token']);

        // Google'dan bilgileri al
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $google_id = $google_account_info->id;
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $avatar = $google_account_info->picture;

        // Veritabanı işlemleri
        $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->execute([$google_id]);
        $user = $stmt->fetch();

        if ($user) {
            // DÜZELTME: Giriş yaparken avatarı GÜNCELLEMİYORUZ. 
            // Böylece kullanıcının yüklediği özel resim korunuyor.
            $stmt = $pdo->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->execute([$name, $user['id']]);
            $_SESSION['user_id'] = $user['id'];
            
            // Session'a veritabanındaki güncel avatarı atıyoruz (Google'dan geleni değil)
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['username'] = $user['username'];
        } else {
            // İlk kez kayıt oluyorsa Google avatarını kaydediyoruz
            $stmt = $pdo->prepare("INSERT INTO users (google_id, name, email, avatar) VALUES (?, ?, ?, ?)");
            $stmt->execute([$google_id, $name, $email, $avatar]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['avatar'] = $avatar;
        }
        
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        die("Giriş Hatası: " . $e->getMessage());
    }
} else {
    header("Location: " . $client->createAuthUrl());
    exit;
}
?>