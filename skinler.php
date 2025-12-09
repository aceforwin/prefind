<?php
// skinler.php
session_start();
require_once 'config.php';

// 1. GÜVENLİK KONTROLÜ
// Kullanıcı giriş yapmamışsa login sayfasına atar.
if (!isset($_SESSION['user_id'])) {
    // Demo modunda session olmayabilir, bu yüzden bu kontrolü production'da açmalısınız.
    // Şimdilik test edebilmeniz için uyarı verip devam ettiriyorum veya redirect ekliyorum.
    
    // Gerçek kullanım için şu satırı açın:
    header("Location: google_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefind - Skin Savaşları</title>
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
                            lolBlue: '#0093FF',
                            accent: '#8b5cf6'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #050505;
            color: #fff;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(255, 70, 85, 0.1), transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(139, 92, 246, 0.05), transparent 30%);
            min-height: 100vh;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .skin-card {
            transition: all 0.3s ease;
        }
        .skin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -10px rgba(255, 70, 85, 0.2);
            border-color: rgba(255, 70, 85, 0.3);
        }
        .vs-circle {
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="flex flex-col">

    <!-- Navbar -->
    <nav class="w-full border-b border-white/5 bg-black/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="welcome.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-white text-black rounded-xl flex items-center justify-center transform -rotate-3 group-hover:rotate-0 transition">
                    <i class="fa-solid fa-crosshairs text-xl"></i>
                </div>
                <span class="font-display font-bold text-2xl tracking-tight">pre<span class="text-brand-valRed">find</span></span>
            </a>
            
            <div class="hidden md:flex items-center gap-6">
                <a href="ilanlar.php" class="text-zinc-400 hover:text-white transition-colors">İlanlar</a>
                <a href="skinler.php" class="text-white font-medium border-b-2 border-brand-valRed pb-1">Skin Savaşları</a>
                <a href="destek.php" class="text-zinc-400 hover:text-white transition-colors">Destek</a>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm text-right hidden sm:block">
                    <div class="text-zinc-400">Giriş Yapıldı</div>
                    <div class="font-bold text-white"><?php echo $_SESSION['user_name'] ?? 'Oyuncu'; ?></div>
                </div>
                <img src="<?php echo $_SESSION['user_picture'] ?? 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png'; ?>" class="w-10 h-10 rounded-full border-2 border-white/10">
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- ÜST BÖLÜM: VS MODU (DÜELLO) -->
        <section class="mb-16 text-center">
            <h1 class="text-4xl md:text-5xl font-display font-bold mb-2">HANGİSİ DAHA İYİ?</h1>
            <p class="text-zinc-400 mb-8">İki Vandal skini arasında seçimini yap, favorini zirveye taşı.</p>

            <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16 relative">
                
                <!-- Sol Skin -->
                <div id="left-skin-container" class="group relative cursor-pointer w-full max-w-md" onclick="vote('left')">
                    <div class="glass-panel p-8 rounded-3xl border-2 border-transparent group-hover:border-brand-valRed transition duration-300 h-[300px] flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-valRed/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <img id="left-skin-img" src="" class="w-full h-auto object-contain drop-shadow-2xl transform group-hover:scale-110 group-hover:rotate-2 transition duration-500 relative z-10" alt="Skin 1">
                        <h3 id="left-skin-name" class="mt-6 text-2xl font-bold text-zinc-300 group-hover:text-white transition relative z-10">Yükleniyor...</h3>
                    </div>
                </div>

                <!-- VS Badge -->
                <div class="z-20 md:absolute md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2">
                    <div class="w-20 h-20 rounded-full bg-black border-4 border-zinc-800 flex items-center justify-center vs-circle">
                        <span class="font-display font-black text-2xl italic text-brand-valRed">VS</span>
                    </div>
                </div>

                <!-- Sağ Skin -->
                <div id="right-skin-container" class="group relative cursor-pointer w-full max-w-md" onclick="vote('right')">
                    <div class="glass-panel p-8 rounded-3xl border-2 border-transparent group-hover:border-brand-lolBlue transition duration-300 h-[300px] flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-lolBlue/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <img id="right-skin-img" src="" class="w-full h-auto object-contain drop-shadow-2xl transform group-hover:scale-110 group-hover:-rotate-2 transition duration-500 relative z-10" alt="Skin 2">
                        <h3 id="right-skin-name" class="mt-6 text-2xl font-bold text-zinc-300 group-hover:text-white transition relative z-10">Yükleniyor...</h3>
                    </div>
                </div>

            </div>
            
            <button onclick="loadDuel()" class="mt-8 text-zinc-500 hover:text-white text-sm underline">Bu ikiliyi geç</button>
        </section>

        <!-- ALT BÖLÜM: SIRALAMA LİSTESİ -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-yellow-500"></i> En Çok Oy Alanlar
                </h2>
                <div class="text-sm text-zinc-500">
                    Toplam <span id="total-skins-count">0</span> Skin Listelendi
                </div>
            </div>

            <div id="leaderboard-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- JS ile buraya kartlar eklenecek -->
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-white/5 bg-black/40 backdrop-blur-md mt-auto py-8 text-center text-zinc-500 text-sm">
        &copy; 2025 Prefind. Valorant API kullanılarak geliştirilmiştir.
    </footer>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const API_URL = "https://valorant-api.com/v1/weapons";
        const VANDAL_UUID = "9c82e19d-4575-0200-1a81-3eacf00cf872";
        
        let allSkins = [];
        let currentLeftIndex = -1;
        let currentRightIndex = -1;

        // LocalStorage'dan oyları çek, yoksa boş obje oluştur
        let votes = JSON.parse(localStorage.getItem('skin_votes')) || {};

        async function init() {
            try {
                // 1. API'den verileri çek
                const response = await fetch(API_URL);
                const data = await response.json();
                
                // 2. Sadece Vandal'ı bul
                const vandalData = data.data.find(w => w.uuid === VANDAL_UUID);
                
                if (vandalData) {
                    // 3. Skinleri filtrele (Standart skinler hariç)
                    allSkins = vandalData.skins.filter(skin => 
                        skin.displayIcon !== null && // İkonu olmayanları atla
                        skin.displayName !== "Standard Vandal" && // Standart skini atla
                        skin.displayName !== "Random Favorite Skin" // Rastgele seçeneğini atla
                    );

                    // 4. İlk Düelloyu Yükle
                    loadDuel();

                    // 5. Sıralamayı Yükle
                    renderLeaderboard();
                    
                    document.getElementById('total-skins-count').innerText = allSkins.length;
                }

            } catch (error) {
                console.error("API Hatası:", error);
                alert("Valorant API'sine bağlanılamadı.");
            }
        }

        function getRandomIndex() {
            return Math.floor(Math.random() * allSkins.length);
        }

        function loadDuel() {
            // Rastgele 2 farklı index seç
            let idx1 = getRandomIndex();
            let idx2 = getRandomIndex();

            // Aynı gelirse tekrar seç
            while (idx1 === idx2) {
                idx2 = getRandomIndex();
            }

            currentLeftIndex = idx1;
            currentRightIndex = idx2;

            const skin1 = allSkins[idx1];
            const skin2 = allSkins[idx2];

            // DOM Güncelle
            document.getElementById('left-skin-img').src = skin1.displayIcon;
            document.getElementById('left-skin-name').innerText = skin1.displayName;
            
            document.getElementById('right-skin-img').src = skin2.displayIcon;
            document.getElementById('right-skin-name').innerText = skin2.displayName;
        }

        function vote(winnerSide) {
            let winningSkin;
            
            if (winnerSide === 'left') {
                winningSkin = allSkins[currentLeftIndex];
            } else {
                winningSkin = allSkins[currentRightIndex];
            }

            // Oyu kaydet (LocalStorage)
            const skinId = winningSkin.uuid;
            if (!votes[skinId]) {
                votes[skinId] = 0;
            }
            votes[skinId]++;
            
            localStorage.setItem('skin_votes', JSON.stringify(votes));

            // Sıralamayı güncelle
            renderLeaderboard();

            // Yeni düello yükle
            loadDuel();
        }

        function renderLeaderboard() {
            const grid = document.getElementById('leaderboard-grid');
            grid.innerHTML = ''; // Temizle

            // Skinleri oylara göre sırala (Çoktan aza)
            const sortedSkins = [...allSkins].sort((a, b) => {
                const voteA = votes[a.uuid] || 0;
                const voteB = votes[b.uuid] || 0;
                return voteB - voteA;
            });

            // İlk 20 skini göster (Performans için)
            // İsterseniz .slice(0, 20) kaldırıp hepsini gösterebilirsiniz.
            sortedSkins.slice(0, 24).forEach((skin, index) => {
                const voteCount = votes[skin.uuid] || 0;
                
                // Kart HTML
                const card = document.createElement('div');
                card.className = 'glass-panel p-4 rounded-xl skin-card flex flex-col items-center text-center relative';
                
                // Sıra numarası (Rozet)
                let rankBadge = '';
                if(index === 0) rankBadge = '<div class="absolute top-2 left-2 w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center font-bold text-black">1</div>';
                else if(index === 1) rankBadge = '<div class="absolute top-2 left-2 w-8 h-8 bg-gray-300 rounded-lg flex items-center justify-center font-bold text-black">2</div>';
                else if(index === 2) rankBadge = '<div class="absolute top-2 left-2 w-8 h-8 bg-orange-700 rounded-lg flex items-center justify-center font-bold text-white">3</div>';

                card.innerHTML = `
                    ${rankBadge}
                    <div class="h-32 w-full flex items-center justify-center mb-4">
                        <img src="${skin.displayIcon}" class="max-h-full max-w-full drop-shadow-lg" loading="lazy">
                    </div>
                    <h4 class="font-bold text-sm text-zinc-200 mb-2 min-h-[40px] flex items-center justify-center">${skin.displayName}</h4>
                    <div class="mt-auto flex items-center gap-2 bg-white/5 px-3 py-1 rounded-full">
                        <i class="fa-solid fa-heart text-brand-valRed text-xs"></i>
                        <span class="font-mono font-bold text-sm">${voteCount} Oy</span>
                    </div>
                `;

                grid.appendChild(card);
            });
        }

        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', init);
    </script>

</body>
</html>