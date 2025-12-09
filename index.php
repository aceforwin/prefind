<?php
require_once 'config.php';

// Güvenlik
if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit;
}

// Kullanıcı verisini taze çek
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$userData = $stmtUser->fetch();

// Session güncelle
$_SESSION['username'] = $userData['username'] ?? null;
$_SESSION['avatar'] = $userData['avatar'];

// --- SÜRE YÖNETİMİ ---
try {
    $pdo->exec("DELETE FROM listings WHERE created_at < (NOW() - INTERVAL 2 MINUTE)");
} catch (PDOException $e) {}

// İlanları çek
$listings = [];
try {
    $sql = "
        SELECT listings.*, 
               users.username as user_username, 
               users.name as google_name, 
               users.avatar as user_avatar,
               (120 - TIMESTAMPDIFF(SECOND, listings.created_at, NOW())) as remaining_seconds
        FROM listings 
        JOIN users ON listings.user_id = users.id 
        WHERE listings.is_active = 1 
        HAVING remaining_seconds > 0
        ORDER BY listings.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {}

// Cooldown Hesaplama
$cooldownRemaining = 0;
try {
    $stmtLast = $pdo->prepare("
        SELECT (120 - TIMESTAMPDIFF(SECOND, created_at, NOW())) as remaining 
        FROM listings 
        WHERE user_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmtLast->execute([$userData['id']]);
    $lastPost = $stmtLast->fetch();
    
    if ($lastPost && $lastPost['remaining'] > 0) {
        $cooldownRemaining = $lastPost['remaining'];
    }
} catch (Exception $e) {}

// YENİ: Rank Bilgileri (İkon + Türkçe İsim + Renk)
function getRankInfo($rank) {
    // Valorant API'den ikonlar
    $baseIconUrl = "https://media.valorant-api.com/competitivetiers/03621f52-342b-cf4e-4f86-9350a49c6d04/";
    
    $ranks = [
        'Iron'      => ['tr' => 'Demir',       'icon' => $baseIconUrl . '3/largeicon.png',  'style' => 'text-zinc-400 border-zinc-600/50 bg-zinc-900/50'],
        'Bronze'    => ['tr' => 'Bronz',       'icon' => $baseIconUrl . '6/largeicon.png',  'style' => 'text-[#a67c52] border-[#a67c52]/30 bg-[#a67c52]/10'],
        'Silver'    => ['tr' => 'Gümüş',       'icon' => $baseIconUrl . '9/largeicon.png',  'style' => 'text-slate-300 border-slate-400/30 bg-slate-800/30'],
        'Gold'      => ['tr' => 'Altın',       'icon' => $baseIconUrl . '12/largeicon.png', 'style' => 'text-[#ecd25e] border-[#ecd25e]/30 bg-[#ecd25e]/10'],
        'Platinum'  => ['tr' => 'Platin',      'icon' => $baseIconUrl . '15/largeicon.png', 'style' => 'text-[#36aeb0] border-[#36aeb0]/30 bg-[#36aeb0]/10'],
        'Diamond'   => ['tr' => 'Elmas',       'icon' => $baseIconUrl . '18/largeicon.png', 'style' => 'text-[#b489c6] border-[#b489c6]/30 bg-[#b489c6]/10'],
        'Ascendant' => ['tr' => 'Yücelik',     'icon' => $baseIconUrl . '21/largeicon.png', 'style' => 'text-[#56a877] border-[#56a877]/30 bg-[#56a877]/10'],
        'Immortal'  => ['tr' => 'Ölümsüzlük',  'icon' => $baseIconUrl . '24/largeicon.png', 'style' => 'text-[#bb3d4e] border-[#bb3d4e]/30 bg-[#bb3d4e]/10'],
        'Radiant'   => ['tr' => 'Radyant',     'icon' => $baseIconUrl . '25/largeicon.png', 'style' => 'text-[#ffffaa] border-[#ffffaa]/50 bg-[#ffffaa]/10 shadow-[0_0_15px_rgba(255,255,170,0.15)]']
    ];
    return $ranks[$rank] ?? $ranks['Iron'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { 
                        dark: { bg: '#09090b', surface: '#18181b', border: '#27272a' }, 
                        primary: '#ff4655'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #09090b; color: #f4f4f5; overflow-x: hidden; }
        .nav-blur { background: rgba(9, 9, 11, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.05); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #09090b; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
        .listing-card { transition: all 0.3s ease; }
        .listing-card:hover { transform: translateY(-4px); border-color: #3f3f46; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5); }
    </style>
</head>
<body class="min-h-screen pt-24 pb-10">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 nav-blur top-0 left-0 h-20">
        <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between relative">
            
            <!-- Sol: Logo -->
            <a href="index.php" class="flex items-center gap-3 font-bold text-2xl tracking-tighter hover:opacity-80 transition-opacity z-20">
                <div class="w-9 h-9 bg-white text-black rounded-xl flex items-center justify-center shadow-lg shadow-white/10">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <span>prefind</span>
            </a>

            <!-- Sağ: Butonlar & Profil -->
            <div class="flex items-center gap-4 z-20">
                <!-- İlan Ver Butonu -->
                <button onclick="checkCooldownAndOpen()" id="mainCreateBtn" 
                        class="bg-white text-black hover:bg-zinc-200 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-white/5 active:scale-95">
                    <i class="fa-solid fa-plus"></i> 
                    <span id="btnText">İlan Ver</span>
                </button>

                <!-- Profil -->
                <div class="relative group cursor-pointer">
                    <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" class="w-10 h-10 rounded-xl border border-white/10 bg-zinc-800 object-cover">
                    
                    <!-- Profil Dropdown -->
                    <div class="absolute right-0 top-full mt-4 w-60 bg-[#121215] border border-white/10 rounded-2xl shadow-2xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right translate-y-2 group-hover:translate-y-0">
                        <div class="px-5 py-4 border-b border-white/5">
                            <p class="text-[10px] text-zinc-500 uppercase tracking-wider font-bold mb-1">Giriş Yapılan</p>
                            <p class="text-sm font-bold text-white truncate">
                                <?php echo htmlspecialchars(!empty($userData['username']) ? '@'.$userData['username'] : $userData['name']); ?>
                            </p>
                        </div>
                        <div class="p-2">
                            <a href="profile.php?id=<?php echo $userData['id']; ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-400 hover:text-white hover:bg-white/5 rounded-xl transition-colors">
                                <i class="fa-solid fa-user"></i> Profilim
                            </a>
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                                <i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Ana İçerik -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Başlık -->
        <div class="flex items-end justify-between mb-8 pb-4 border-b border-white/5">
            <div>
                <h1 class="text-3xl font-extrabold text-white mb-2 tracking-tight">Takımını Kur</h1>
                <p class="text-zinc-500 text-sm">Şu an aktif <span class="text-white font-bold"><?php echo count($listings); ?></span> oyuncu ilan verdi.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/5 text-xs text-zinc-400">
                <i class="fa-regular fa-clock"></i> Süre: <span class="text-white font-bold">2 Dakika</span>
            </div>
        </div>

        <!-- İlanlar Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($listings as $listing): ?>
                <?php 
                    $remainingSeconds = $listing['remaining_seconds'];
                    if ($remainingSeconds > 120) $remainingSeconds = 120;
                    if ($remainingSeconds < 0) $remainingSeconds = 0;

                    $displayName = !empty($listing['user_username']) ? $listing['user_username'] : $listing['google_name'];
                    
                    // Rank Bilgilerini Al
                    $minRank = getRankInfo($listing['min_rank']);
                    $maxRank = getRankInfo($listing['max_rank']);
                ?>
                <div class="listing-card bg-[#121215] border border-white/5 rounded-2xl p-6 relative flex flex-col h-full group" 
                     data-remaining="<?php echo $remainingSeconds; ?>">
                    
                    <!-- Üst Kısım -->
                    <div class="flex justify-between items-start mb-5">
                        <a href="profile.php?id=<?php echo $listing['user_id']; ?>" class="flex items-center gap-3 group/profile">
                            <img src="<?php echo htmlspecialchars($listing['user_avatar']); ?>" class="w-12 h-12 rounded-xl bg-zinc-800 object-cover ring-2 ring-white/5 group-hover/profile:ring-white/20 transition-all">
                            <div>
                                <h3 class="font-bold text-white text-base group-hover/profile:text-primary transition-colors"><?php echo htmlspecialchars($listing['riot_id']); ?></h3>
                                <p class="text-zinc-500 text-xs mt-0.5 group-hover/profile:text-zinc-300">@<?php echo htmlspecialchars($displayName); ?></p>
                            </div>
                        </a>
                        
                        <!-- SAYAÇ (DK Formatı) -->
                        <div class="bg-red-500/10 text-red-500 border border-red-500/20 px-3 py-1 rounded-lg text-xs font-mono font-bold flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="countdown-text">2 DK</span>
                        </div>
                    </div>

                    <!-- Ranklar (İkonlu) -->
                    <div class="flex items-center gap-2 mb-5">
                        <!-- Min Rank -->
                        <div class="flex-1 flex items-center justify-center gap-2 px-2 py-2 rounded-lg border text-xs font-bold <?php echo $minRank['style']; ?>">
                            <img src="<?php echo $minRank['icon']; ?>" class="w-5 h-5 object-contain">
                            <span><?php echo $minRank['tr']; ?></span>
                        </div>
                        
                        <i class="fa-solid fa-arrow-right-long text-zinc-600 text-xs opacity-50"></i>
                        
                        <!-- Max Rank -->
                        <div class="flex-1 flex items-center justify-center gap-2 px-2 py-2 rounded-lg border text-xs font-bold <?php echo $maxRank['style']; ?>">
                            <span><?php echo $maxRank['tr']; ?></span>
                            <img src="<?php echo $maxRank['icon']; ?>" class="w-5 h-5 object-contain">
                        </div>
                    </div>

                    <!-- Not -->
                    <div class="flex-grow mb-5">
                        <p class="text-sm text-zinc-400 italic leading-relaxed bg-black/20 p-3 rounded-lg border border-white/5 min-h-[60px]">
                            "<?php echo $listing['note'] ? htmlspecialchars($listing['note']) : 'Not yok.'; ?>"
                        </p>
                    </div>

                    <!-- Alt Kısım -->
                    <div class="pt-4 border-t border-white/5 mt-auto flex items-center justify-between gap-3">
                        <?php if($listing['lobby_code']): ?>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider">Lobi Kodu</span>
                                <span class="text-sm font-mono font-bold text-white tracking-wide"><?php echo htmlspecialchars($listing['lobby_code']); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider">Durum</span>
                                <span class="text-sm text-zinc-300">Davet Bekliyor</span>
                            </div>
                        <?php endif; ?>

                        <button onclick="copyCode('<?php echo $listing['lobby_code'] ?: $listing['riot_id']; ?>', this)" 
                                class="bg-white hover:bg-zinc-200 text-black px-5 py-2 rounded-lg text-xs font-bold transition-all active:scale-95 flex items-center gap-2 shadow-lg shadow-white/5">
                            <i class="fa-regular fa-copy"></i>
                            <span>Kopyala</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (count($listings) == 0): ?>
                <div class="col-span-full py-24 flex flex-col items-center justify-center text-center border border-dashed border-white/10 rounded-3xl bg-white/[0.02]">
                    <div class="w-16 h-16 bg-zinc-900 rounded-full flex items-center justify-center mb-4 border border-white/5">
                        <i class="fa-solid fa-ghost text-2xl text-zinc-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Kimse Yok Mu?</h3>
                    <p class="text-zinc-500 text-sm">Şu an aktif ilan bulunmuyor. İlk ilanı sen oluştur!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- İlan Oluştur Modal -->
    <div id="create-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg transition-all duration-300 transform scale-95 opacity-0" id="modal-content">
            <div class="bg-[#121215] border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-white/5 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i> Yeni İlan
                    </h2>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="create-form" class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 mb-2 uppercase">Riot ID <span class="text-red-500">*</span></label>
                            <input type="text" name="riot_id" required placeholder="Jett#TR1" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-white/30 outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 mb-2 uppercase">Oda Kodu</label>
                            <input type="text" name="lobby_code" placeholder="12345" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-white/30 outline-none transition-colors font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 mb-2 uppercase">Min Rank</label>
                            <div class="relative">
                                <select name="min_rank" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-white/30 outline-none appearance-none cursor-pointer">
                                    <option value="Iron">Demir</option>
                                    <option value="Bronze">Bronz</option>
                                    <option value="Silver">Gümüş</option>
                                    <option value="Gold" selected>Altın</option>
                                    <option value="Platinum">Platin</option>
                                    <option value="Diamond">Elmas</option>
                                    <option value="Ascendant">Yücelik</option>
                                    <option value="Immortal">Ölümsüzlük</option>
                                    <option value="Radiant">Radyant</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 mb-2 uppercase">Max Rank</label>
                            <div class="relative">
                                <select name="max_rank" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-white/30 outline-none appearance-none cursor-pointer">
                                    <option value="Gold">Altın</option>
                                    <option value="Platinum" selected>Platin</option>
                                    <option value="Diamond">Elmas</option>
                                    <option value="Ascendant">Yücelik</option>
                                    <option value="Immortal">Ölümsüzlük</option>
                                    <option value="Radiant">Radyant</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 mb-2 uppercase">Notun</label>
                        <textarea name="note" rows="3" placeholder="Oyun tarzından bahset..." class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-white/30 outline-none resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-white text-black hover:bg-zinc-200 font-bold py-3.5 rounded-xl transition-colors text-sm flex justify-center items-center gap-2 mt-2">
                        <span>Yayınla</span>
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KULLANICI ADI MODALI (Zorunlu) -->
    <?php if(empty($userData['username'])): ?>
    <div id="username-modal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95 backdrop-blur-xl p-4">
        <div class="w-full max-w-sm bg-[#121215] border border-white/10 rounded-3xl shadow-2xl p-8 relative overflow-hidden text-center">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
            <div class="w-20 h-20 bg-white/5 rounded-2xl border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-lg rotate-3">
                <i class="fa-solid fa-fingerprint text-3xl text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Kimliğini Oluştur</h2>
            <p class="text-zinc-400 text-sm mb-8 leading-relaxed">Toplulukta seni tanımamız için benzersiz bir kullanıcı adı seç.</p>
            <form id="username-form" class="space-y-4">
                <div class="relative">
                    <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm transition-colors"></i>
                    <input type="text" name="username" required placeholder="kullaniciadi" 
                           class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-4 pl-10 text-white focus:border-white/30 focus:bg-black/50 outline-none transition-all text-sm font-bold tracking-wide placeholder-zinc-600">
                </div>
                <p id="username-error" class="text-red-400 text-xs bg-red-500/10 py-2.5 rounded-lg border border-red-500/20 hidden flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <span>Hata mesajı</span>
                </p>
                <button type="submit" class="w-full bg-white text-black hover:bg-zinc-200 font-bold py-4 rounded-xl transition-all shadow-lg shadow-white/5 active:scale-95 flex items-center justify-center gap-2">
                    <span>Tamamla</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // --- GERİ SAYIM (DK Formatı) ---
        function updateTimers() {
            document.querySelectorAll('.listing-card').forEach(card => {
                let remaining = parseInt(card.getAttribute('data-remaining'));
                
                if (remaining <= 0) {
                    card.remove(); 
                    return;
                }

                card.setAttribute('data-remaining', remaining - 1);
                const text = card.querySelector('.countdown-text');
                
                // Format: X DK
                if (remaining > 60) {
                    text.innerText = '2 DK';
                } else {
                    text.innerText = '1 DK'; 
                }
                
                if(remaining < 30) {
                    text.parentElement.classList.add('text-red-500', 'border-red-500/50', 'bg-red-500/20');
                }
            });
        }
        setInterval(updateTimers, 1000);
        updateTimers(); 

        // --- COOLDOWN BUTONU (Basit JS) ---
        let cooldownSeconds = <?php echo $cooldownRemaining; ?>; 
        const createBtn = document.getElementById('mainCreateBtn');
        const btnText = document.getElementById('btnText');

        function updateBtnTimer() {
            if (cooldownSeconds > 0) {
                createBtn.disabled = true;
                createBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Butonda da aynı DK mantığı
                let displayTime = cooldownSeconds > 60 ? "2 DK" : cooldownSeconds + "s";
                
                btnText.innerHTML = `<i class="fa-solid fa-stopwatch mr-1"></i> ${displayTime}`;
                cooldownSeconds--;
                setTimeout(updateBtnTimer, 1000);
            } else {
                createBtn.disabled = false;
                createBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                btnText.innerHTML = "İlan Ver";
            }
        }
        updateBtnTimer();

        // --- MODAL İŞLEMLERİ ---
        const modal = document.getElementById('create-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const content = document.getElementById('modal-content');

        function checkCooldownAndOpen() {
            if (cooldownSeconds <= 0) {
                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                });
            }
        }

        function closeModal() {
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // --- FORMLAR ---
        const uForm = document.getElementById('username-form');
        if(uForm) {
            uForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                const err = document.getElementById('username-error');
                const originalHtml = btn.innerHTML;
                
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
                btn.disabled = true;
                err.classList.add('hidden');

                fetch('api_set_username.php', { method: 'POST', body: new FormData(this) })
                .then(r => r.json()).then(d => {
                    if(d.status === 'success') location.reload();
                    else { 
                        err.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span>${d.message}</span>`;
                        err.classList.remove('hidden');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                });
            });
        }

        document.getElementById('create-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
            
            fetch('api_add_listing.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.json()).then(d => {
                if(d.status === 'success') location.href = 'index.php';
                else { alert(d.message); btn.innerHTML = original; btn.disabled = false; }
            });
        });

        function copyCode(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check text-green-500"></i>';
                setTimeout(() => btn.innerHTML = original, 1500);
            });
        }
    </script>
</body>
</html>