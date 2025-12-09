<?php
require_once 'config.php';

// ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$profileId = intval($_GET['id']);
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Profil sahibinin bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header("Location: index.php");
    exit;
}

// Profil sahibinin aktif ilanlarını çek
$stmtListings = $pdo->prepare("SELECT * FROM listings WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC");
$stmtListings->execute([$profileId]);
$userListings = $stmtListings->fetchAll();

// Görünen isim
$displayName = !empty($profileUser['username']) ? $profileUser['username'] : $profileUser['name'];
$isOwnProfile = ($currentUserId == $profileId);

// Rank Renkleri
function getRankColor($rank) {
    $colors = [
        'Iron'      => 'bg-zinc-700 text-zinc-300',
        'Bronze'    => 'bg-yellow-900/50 text-yellow-500',
        'Silver'    => 'bg-slate-700 text-slate-300',
        'Gold'      => 'bg-yellow-600/20 text-yellow-500',
        'Platinum'  => 'bg-cyan-900/30 text-cyan-400',
        'Diamond'   => 'bg-purple-900/30 text-purple-400',
        'Ascendant' => 'bg-emerald-900/30 text-emerald-400',
        'Immortal'  => 'bg-rose-900/30 text-rose-400',
        'Radiant'   => 'bg-amber-100/10 text-amber-200 border border-amber-500/20'
    ];
    return $colors[$rank] ?? 'bg-zinc-800 text-zinc-400';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($displayName); ?> - Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        dark: {
                            bg: '#0f0f11',
                            surface: '#18181b',
                            border: '#27272a'
                        },
                        primary: '#3b82f6'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0f11; color: #f4f4f5; }
        .nav-blur { background: rgba(15, 15, 17, 0.9); backdrop-filter: blur(8px); border-bottom: 1px solid #27272a; }
        .card-hover { transition: transform 0.2s ease, border-color 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); border-color: #3f3f46; }
    </style>
</head>
<body class="min-h-screen pt-20">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 nav-blur top-0 left-0 h-16">
        <div class="max-w-6xl mx-auto px-4 h-full flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 font-bold text-xl tracking-tight hover:opacity-80 transition-opacity">
                <i class="fa-solid fa-arrow-left text-zinc-400 mr-2 text-sm"></i>
                <div class="w-8 h-8 bg-white text-black rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <span>prefind</span>
            </a>

            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-sm text-zinc-400 hover:text-white transition-colors">İlanlar</a>
                <div class="h-4 w-[1px] bg-dark-border"></div>
                <a href="logout.php" class="text-sm text-red-400 hover:text-red-300 transition-colors">Çıkış</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Profil İçeriği -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Üst Bilgi Kartı -->
        <div class="bg-dark-surface border border-dark-border rounded-2xl p-8 mb-8 flex flex-col md:flex-row items-center md:items-start gap-8 relative overflow-hidden group/card">
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-zinc-800/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none transition-opacity duration-500 group-hover/card:bg-primary/5"></div>

            <!-- Avatar -->
            <div class="relative group">
                <div class="relative w-28 h-28 md:w-36 md:h-36">
                    <img src="<?php echo htmlspecialchars($profileUser['avatar']); ?>" class="w-full h-full rounded-2xl border-4 border-dark-bg object-cover shadow-2xl bg-zinc-800 ring-1 ring-white/10 relative z-10 transition-transform duration-300 group-hover:scale-[1.02]">
                    <!-- Glow Effect -->
                    <div class="absolute inset-0 bg-primary/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
                </div>
                
                <?php if($isOwnProfile): ?>
                    <button onclick="openEditModal()" class="absolute inset-0 z-20 bg-black/60 rounded-2xl opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white transition-all duration-300 cursor-pointer backdrop-blur-sm border-4 border-transparent">
                        <i class="fa-solid fa-camera text-2xl mb-1"></i>
                        <span class="text-xs font-bold">Değiştir</span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Bilgiler -->
            <div class="text-center md:text-left flex-grow w-full pt-2">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-3">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-1"><?php echo htmlspecialchars($displayName); ?></h1>
                        <p class="text-zinc-500 text-sm font-medium">
                            <?php if(!empty($profileUser['username'])): ?>
                                @<?php echo htmlspecialchars($profileUser['username']); ?>
                            <?php else: ?>
                                Kullanıcı Adı Yok
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if($isOwnProfile): ?>
                        <button onclick="openEditModal()" class="bg-white text-black hover:bg-zinc-200 px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-white/5 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen"></i> <span>Düzenle</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- İstatistikler -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-6">
                    <span class="bg-zinc-800/50 text-zinc-400 px-3 py-1.5 rounded-lg text-xs font-medium border border-dark-border flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-primary/70"></i>
                        <span>Katılım: <?php echo date('d.m.Y', strtotime($profileUser['created_at'])); ?></span>
                    </span>
                    <span class="bg-zinc-800/50 text-zinc-400 px-3 py-1.5 rounded-lg text-xs font-medium border border-dark-border flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-primary/70"></i>
                        <span>İlanlar: <?php echo count($userListings); ?></span>
                    </span>
                </div>

                <!-- Sosyal Medya Linkleri -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-5 border-t border-dark-border">
                    
                    <!-- Discord -->
                    <?php if(!empty($profileUser['discord_username'])): ?>
                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($profileUser['discord_username']); ?>', this)" 
                            class="flex items-center gap-2 px-4 py-2 bg-[#5865F2]/10 hover:bg-[#5865F2]/20 text-[#5865F2] border border-[#5865F2]/20 rounded-lg transition-all text-sm font-medium group active:scale-95">
                        <i class="fa-brands fa-discord text-lg"></i>
                        <span><?php echo htmlspecialchars($profileUser['discord_username']); ?></span>
                        <i class="fa-regular fa-copy opacity-50 group-hover:opacity-100 ml-1"></i>
                    </button>
                    <?php endif; ?>

                    <!-- Tracker.gg -->
                    <?php if(!empty($profileUser['tracker_link'])): ?>
                    <a href="<?php echo htmlspecialchars($profileUser['tracker_link']); ?>" target="_blank" 
                       class="flex items-center gap-2 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 border border-red-500/20 rounded-lg transition-all text-sm font-medium active:scale-95">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>Tracker.gg</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>
                    </a>
                    <?php endif; ?>

                    <?php if(empty($profileUser['discord_username']) && empty($profileUser['tracker_link']) && !$isOwnProfile): ?>
                        <span class="text-zinc-600 text-sm italic">Sosyal medya hesabı eklenmemiş.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bölüm Başlığı -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Aktif İlanlar</h2>
            <?php if(count($userListings) > 0): ?>
                <span class="text-xs text-green-400 flex items-center gap-1 bg-green-400/10 px-2 py-1 rounded border border-green-400/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Yayında
                </span>
            <?php endif; ?>
        </div>

        <!-- İlan Listesi -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($userListings as $listing): ?>
                <?php 
                    $createdTime = strtotime($listing['created_at']);
                    $expireTime = $createdTime + (5 * 60); 
                ?>
                <div class="card-hover bg-dark-surface border border-dark-border rounded-xl p-5 relative flex flex-col listing-card" data-expire="<?php echo $expireTime; ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-white text-sm"><?php echo htmlspecialchars($listing['riot_id']); ?></h3>
                            <div class="text-xs font-mono text-zinc-500 bg-black/20 px-2 py-1 rounded mt-1 inline-block countdown-text">--:--</div>
                        </div>
                        <?php if($listing['lobby_code']): ?>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] text-zinc-500 uppercase font-bold">Lobi</span>
                                <span class="text-sm font-mono text-white"><?php echo htmlspecialchars($listing['lobby_code']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-2 mb-3 text-xs font-medium">
                        <div class="px-2 py-1 rounded <?php echo getRankColor($listing['min_rank']); ?>"><?php echo $listing['min_rank']; ?></div>
                        <i class="fa-solid fa-arrow-right text-zinc-600 text-[10px]"></i>
                        <div class="px-2 py-1 rounded <?php echo getRankColor($listing['max_rank']); ?>"><?php echo $listing['max_rank']; ?></div>
                    </div>

                    <p class="text-sm text-zinc-400 mb-4 line-clamp-2"><?php echo $listing['note'] ? '"'.htmlspecialchars($listing['note']).'"' : ''; ?></p>

                    <div class="mt-auto pt-3 border-t border-dark-border flex justify-between items-center">
                        <span class="text-xs text-zinc-500"><?php echo date('H:i', strtotime($listing['created_at'])); ?></span>
                        <?php if($isOwnProfile): ?>
                            <button class="text-red-400 hover:text-red-300 text-xs font-semibold px-2 py-1 rounded hover:bg-red-500/10 transition-colors">
                                <i class="fa-solid fa-trash mr-1"></i> Sil
                            </button>
                        <?php else: ?>
                            <button onclick="copyToClipboard('<?php echo $listing['lobby_code'] ?: $listing['riot_id']; ?>', this)" class="bg-white/5 hover:bg-white/10 text-white text-xs px-3 py-1.5 rounded transition-colors border border-dark-border">
                                Kopyala
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($userListings) == 0): ?>
                <div class="col-span-full py-12 text-center border border-dashed border-dark-border rounded-xl bg-dark-surface/30">
                    <i class="fa-solid fa-ghost text-2xl text-zinc-600 mb-2"></i>
                    <p class="text-zinc-500 text-sm">Şu an aktif bir ilan yok.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Düzenleme Modalı -->
    <?php if($isOwnProfile): ?>
    <div id="edit-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-dark-surface border border-dark-border rounded-xl shadow-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-white">Profili Düzenle</h2>
                <button onclick="closeEditModal()" class="text-zinc-500 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="edit-form" class="space-y-5" enctype="multipart/form-data">
                <!-- Avatar Upload (New) -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 mb-2">Profil Fotoğrafı</label>
                    <div class="flex items-center gap-4">
                        <img id="preview-img" src="<?php echo htmlspecialchars($profileUser['avatar']); ?>" class="w-16 h-16 rounded-xl object-cover border border-dark-border bg-zinc-800">
                        <label class="flex-1 cursor-pointer group">
                            <span class="sr-only">Dosya Seç</span>
                            <input type="file" name="avatar_file" accept=".jpg,.jpeg,.png,.jfif" class="block w-full text-sm text-zinc-400
                              file:mr-4 file:py-2.5 file:px-4
                              file:rounded-lg file:border-0
                              file:text-xs file:font-semibold
                              file:bg-white file:text-black
                              hover:file:bg-zinc-200
                              cursor-pointer transition-colors
                            " onchange="previewFile(this)">
                            <p class="text-[10px] text-zinc-600 mt-2 pl-1">Desteklenen: JPG, PNG, JFIF. Max 2MB.</p>
                        </label>
                    </div>
                </div>

                <!-- Discord -->
                <div>
                    <label class="block text-xs font-semibold text-[#5865F2] mb-1.5"><i class="fa-brands fa-discord mr-1"></i> Discord Kullanıcı Adı</label>
                    <input type="text" name="discord_username" value="<?php echo htmlspecialchars($profileUser['discord_username'] ?? ''); ?>" placeholder="kullanici#0000 veya kullanici" class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2.5 text-sm text-white focus:border-[#5865F2] outline-none transition-colors">
                </div>

                <!-- Tracker Link -->
                <div>
                    <label class="block text-xs font-semibold text-red-500 mb-1.5"><i class="fa-solid fa-chart-simple mr-1"></i> Tracker.gg Linki</label>
                    <input type="url" name="tracker_link" value="<?php echo htmlspecialchars($profileUser['tracker_link'] ?? ''); ?>" placeholder="https://tracker.gg/valorant/profile/..." class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2.5 text-sm text-white focus:border-red-500 outline-none transition-colors">
                </div>

                <button type="submit" class="w-full bg-white text-black hover:bg-zinc-200 font-bold py-3 rounded-lg transition-colors text-sm mt-2">
                    Değişiklikleri Kaydet
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Modal İşlemleri
        const modal = document.getElementById('edit-modal');
        function openEditModal() { modal.classList.remove('hidden'); }
        function closeEditModal() { modal.classList.add('hidden'); }

        // Dosya Önizleme
        function previewFile(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Form Submit (AJAX)
        const editForm = document.getElementById('edit-form');
        if(editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Kaydediliyor...';
                btn.disabled = true;

                fetch('api_update_profile.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Bir hata oluştu.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            });
        }

        // Sayaç Mantığı
        function updateTimers() {
            const now = Math.floor(Date.now() / 1000);
            document.querySelectorAll('.listing-card').forEach(card => {
                const expireTime = parseInt(card.getAttribute('data-expire'));
                const timeLeft = expireTime - now;
                
                if (timeLeft <= 0) { card.remove(); return; }

                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;
                const formattedM = m < 10 ? '0' + m : m;
                const formattedS = s < 10 ? '0' + s : s;

                const text = card.querySelector('.countdown-text');
                text.innerText = `${formattedM}:${formattedS}`;
                
                if(timeLeft < 60) text.classList.add('text-red-400');
            });
        }
        setInterval(updateTimers, 1000);
        updateTimers();

        // Kopyalama
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const original = btn.innerHTML;
                // Eğer iconlu butonsa içeriği değiştirme mantığı farklı olabilir, basit tutuyoruz
                if(btn.tagName === 'BUTTON' && btn.classList.contains('bg-[#5865F2]/10')) {
                    // Discord butonu
                    const icon = btn.querySelector('.fa-copy');
                    if(icon) icon.className = 'fa-solid fa-check text-green-500 ml-1';
                    setTimeout(() => { if(icon) icon.className = 'fa-regular fa-copy opacity-50 group-hover:opacity-100 ml-1'; }, 1500);
                } else {
                    btn.innerHTML = 'Kopyalandı';
                    setTimeout(() => btn.innerHTML = original, 1000);
                }
            });
        }
    </script>
</body>
</html>