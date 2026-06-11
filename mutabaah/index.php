<?php
require 'config.php';

// Ambil data pengaturan sekolah & kegiatan untuk ditampilkan di splash screen
// Variabel $nama_sekolah dan $nama_kegiatan sudah tersedia dari config.php
$query_setting = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
$setting = mysqli_fetch_assoc($query_setting);

// Format tanggal Indonesia sederhana
function tgl_indo_short($tanggal){
	$bulan = array (
		1 =>   'Jan',
		'Feb',
		'Mar',
		'Apr',
		'Mei',
		'Jun',
		'Jul',
		'Agt',
		'Sep',
		'Okt',
		'Nov',
		'Des'
	);
	$pecahkan = explode('-', $tanggal);
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

$periode = tgl_indo_short($setting['tgl_mulai']) . " - " . tgl_indo_short($setting['tgl_selesai']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang | <?= htmlspecialchars($nama_sekolah) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Animasi Fade In & Slide Up */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-enter {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; opacity: 0; }
        .delay-200 { animation-delay: 0.3s; opacity: 0; }
        .delay-300 { animation-delay: 0.5s; opacity: 0; }

        /* Loading Bar Animation */
        @keyframes load {
            0% { width: 0; }
            100% { width: 100%; }
        }
        .loading-bar {
            animation: load 5s linear forwards;
        }
    </style>
    
    <!-- Auto Redirect setelah 5 detik -->
    <meta http-equiv="refresh" content="5;url=login.php" />
</head>
<body class="bg-gradient-to-br from-emerald-600 to-teal-800 min-h-screen flex flex-col items-center justify-center text-white relative overflow-hidden">

    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-400 opacity-10 rounded-full blur-3xl"></div>
    </div>

    <div class="text-center z-10 px-6">
        
        <!-- Logo / Icon -->
        <div class="mb-8 flex justify-center animate-enter">
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-3xl shadow-2xl border border-white/20">
                <!-- Ganti dengan <img> jika punya logo sekolah -->
                <i data-lucide="book-open-check" class="w-20 h-20 text-emerald-100"></i>
            </div>
        </div>

        <!-- Nama Sekolah -->
        <h1 class="text-3xl md:text-4xl font-bold mb-2 tracking-tight animate-enter delay-100">
            <?= htmlspecialchars($nama_sekolah) ?>
        </h1>

        <!-- Nama Kegiatan -->
        <p class="text-xl md:text-2xl text-emerald-100 font-medium mb-6 animate-enter delay-200">
            <?= htmlspecialchars($nama_kegiatan) ?>
        </p>

        <!-- Periode -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/10 animate-enter delay-300">
            <i data-lucide="calendar-range" class="w-4 h-4 text-emerald-200"></i>
            <span class="text-sm font-medium tracking-wide text-emerald-50">
                Periode: <?= $periode ?>
            </span>
        </div>

    </div>

    <!-- Loading Indicator di Bawah -->
    <div class="absolute bottom-10 w-64 h-1.5 bg-white/20 rounded-full overflow-hidden">
        <div class="h-full bg-white loading-bar rounded-full shadow-[0_0_10px_rgba(255,255,255,0.5)]"></div>
    </div>
    
    <p class="absolute bottom-4 text-xs text-emerald-200/60 font-medium">Memuat Aplikasi...</p>

    <script>
        lucide.createIcons();
        
        // Fallback JS redirect jika meta tag tidak jalan di beberapa browser
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
    </script>
</body>
</html>