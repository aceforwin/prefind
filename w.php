<?php
require_once 'config.php';

// Eğer kullanıcı zaten giriş yapmışsa direkt ana sayfaya at
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefind - Oyuncu Bul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Space Grotesk', 'sans-serif'] },
                    colors: {
                        brand: {
                            dark: '#09090b',
                            blue: '#3b82f6',
                            valRed: '#ff4655'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #050505;
            color: #fff;
            background-image: radial-gradient(circle at 50% 0%, rgba(255, 70, 85, 0.1), transparent 60%);
        }
        .game-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(18, 18, 21, 0.6);
            backdrop-filter: blur(10px);
        }
        .game-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .disabled-card {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(100%);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">

    <!-- Dekoratif Arka Plan -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-valRed/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px]"></div>
    </div>

    <!-- Logo Area -->
    <div class="mb-12 text-center relative z-10">
        <div class="flex items-center justify-center gap-3 mb-4">
            <div class="w-16 h-16 bg-white text-black rounded-2xl flex items-center justify-center shadow-2xl shadow-brand-valRed/20 transform -rotate-3">
                <i class="fa-solid fa-crosshairs text-3xl"></i>
            </div>
        </div>
        <h1 class="text-5xl md:text-7xl font-display font-bold tracking-tighter mb-4 text-white">pre<span class="text-brand-valRed">find</span></h1>
        <p class="text-zinc-400 text-lg max-w-lg mx-auto leading-relaxed">Takım arkadaşını bul, istatistiklerini paylaş ve toplulukla etkileşime geç.</p>
    </div>

    <!-- Selection Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl w-full mb-16 relative z-10">
        
        <!-- Valorant Card (Active) -->
        <a href="google_login.php" class="game-card p-8 rounded-3xl relative overflow-hidden group text-left block border-t-4 border-brand-valRed">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-v text-8xl transform rotate-12"></i>
            </div>
            <div class="relative z-10">
                <span class="bg-brand-valRed/10 text-brand-valRed text-xs font-bold px-3 py-1 rounded-full border border-brand-valRed/20 uppercase tracking-wider">Popüler</span>
                <h2 class="text-3xl font-bold mt-4 mb-2 text-white">Valorant</h2>
                <p class="text-zinc-400 text-sm mb-6">Rankına uygun duo, trio veya 5-stack takımını bul. Sesli sohbet garantili.</p>
                <div class="inline-flex items-center gap-2 text-white font-bold group-hover:text-brand-valRed transition-colors">
                    Ajan Ara <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>
        </a>

        <!-- Soru & Cevap Card (New) -->
        <a href="questions.php" class="game-card p-8 rounded-3xl relative overflow-hidden group text-left block border-t-4 border-amber-500">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-comments text-8xl text-amber-500 transform -rotate-12"></i>
            </div>
            <div class="relative z-10">
                <span class="bg-amber-500/10 text-amber-500 text-xs font-bold px-3 py-1 rounded-full border border-amber-500/20 uppercase tracking-wider">Topluluk</span>
                <h2 class="text-3xl font-bold mt-4 mb-2 text-white">Soru & Cevap</h2>
                <p class="text-zinc-400 text-sm mb-6">Ajan taktikleri, lineup'lar ve oyun hataları hakkında tartış.</p>
                <div class="inline-flex items-center gap-2 text-white font-bold group-hover:text-amber-500 transition-colors">
                    Tartışmaya Katıl <i class="fa-solid fa-message"></i>
                </div>
            </div>
        </a>

        <!-- LoL Card (Disabled) -->
        <div class="game-card disabled-card p-8 rounded-3xl relative overflow-hidden text-left border-t-4 border-blue-500/30">
            <div class="absolute top-0 right-0 p-6 opacity-10">
                <i class="fa-solid fa-khanda text-8xl text-blue-500 transform rotate-12"></i>
            </div>
            <div class="relative z-10">
                <span class="bg-blue-500/10 text-blue-400 text-xs font-bold px-3 py-1 rounded-full border border-blue-500/20 uppercase tracking-wider">Yakında</span>
                <h2 class="text-3xl font-bold mt-4 mb-2 text-zinc-500">League of Legends</h2>
                <p class="text-zinc-600 text-sm mb-6">Clash takımı veya Duo Q ortağı arama sistemi hazırlanıyor.</p>
                <div class="inline-flex items-center gap-2 text-zinc-600 font-bold">
                    Hazırlanıyor <i class="fa-solid fa-lock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Button (Footer) -->
    <div class="text-center relative z-10">
        <a href="google_login.php" class="inline-flex items-center gap-3 bg-white text-black px-8 py-4 rounded-xl font-bold text-lg hover:bg-zinc-200 transition-all shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6 h-6">
            <span>Hemen Giriş Yap</span>
        </a>
        <p class="mt-6 text-xs text-zinc-600">Giriş yaparak kullanım koşullarını kabul etmiş sayılırsınız.</p>
    </div>

</body>
</html>