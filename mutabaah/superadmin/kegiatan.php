<?php
require '../config.php';
cekLogin(['superadmin']);

$edit_data = null;

// --- HANDLE TAMBAH KEGIATAN ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = $_POST['kategori'];
    $poin = (int) $_POST['poin'];
    $udzur = (int) $_POST['ada_udzur'];

    $query = "INSERT INTO kegiatan (nama_kegiatan, kategori, poin, ada_udzur) VALUES ('$nama', '$kategori', '$poin', '$udzur')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Kegiatan berhasil ditambahkan'); window.location='kegiatan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// --- HANDLE UPDATE KEGIATAN ---
if (isset($_POST['update'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = $_POST['kategori'];
    $poin = (int) $_POST['poin'];
    $udzur = (int) $_POST['ada_udzur'];

    $query = "UPDATE kegiatan SET nama_kegiatan='$nama', kategori='$kategori', poin='$poin', ada_udzur='$udzur' WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Kegiatan berhasil diupdate'); window.location='kegiatan.php';</script>";
    } else {
        echo "<script>alert('Gagal Update');</script>";
    }
}

// --- AMBIL DATA EDIT ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM kegiatan WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($q);
}

// --- HANDLE HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kegiatan WHERE id=$id");
    header("Location: kegiatan.php");
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

                <!-- Active State -->
                <a href="kegiatan.php" class="flex items-center gap-3 px-4 py-3 text-indigo-600 bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                    <i data-lucide="list-todo" class="w-5 h-5"></i>
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
    <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-gray-50">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <!-- Toggle Button (Mobile) -->
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <span class="font-bold text-lg text-indigo-600 md:hidden">Master Kegiatan</span>
                
                <div class="hidden md:block">
                    <h2 class="text-lg font-semibold text-gray-800">Master Kegiatan & Poin</h2>
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
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Form Section (Left/Top) - Sticky -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6 transition-all">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <i data-lucide="<?= $edit_data ? 'edit-3' : 'plus-square' ?>" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800"><?= $edit_data ? 'Edit Kegiatan' : 'Tambah Kegiatan' ?></h3>
                        </div>

                        <form method="POST" class="space-y-5">
                            <?php if($edit_data): ?>
                                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kegiatan</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="type" class="w-4 h-4"></i>
                                    </span>
                                    <input type="text" name="nama" 
                                        placeholder="Contoh: Sholat Subuh" 
                                        value="<?= $edit_data['nama_kegiatan'] ?? '' ?>" 
                                        required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="tag" class="w-4 h-4"></i>
                                    </span>
                                    <select name="kategori" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm bg-white">
                                        <option value="ibadah_wajib" <?= ($edit_data['kategori']??'')=='ibadah_wajib'?'selected':'' ?>>Ibadah Wajib</option>
                                        <option value="ibadah_sunnah" <?= ($edit_data['kategori']??'')=='ibadah_sunnah'?'selected':'' ?>>Ibadah Sunnah</option>
                                        <option value="kegiatan_sosial" <?= ($edit_data['kategori']??'')=='kegiatan_sosial'?'selected':'' ?>>Kegiatan Sosial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Poin</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                            <i data-lucide="star" class="w-4 h-4"></i>
                                        </span>
                                        <input type="number" name="poin" 
                                            value="<?= $edit_data['poin'] ?? 1 ?>" 
                                            required 
                                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Udzur Haidh?</label>
                                    <select name="ada_udzur" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm bg-white">
                                        <option value="0" <?= ($edit_data['ada_udzur']??0)==0?'selected':'' ?>>Tidak</option>
                                        <option value="1" <?= ($edit_data['ada_udzur']??0)==1?'selected':'' ?>>Ya</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex gap-2 items-start">
                                <i data-lucide="info" class="w-4 h-4 text-blue-500 mt-0.5 shrink-0"></i>
                                <p class="text-xs text-blue-600 leading-tight">
                                    Pilih <strong>'Ya'</strong> pada Udzur Haidh untuk kegiatan yang gugur kewajibannya bagi wanita (cth: Sholat, Puasa).
                                </p>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="submit" name="<?= $edit_data ? 'update' : 'tambah' ?>" 
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all hover:shadow-md flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <?= $edit_data ? 'Simpan Perubahan' : 'Simpan Kegiatan' ?>
                                </button>
                                
                                <?php if($edit_data): ?>
                                    <a href="kegiatan.php" class="px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors shadow-sm text-center">
                                        Batal
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Section (Right/Bottom) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i data-lucide="list" class="w-4 h-4 text-gray-400"></i> Daftar Kegiatan
                            </h3>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                                Total: <?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM kegiatan")) ?>
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Nama Kegiatan</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Kategori</th>
                                        <th class="px-6 py-3 font-semibold text-center tracking-wider">Poin</th>
                                        <th class="px-6 py-3 font-semibold text-center tracking-wider">Udzur</th>
                                        <th class="px-6 py-3 font-semibold text-right tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php
                                    $q = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY kategori ASC, id ASC");
                                    if (mysqli_num_rows($q) > 0):
                                        while ($row = mysqli_fetch_assoc($q)):
                                            // Badge Warna Kategori
                                            $badgeClass = 'bg-gray-100 text-gray-800';
                                            if($row['kategori'] == 'ibadah_wajib') $badgeClass = 'bg-red-50 text-red-700 border border-red-100';
                                            elseif($row['kategori'] == 'ibadah_sunnah') $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-100';
                                            elseif($row['kategori'] == 'kegiatan_sosial') $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-100';
                                            
                                            $kategoriLabel = ucwords(str_replace('_', ' ', $row['kategori']));
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors group">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <?= $row['nama_kegiatan'] ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                                <?= $kategoriLabel ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="font-mono font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                                <?= $row['poin'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if($row['ada_udzur']): ?>
                                                <div class="flex justify-center">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-pink-50 text-pink-600 rounded-full border border-pink-100" title="Ada Udzur Haidh">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                <a href="kegiatan.php?edit=<?= $row['id'] ?>" class="p-1.5 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 border border-transparent hover:border-amber-200 transition-all shadow-sm" title="Edit">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </a>
                                                <a href="kegiatan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus kegiatan ini?')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 border border-transparent hover:border-red-200 transition-all shadow-sm" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="p-3 bg-gray-50 rounded-full">
                                                    <i data-lucide="clipboard-list" class="w-6 h-6 opacity-40"></i>
                                                </div>
                                                <span class="text-sm">Belum ada data kegiatan.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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