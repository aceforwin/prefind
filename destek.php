<?php
// Hata Raporlamayı Aç (Geliştirme aşamasında hataları görmek için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısı kontrolü (Hata 500'ü önlemek için dosya kontrolü)
$db = null;
if (file_exists('config.php')) {
    require_once 'config.php';
    
    // HATA DÜZELTME / UYUM MODU:
    // config.php dosyanız bağlantıyı '$pdo' değişkenine atıyor, ancak bu sayfa '$db' kullanıyor.
    // Eğer $pdo varsa, onu $db'ye aktararak kodun geri kalanının çalışmasını sağlıyoruz.
    if (isset($pdo) && $pdo instanceof PDO) {
        $db = $pdo;
    }
}

$message = "";
$messageType = ""; // 'success' veya 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al ve temizle
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $msg_content = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // HATA DÜZELTME: Mutlak yol (Absolute Path) kullanımı
    $uploadDir = 'uploads/helps/';
    $uploadPath = __DIR__ . '/' . $uploadDir;
    
    $fileName = null;
    $uploadOk = true;

    // 1. Klasör Kontrolü ve Oluşturma
    if (!file_exists($uploadPath)) {
        // Hata bastırma operatörü (@) ile mkdir uyarısını gizleyip sonucu kontrol ediyoruz
        if (!@mkdir($uploadPath, 0777, true)) {
            $error = error_get_last();
            $message = "Sunucu hatası: Klasör oluşturulamadı. Hata: " . ($error['message'] ?? 'Bilinmiyor');
            $messageType = "error";
            $uploadOk = false;
        }
    }

    // 2. Yazma İzni Kontrolü
    if ($uploadOk && !is_writable($uploadPath)) {
        $message = "Sunucu hatası: Yükleme klasörüne yazma izni yok. (chmod 777 uploads komutunu deneyin)";
        $messageType = "error";
        $uploadOk = false;
    }

    // 3. Dosya Yükleme İşlemi
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] != 4) {
        
        if ($uploadOk) {
            $file = $_FILES['attachment'];

            if ($file['error'] !== 0) {
                $message = "Dosya yüklenirken sunucu hatası oluştu. Hata Kodu: " . $file['error'];
                $messageType = "error";
                $uploadOk = false;
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                
                if (!in_array($file['type'], $allowedTypes)) {
                    $message = "Sadece JPG ve PNG formatındaki görseller kabul edilir.";
                    $messageType = "error";
                    $uploadOk = false;
                }
                elseif ($file['size'] > 5 * 1024 * 1024) {
                    $message = "Dosya boyutu 5MB'dan büyük olamaz.";
                    $messageType = "error";
                    $uploadOk = false;
                }
                else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid('help_', true) . '.' . $ext;
                    
                    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                        $message = "Dosya taşınamadı. Klasör izinlerini kontrol edin.";
                        $messageType = "error";
                        $uploadOk = false;
                        $fileName = null;
                    }
                }
            }
        }
    }

    // 4. Veritabanı Kaydı
    if ($uploadOk && empty($message)) {
        try {
            // HATA DÜZELTME: $db'nin geçerli bir PDO nesnesi olup olmadığını kontrol et
            // Eğer $db bir string ise (örn: veritabanı adı), prepare fonksiyonu çalışmaz ve Fatal Error verir.
            if ($db instanceof PDO) {
                $sql = "INSERT INTO support_tickets (name, email, subject, message, attachment, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$name, $email, $subject, $msg_content, $fileName]);

                if ($result) {
                    $message = "Destek talebiniz başarıyla oluşturuldu. Destek ekibimiz en kısa sürede size dönüş yapacaktır.";
                    $messageType = "success";
                } else {
                    $message = "Veritabanına kayıt sırasında bir hata oluştu.";
                    $messageType = "error";
                }
            } else {
                // Veritabanı bağlantısı yoksa veya $db hatalıysa Demo Modu
                $fileNameInfo = $fileName ? " (Dosya yüklendi: $fileName)" : "";
                
                $extraInfo = "";
                if (isset($db) && is_string($db)) {
                    $extraInfo = "<br><span class='text-sm opacity-75'>Not: Config dosyasında bağlantı '\$pdo' değişkeninde tutuluyor, ancak '\$db' kullanılıyor. Otomatik düzeltme devreye giremedi.</span>";
                } elseif (is_null($db)) {
                    $extraInfo = "<br><span class='text-sm opacity-75'>Not: Config dosyası yüklenemedi veya bağlantı nesnesi tanımlanmadı.</span>";
                }

                $message = "<strong>Demo Modu:</strong> Veritabanı bağlantısı sağlanamadığı için işlem simüle edildi.$fileNameInfo $extraInfo";
                $messageType = "success";
            }
            
        } catch (PDOException $e) {
            $message = "Veritabanı hatası: " . $e->getMessage();
            $messageType = "error";
        } catch (Exception $e) {
            $message = "Genel hata: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefind - Destek Merkezi</title>
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
                            support: '#8b5cf6', // Mor tema
                            supportDark: '#7c3aed',
                            success: '#10b981',
                            error: '#ef4444'
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
            background-image: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.15), #050505 80%);
            min-height: 100vh;
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .form-input {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
            background: rgba(0, 0, 0, 0.6);
        }

        /* Bildirim Kutusu Animasyonu */
        .notification {
            animation: slideIn 0.5s ease-out forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] summary ~ * { animation: sweep .3s ease-in-out; }
        @keyframes sweep { 0% {opacity: 0; transform: translateY(-10px)} 100% {opacity: 1; transform: translateY(0)} }
        details summary i { transition: transform 0.3s ease; }
        details[open] summary i { transform: rotate(180deg); }
    </style>
</head>
<body class="flex flex-col relative overflow-x-hidden">

    <!-- Arka Plan Süslemeleri -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand-support/10 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <!-- Navbar -->
    <nav class="w-full z-50 border-b border-white/5 bg-black/20 backdrop-blur-md sticky top-0">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="welcome.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-gradient-to-br from-brand-support to-brand-supportDark rounded-xl flex items-center justify-center shadow-lg shadow-brand-support/20 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-headset text-white text-xl"></i>
                </div>
                <span class="font-display font-bold text-2xl tracking-tight">pre<span class="text-brand-support">destek</span></span>
            </a>
            
            <a href="welcome.php" class="hidden md:flex items-center gap-2 text-zinc-400 hover:text-white transition-colors text-sm font-medium border border-white/10 px-4 py-2 rounded-full hover:bg-white/5">
                <i class="fa-solid fa-arrow-left"></i>
                Ana Sayfaya Dön
            </a>
        </div>
    </nav>

    <!-- Ana İçerik -->
    <main class="flex-grow container mx-auto px-6 py-12 relative z-10 max-w-6xl">
        
        <!-- Başlık -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-display font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-b from-white to-white/60">
                Nasıl yardımcı olabiliriz?
            </h1>
            <p class="text-zinc-400 text-lg max-w-2xl mx-auto">
                Teknik sorunlar, hesap işlemleri veya önerilerin için buradayız. Formu doldurun, çözüm üretelim.
            </p>
        </div>

        <!-- PHP Bildirim Alanı -->
        <?php if (!empty($message)): ?>
        <div class="max-w-3xl mx-auto mb-8 notification">
            <div class="p-4 rounded-xl border flex items-center gap-4 <?php echo $messageType == 'success' ? 'bg-brand-success/10 border-brand-success/20 text-brand-success' : 'bg-brand-error/10 border-brand-error/20 text-brand-error'; ?>">
                <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $messageType == 'success' ? 'bg-brand-success/20' : 'bg-brand-error/20'; ?>">
                    <i class="fa-solid <?php echo $messageType == 'success' ? 'fa-check' : 'fa-exclamation'; ?> text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold"><?php echo $messageType == 'success' ? 'Başarılı!' : 'Hata Oluştu'; ?></h4>
                    <p class="text-sm opacity-90"><?php echo $message; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Sol: İletişim Formu -->
            <div class="lg:col-span-2">
                <div class="glass-panel p-8 rounded-3xl">
                    <h2 class="text-2xl font-display font-bold mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-pen-to-square text-brand-support"></i>
                        Destek Talebi Oluştur
                    </h2>
                    
                    <!-- Form Başlangıcı -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" class="space-y-6" id="supportForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Ad Soyad -->
                            <div class="space-y-2">
                                <label class="text-sm text-zinc-400 font-medium ml-1">Ad Soyad</label>
                                <input type="text" name="name" required placeholder="Örn: Ahmet Yılmaz" class="w-full form-input px-4 py-3 rounded-xl placeholder-zinc-600 focus:placeholder-zinc-500">
                            </div>
                            
                            <!-- E-posta -->
                            <div class="space-y-2">
                                <label class="text-sm text-zinc-400 font-medium ml-1">E-posta Adresi</label>
                                <input type="email" name="email" required placeholder="mail@ornek.com" class="w-full form-input px-4 py-3 rounded-xl placeholder-zinc-600 focus:placeholder-zinc-500">
                            </div>
                        </div>

                        <!-- Konu Seçimi -->
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-400 font-medium ml-1">Konu</label>
                            <div class="relative">
                                <select name="subject" required class="w-full form-input px-4 py-3 rounded-xl appearance-none cursor-pointer text-zinc-300">
                                    <option value="" disabled selected>Sorununuzla ilgili bir başlık seçin...</option>
                                    <option value="account">Hesap Sorunları</option>
                                    <option value="technical">Teknik Hata / Bug</option>
                                    <option value="report">Oyuncu Raporlama</option>
                                    <option value="suggestion">Öneri & İşbirliği</option>
                                    <option value="other">Diğer</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Mesaj -->
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-400 font-medium ml-1">Mesajınız</label>
                            <textarea name="message" required rows="5" placeholder="Lütfen sorununuzu detaylı bir şekilde açıklayın..." class="w-full form-input px-4 py-3 rounded-xl placeholder-zinc-600 focus:placeholder-zinc-500 resize-none"></textarea>
                        </div>

                        <!-- Dosya Yükleme -->
                        <div class="relative group">
                            <input type="file" name="attachment" id="fileInput" accept="image/png, image/jpeg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                            <div class="flex items-center gap-4 p-4 border border-dashed border-white/10 rounded-xl bg-white/5 group-hover:bg-white/10 transition" id="fileDisplay">
                                <div class="w-10 h-10 rounded-full bg-brand-support/20 flex items-center justify-center text-brand-support group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-paperclip"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-zinc-300" id="fileNameText">Ekran Görüntüsü Ekle (Opsiyonel)</span>
                                    <p class="text-xs text-zinc-500" id="fileSizeText">Maks. 5MB (JPG, PNG)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Gönder Butonu -->
                        <button type="submit" id="submitBtn" class="w-full py-4 rounded-xl bg-gradient-to-r from-brand-support to-indigo-600 hover:from-brand-supportDark hover:to-indigo-700 text-white font-bold text-lg shadow-lg shadow-brand-support/25 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                            <span>Talebi Gönder</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sağ: SSS ve İletişim Bilgileri -->
            <div class="space-y-6">
                
                <!-- Hızlı İletişim Kartı -->
                <div class="glass-panel p-6 rounded-3xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fa-solid fa-envelope-open-text text-6xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-display font-bold mb-2">Doğrudan İletişim</h3>
                    <p class="text-sm text-zinc-400 mb-4">Acil durumlar için bize mail atabilirsin.</p>
                    <a href="mailto:destek@prefind.com" class="flex items-center gap-3 text-brand-support hover:text-white transition-colors font-medium p-3 rounded-lg hover:bg-brand-support/10 border border-transparent hover:border-brand-support/20">
                        <i class="fa-solid fa-envelope"></i>
                        destek@prefind.com
                    </a>
                    <a href="#" class="flex items-center gap-3 text-[#5865F2] hover:text-white transition-colors font-medium p-3 rounded-lg hover:bg-[#5865F2]/10 border border-transparent hover:border-[#5865F2]/20">
                        <i class="fa-brands fa-discord"></i>
                        Discord Sunucusu
                    </a>
                </div>

                <!-- Sıkça Sorulan Sorular -->
                <div class="glass-panel p-6 rounded-3xl">
                    <h3 class="text-xl font-display font-bold mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-circle-question text-zinc-500"></i>
                        S.S.S
                    </h3>
                    
                    <div class="space-y-4">
                        <details class="group">
                            <summary class="flex justify-between items-center font-medium cursor-pointer list-none text-zinc-300 hover:text-brand-support transition-colors">
                                <span>Nasıl takım bulabilirim?</span>
                                <span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-xs"></i></span>
                            </summary>
                            <p class="text-zinc-500 text-sm mt-3 leading-relaxed pl-2 border-l-2 border-brand-support/30">
                                Ana sayfadan oyununuzu seçtikten sonra filtreleri kullanarak rankınıza uygun ilanları görüntüleyebilir veya kendi ilanınızı oluşturabilirsiniz.
                            </p>
                        </details>
                        <div class="h-px bg-white/5"></div>
                        <details class="group">
                            <summary class="flex justify-between items-center font-medium cursor-pointer list-none text-zinc-300 hover:text-brand-support transition-colors">
                                <span>Üyelik ücretli mi?</span>
                                <span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-xs"></i></span>
                            </summary>
                            <p class="text-zinc-500 text-sm mt-3 leading-relaxed pl-2 border-l-2 border-brand-support/30">
                                Hayır, Prefind tamamen ücretsizdir. Oyuncu bulmak ve ilan oluşturmak için herhangi bir ücret ödemezsiniz.
                            </p>
                        </details>
                        <div class="h-px bg-white/5"></div>
                        <details class="group">
                            <summary class="flex justify-between items-center font-medium cursor-pointer list-none text-zinc-300 hover:text-brand-support transition-colors">
                                <span>Rahatsız eden birini nasıl şikayet ederim?</span>
                                <span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-xs"></i></span>
                            </summary>
                            <p class="text-zinc-500 text-sm mt-3 leading-relaxed pl-2 border-l-2 border-brand-support/30">
                                Kullanıcı profilindeki "Şikayet Et" butonunu kullanabilir veya bu sayfadaki formu "Oyuncu Raporlama" başlığıyla doldurabilirsiniz.
                            </p>
                        </details>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-white/5 bg-black/40 backdrop-blur-md mt-auto py-8">
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
            <p class="text-zinc-500 text-sm">
                &copy; 2025 <span class="text-white">Prefind</span>. Tüm hakları saklıdır.
            </p>
            <div class="flex gap-6">
                <a href="#" class="text-zinc-400 hover:text-white transition-colors text-sm">Gizlilik Politikası</a>
                <a href="#" class="text-zinc-400 hover:text-white transition-colors text-sm">Kullanım Koşulları</a>
            </div>
        </div>
    </footer>

    <!-- Script: Dosya Adı Gösterme & Buton Loading -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dosya adı gösterme fonksiyonu (global scope'a eklendi)
            window.updateFileName = function(input) {
                const fileNameDisplay = document.getElementById('fileNameText');
                const fileDisplayBox = document.getElementById('fileDisplay');
                
                if (input.files && input.files.length > 0) {
                    fileNameDisplay.textContent = input.files[0].name;
                    fileNameDisplay.classList.add('text-brand-support');
                    fileDisplayBox.classList.add('border-brand-support/50', 'bg-brand-support/10');
                } else {
                    fileNameDisplay.textContent = "Ekran Görüntüsü Ekle (Opsiyonel)";
                    fileNameDisplay.classList.remove('text-brand-support');
                    fileDisplayBox.classList.remove('border-brand-support/50', 'bg-brand-support/10');
                }
            };

            // Form gönderilirken buton efekti - Hata kontrolü ile
            const supportForm = document.getElementById('supportForm');
            if (supportForm) {
                supportForm.addEventListener('submit', function() {
                    const btn = document.getElementById('submitBtn');
                    if (btn) {
                        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Gönderiliyor...</span>';
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                    }
                });
            }
        });
    </script>

</body>
</html>