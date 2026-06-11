<?php
require '../config.php';
cekLogin(['superadmin']);

// Ambil Statistik
$count_santri = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='santri'"));
$count_guru = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='guru'"));
$count_kelas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kelas"));
$setting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));

// Format Tanggal Indonesia
function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
		'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
	);
	$pecahkan = explode('-', $tanggal);
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTQ | Mutaba'ah</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        /* Transisi Sidebar Halus */
        #sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="text-gray-800 antialiased flex h-screen overflow-hidden">

    <!-- Mobile Overlay (Background gelap saat sidebar aktif di HP) -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static lg:flex flex-col h-full shadow-xl lg:shadow-none transition-transform duration-300 ease-in-out">
        <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
            <div class="flex items-center gap-2 text-indigo-600">
                <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                <span class="font-bold text-xl tracking-tight">MTQ Ibnu Abbas</span>
            </div>
            <!-- Tombol Close Sidebar (Mobile Only) -->
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-gray-600 focus:outline-none">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
            <nav class="px-4 space-y-1">
                <!-- Menu Dashboard (Active) -->
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-indigo-600 bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Dashboard
                </a>
                
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Data</div>
                
                <a href="kelas.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
                    Data Kelas
                </a>
                <a href="guru.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="users" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
                    Data Guru
                </a>
                <a href="santri.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="user-check" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
                    Data Santri
                </a>
                <a href="kegiatan.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="list-todo" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
                    Master Kegiatan
                </a>

                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sistem</div>

                <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="settings" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
                    Pengaturan Sistem
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-100">
            <a href="../logout.php" class="flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors w-full group">
                <i data-lucide="log-out" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <!-- Toggle Button (Mobile) -->
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <div class="hidden md:block">
                    <h2 class="text-lg font-semibold text-gray-800">Overview</h2>
                </div>
                <span class="font-bold text-lg text-indigo-600 md:hidden">AdminPanel</span>
            </div>

            <!-- User Profile -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['nama']) ?></p>
                    <p class="text-xs text-gray-500">Super Administrator</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border-2 border-white shadow-sm">
                    <?= substr($_SESSION['nama'], 0, 1) ?>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl p-6 sm:p-10 text-white shadow-lg mb-8 relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Ahlan wa Sahlan, Ustadz/ah 👋</h1>
                    <p class="text-indigo-100 max-w-xl text-sm sm:text-base">Selamat datang di panel kontrol aplikasi Mutaba'ah. Pantau perkembangan santri dan kelola data akademik dengan mudah di sini.</p>
                </div>
                <!-- Decorative Circles -->
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 right-20 w-20 h-20 bg-indigo-400 opacity-20 rounded-full blur-xl"></div>
            </div>

            <!-- Periode Info -->
            <div class="bg-white border-l-4 border-amber-400 rounded-r-xl shadow-sm p-4 mb-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="flex items-center gap-4 w-full">
                    <div class="bg-amber-50 p-2 rounded-full text-amber-600 shrink-0">
                        <i data-lucide="calendar-range" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-amber-600 uppercase tracking-wide mb-0.5">Periode Liburan Aktif</p>
                        <p class="text-gray-700 font-medium text-sm sm:text-base">
                            <?= tgl_indo($setting['tgl_mulai']) ?> <span class="mx-2 text-gray-300">|</span> <?= tgl_indo($setting['tgl_selesai']) ?>
                        </p>
                    </div>
                </div>
                <div class="w-full sm:w-auto pl-11 sm:pl-0">
                    <a href="pengaturan.php" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 hover:underline">
                        Ubah <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Card Santri -->
                <a href="santri.php" class="group bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all duration-300 relative overflow-hidden">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Total Santri</p>
                            <h3 class="text-3xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors"><?= $count_santri ?></h3>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs font-medium text-gray-400 group-hover:text-gray-500">
                        <span>Kelola data santri</span>
                        <i data-lucide="arrow-right" class="w-3 h-3 ml-1 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

                <!-- Card Guru -->
                <a href="guru.php" class="group bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-100 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Total Guru</p>
                            <h3 class="text-3xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors"><?= $count_guru ?></h3>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors shadow-sm">
                            <i data-lucide="user-check" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs font-medium text-gray-400 group-hover:text-gray-500">
                        <span>Kelola data pengajar</span>
                        <i data-lucide="arrow-right" class="w-3 h-3 ml-1 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

                <!-- Card Kelas -->
                <a href="kelas.php" class="group bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-rose-100 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Total Kelas</p>
                            <h3 class="text-3xl font-bold text-gray-800 group-hover:text-rose-600 transition-colors"><?= $count_kelas ?></h3>
                        </div>
                        <div class="bg-rose-50 p-3 rounded-xl text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors shadow-sm">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs font-medium text-gray-400 group-hover:text-gray-500">
                        <span>Kelola rombel belajar</span>
                        <i data-lucide="arrow-right" class="w-3 h-3 ml-1 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

            </div>

            <!-- Quick Actions -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 px-1 flex items-center gap-2">
                <i data-lucide="zap" class="w-5 h-5 text-amber-500"></i> Akses Cepat
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="kegiatan.php" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-center justify-center text-center gap-3 group">
                    <div class="bg-purple-50 p-3 rounded-full text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors shadow-sm">
                        <i data-lucide="list-todo" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-purple-700">Master Kegiatan</span>
                </a>
                
                <a href="pengaturan.php" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-center justify-center text-center gap-3 group">
                    <div class="bg-orange-50 p-3 rounded-full text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors shadow-sm">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-orange-700">Set Waktu Libur</span>
                </a>
                
                <!-- Placeholder -->
                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300 flex flex-col items-center justify-center text-center gap-2 opacity-60 cursor-not-allowed">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-gray-400"></i>
                    <span class="text-sm font-medium text-gray-400">Laporan Statistik</span>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300 flex flex-col items-center justify-center text-center gap-2 opacity-60 cursor-not-allowed">
                    <i data-lucide="file-text" class="w-6 h-6 text-gray-400"></i>
                    <span class="text-sm font-medium text-gray-400">Export Data</span>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Inisialisasi Icon Lucide
        lucide.createIcons();

        // Fungsi Toggle Sidebar Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>