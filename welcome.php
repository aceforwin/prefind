<?php
// Not: Bu kod sunucuda .php uzantısı ile çalıştırılmalıdır.
// require_once 'config.php';

// Demo amaçlı session kontrolü simülasyonu (PHP sunucusunda aktifleşir)
// if (isset($_SESSION['user_id'])) {
//    header("Location: index.php");
//    exit;
// }
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
                            valRed: '#ff4655',
                            lolGold: '#C89B3C',
                            lolBlue: '#0093FF',
                            support: '#8b5cf6'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
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
            background-image: radial-gradient(circle at 50% 0%, rgba(20, 20, 30, 1), #050505 80%);
        }
        .game-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .game-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.7);
        }
        .game-card .logo-container img {
            transition: transform 0.5s ease;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.1));
        }
        .game-card:hover .logo-container img {
            transform: scale(1.1) rotate(2deg);
            filter: drop-shadow(0 0 25px rgba(255,255,255,0.2));
        }
        /* Özel Glow Efektleri */
        .glow-val:hover { box-shadow: 0 0 30px rgba(255, 70, 85, 0.2); border-color: rgba(255, 70, 85, 0.3); }
        .glow-lol:hover { box-shadow: 0 0 30px rgba(200, 155, 60, 0.2); border-color: rgba(200, 155, 60, 0.3); }
        .glow-sup:hover { box-shadow: 0 0 30px rgba(139, 92, 246, 0.2); border-color: rgba(139, 92, 246, 0.3); }
        
        .badge-soon {
            background: linear-gradient(45deg, #1a1a1a, #333);
            border: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<!-- Düzeltme: overflow-hidden kaldırıldı, overflow-x-hidden eklendi. justify-center kaldırıldı. -->
<body class="min-h-screen flex flex-col items-center py-12 px-6 relative overflow-x-hidden">

    <!-- Arka Plan Dekorasyonları -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-brand-valRed/5 rounded-full blur-[120px] mix-blend-screen animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-brand-lolBlue/5 rounded-full blur-[120px] mix-blend-screen animate-pulse" style="animation-delay: 2s"></div>
    </div>

    <!-- Başlık Alanı -->
    <div class="mb-12 text-center relative z-10 animate-float">
        <div class="inline-block p-1 rounded-2xl bg-gradient-to-br from-white/10 to-transparent border border-white/5 mb-6">
            <div class="bg-black/50 backdrop-blur-xl p-4 rounded-xl">
                <i class="fa-solid fa-gamepad text-4xl text-white"></i>
            </div>
        </div>
        <h1 class="text-5xl md:text-8xl font-display font-bold tracking-tighter mb-4 bg-clip-text text-transparent bg-gradient-to-b from-white to-white/60">
            pre<span class="text-brand-valRed">find</span>
        </h1>
        <p class="text-zinc-400 text-lg md:text-xl max-w-lg mx-auto font-light">
            Senin oyunun, senin takımın.
        </p>
    </div>

    <!-- Ana Grid: Valorant, LoL, Destek -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full mb-16 relative z-10">
        
        <!-- 1. KART: VALORANT -->
        <a href="google_login.php" class="game-card glow-val relative group overflow-hidden rounded-[2rem] p-8 flex flex-col items-center text-center h-[420px]">
            <!-- Arka Plan Görseli (Opak) -->
            <div class="absolute inset-0 bg-[url('https://images.contentstack.io/v3/assets/bltb6530b271fddd0b1/blt3f072336e3f3ade4/63096d7be4a8c30e088e771d/Valorant_2022_E5A2_PlayVALORANT_ContentStackThumbnail_1920x1080.jpg')] bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-500 grayscale group-hover:grayscale-0"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>
            
            <!-- İçerik -->
            <div class="relative z-10 flex flex-col items-center h-full justify-between w-full">
                <div class="w-full flex justify-between items-start">
                    <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider bg-brand-valRed text-white shadow-lg shadow-brand-valRed/20">AKTİF</span>
                    <i class="fa-solid fa-arrow-right -rotate-45 text-zinc-500 group-hover:text-white transition-colors"></i>
                </div>
                
                <div class="logo-container my-auto">
                    <!-- Valorant Logo (SVG) -->
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fc/Valorant_logo_-_pink_color_version.svg" alt="Valorant Logo" class="h-24 w-auto drop-shadow-2xl">
                </div>

                <div class="w-full space-y-2">
                    <h2 class="text-3xl font-display font-bold text-white">VALORANT</h2>
                    <p class="text-sm text-zinc-400 font-light px-2">Duo, Trio veya 5-Stack takımını bul. Ajanını seç, savaşa katıl.</p>
                </div>
            </div>
        </a>

        <!-- 2. KART: LEAGUE OF LEGENDS -->
        <!-- Henüz aktif olmadığı için href="#" ve opacity ayarları -->
        <div class="game-card glow-lol relative group overflow-hidden rounded-[2rem] p-8 flex flex-col items-center text-center h-[420px] cursor-not-allowed">
            <!-- Arka Plan Görseli -->
            <div class="absolute inset-0 bg-[url('https://ddragon.leagueoflegends.com/cdn/img/champion/splash/Ahri_0.jpg')] bg-cover bg-center opacity-10 group-hover:opacity-20 transition-opacity duration-500 grayscale"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>

            <div class="relative z-10 flex flex-col items-center h-full justify-between w-full">
                <div class="w-full flex justify-between items-start">
                    <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider badge-soon text-brand-lolGold shadow-lg">YAKINDA</span>
                    <i class="fa-solid fa-lock text-zinc-600"></i>
                </div>

                <div class="logo-container my-auto">
                    <!-- LoL Logo (Düzeltildi) -->
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/LoL_Icon.svg/640px-LoL_Icon.svg.png" alt="" class="h-24 w-auto grayscale group-hover:grayscale-0 transition-all duration-500 opacity-70 group-hover:opacity-100">
                </div>

                <div class="w-full space-y-2">
                    <h2 class="text-3xl font-display font-bold text-zinc-300 group-hover:text-brand-lolGold transition-colors">League of Legends</h2>
                    <p class="text-sm text-zinc-500 font-light px-2">Vadiye inmeye hazır mısın? Duo Q ve Clash takımları çok yakında.</p>
                </div>
            </div>
        </div>

        <!-- 3. KART: DESTEK -->
        <a href="destek.php" class="game-card glow-sup relative group overflow-hidden rounded-[2rem] p-8 flex flex-col items-center text-center h-[420px]">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-support/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div class="relative z-10 flex flex-col items-center h-full justify-between w-full">
                <div class="w-full flex justify-between items-start">
                    <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider bg-zinc-800 text-brand-support border border-brand-support/20">7/24</span>
                    <i class="fa-solid fa-arrow-right -rotate-45 text-zinc-500 group-hover:text-white transition-colors"></i>
                </div>

                <div class="logo-container my-auto relative">
                    <div class="absolute inset-0 bg-brand-support/20 blur-2xl rounded-full scale-0 group-hover:scale-150 transition-transform duration-700"></div>
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="hidden"> <!-- Preload fix dummy -->
                    <!-- Custom Support Icon / Illustration -->
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-brand-support to-indigo-600 flex items-center justify-center shadow-2xl shadow-brand-support/20 group-hover:scale-110 transition-transform duration-500">
                        <i class="fa-solid fa-headset text-5xl text-white"></i>
                    </div>
                </div>

                <div class="w-full space-y-2">
                    <h2 class="text-3xl font-display font-bold text-white">Destek & Yardım</h2>
                    <p class="text-sm text-zinc-400 font-light px-2">Bir sorun mu var? Bize bildir, topluluğu birlikte daha iyi yapalım.</p>
                </div>
            </div>
        </a>

    </div>

    <!-- Giriş Butonu -->
    <div class="text-center relative z-10 mb-12">
        <a href="google_login.php" class="group inline-flex items-center gap-3 bg-white hover:bg-zinc-200 text-black px-8 py-4 rounded-full font-bold text-lg transition-all shadow-[0_0_40px_-10px_rgba(255,255,255,0.3)] hover:shadow-[0_0_60px_-15px_rgba(255,255,255,0.5)] hover:-translate-y-1">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6 h-6 group-hover:rotate-12 transition-transform">
            <span>Hemen Giriş Yap</span>
        </a>
    </div>

    <!-- Yeni Kısa Footer -->
    <footer class="w-full relative z-10 border-t border-white/5 bg-black/20 backdrop-blur-sm mt-auto">
        <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Sol: Copyright -->
            <div class="text-zinc-500 text-sm font-medium">
                &copy; 2025 <span class="text-white">Prefind</span>. Tüm hakları saklıdır.
            </div>

            <!-- Orta: Hızlı Linkler (Opsiyonel) -->
            <div class="hidden md:flex gap-6 text-sm text-zinc-400">
                <a href="#" class="hover:text-white transition-colors">Gizlilik</a>
                <a href="#" class="hover:text-white transition-colors">Kurallar</a>
                <a href="#" class="hover:text-white transition-colors">İletişim</a>
            </div>

            <!-- Sağ: Sosyal Medya -->
            <div class="flex gap-4">
                <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-zinc-400 hover:bg-brand-valRed hover:text-white transition-all">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-zinc-400 hover:bg-[#5865F2] hover:text-white transition-all">
                    <i class="fa-brands fa-discord"></i>
                </a>

            </div>
        </div>
    </footer>

</body>
</html>