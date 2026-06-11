<?php
require '../config.php';
cekLogin(['superadmin']);

// Cek & Buat Data Default jika kosong
$check = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    // Pastikan kolom nama_sekolah & nama_kegiatan sudah dibuat di DB sesuai instruksi sebelumnya
    mysqli_query($conn, "INSERT INTO pengaturan (tgl_mulai, tgl_selesai, nama_sekolah, nama_kegiatan) VALUES (CURRENT_DATE, CURRENT_DATE, 'Pondok Pesantren', 'Mutaba\'ah Harian')");
}

// HANDLE UPDATE
if (isset($_POST['update'])) {
    $mulai = $_POST['mulai'];
    $selesai = $_POST['selesai'];
    // Menggunakan real_escape_string untuk keamanan input teks
    $sekolah = mysqli_real_escape_string($conn, $_POST['nama_sekolah']);
    $kegiatan = mysqli_real_escape_string($conn, $_POST['nama_kegiatan']);

    // Query Update (Asumsi ID selalu 1)
    $query = "UPDATE pengaturan SET 
              tgl_mulai='$mulai', 
              tgl_selesai='$selesai', 
              nama_sekolah='$sekolah', 
              nama_kegiatan='$kegiatan' 
              WHERE id=1"; 

    if(mysqli_query($conn, $query)) {
        echo "<script>alert('Pengaturan berhasil disimpan!'); window.location='pengaturan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// Ambil Data Terbaru
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));
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

    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static lg:flex flex-col h-full shadow-xl lg:shadow-none">
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
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="home" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i>
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

                <!-- Active State -->
                <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 text-indigo-600 bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                    <i data-lucide="settings" class="w-5 h-5"></i>
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
    <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-gray-50">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <!-- Toggle Button (Mobile) -->
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <span class="font-bold text-lg text-indigo-600 md:hidden">Pengaturan</span>
                
                <div class="hidden md:block">
                    <h2 class="text-lg font-semibold text-gray-800">Konfigurasi Sistem</h2>
                </div>
            </div>

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
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scroll-smooth">
            
            <form method="POST" class="max-w-4xl mx-auto space-y-6">
                
                <!-- Card 1: Identitas Aplikasi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i data-lucide="app-window" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Identitas Aplikasi</h3>
                            <p class="text-xs text-gray-500">Nama sekolah dan judul kegiatan yang tampil di login/header.</p>
                        </div>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Sekolah / Pesantren</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i data-lucide="school" class="w-4 h-4"></i>
                                </span>
                                <input type="text" name="nama_sekolah" 
                                    value="<?= htmlspecialchars($data['nama_sekolah'] ?? '') ?>" 
                                    placeholder="Contoh: Pondok Pesantren Al-Hidayah"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kegiatan</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i data-lucide="tag" class="w-4 h-4"></i>
                                </span>
                                <input type="text" name="nama_kegiatan" 
                                    value="<?= htmlspecialchars($data['nama_kegiatan'] ?? '') ?>" 
                                    placeholder="Contoh: Mutaba'ah Liburan Semester Ganjil"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Pengaturan Waktu -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                        <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                            <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Periode Liburan</h3>
                            <p class="text-xs text-gray-500">Tentukan rentang waktu pelaksanaan mutaba'ah.</p>
                        </div>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </span>
                                <input type="date" name="mulai" value="<?= $data['tgl_mulai'] ?>" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-100 focus:border-amber-500 outline-none transition-all text-sm cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                </span>
                                <input type="date" name="selesai" value="<?= $data['tgl_selesai'] ?>" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-100 focus:border-amber-500 outline-none transition-all text-sm cursor-pointer">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Box -->
                    <div class="px-6 pb-6">
                        <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-lg text-sm flex gap-3 items-start border border-blue-100 shadow-sm">
                            <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5"></i>
                            <p class="leading-relaxed">
                                Di luar rentang tanggal ini, santri tetap bisa login tetapi mungkin mendapat peringatan saat mengisi laporan ("Mengisi masa lalu" atau "Masa depan"). Perubahan tanggal akan mempengaruhi perhitungan <strong>Target Poin Harian</strong> secara otomatis di Dashboard Guru.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pb-10">
                    <a href="dashboard.php" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 transition-all shadow-sm hover:shadow">
                        Batal
                    </a>
                    <button type="submit" name="update" class="px-6 py-2.5 rounded-lg text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg flex items-center gap-2 transform active:scale-95">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
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