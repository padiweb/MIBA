<?php
require '../config.php';
cekLogin(['superadmin']);

$edit_data = null;

// --- HANDLE TAMBAH GURU ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $kelas = $_POST['kelas_id'] ?: 'NULL';
    
    // Cek username unik
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
    if(mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal: Username sudah digunakan!'); window.location='guru.php';</script>";
    } else {
        $query = "INSERT INTO users (username, password, nama_lengkap, role, kelas_id) VALUES ('$user', '$pass', '$nama', 'guru', $kelas)";
        if(mysqli_query($conn, $query)) {
            echo "<script>alert('Guru berhasil ditambahkan'); window.location='guru.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// --- HANDLE UPDATE GURU ---
if (isset($_POST['update'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $kelas = $_POST['kelas_id'] ?: 'NULL';
    
    // Cek username unik (kecuali punya sendiri)
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username='$user' AND id != $id");
    if(mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal: Username sudah digunakan orang lain!'); window.location='guru.php';</script>";
    } else {
        $sql_pass = "";
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql_pass = ", password='$pass'";
        }

        $query = "UPDATE users SET nama_lengkap='$nama', username='$user', kelas_id=$kelas $sql_pass WHERE id=$id AND role='guru'";
        
        if(mysqli_query($conn, $query)) {
            echo "<script>alert('Data Guru berhasil diupdate'); window.location='guru.php';</script>";
        } else {
            echo "<script>alert('Gagal Update');</script>";
        }
    }
}

// --- AMBIL DATA EDIT ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id=$id AND role='guru'");
    $edit_data = mysqli_fetch_assoc($q);
}

// --- HANDLE HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='guru'");
    header("Location: guru.php");
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
                
                <!-- Active State -->
                <a href="guru.php" class="flex items-center gap-3 px-4 py-3 text-indigo-600 bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                    <i data-lucide="users" class="w-5 h-5"></i>
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
    <div class="flex-1 flex flex-col h-full overflow-hidden relative bg-gray-50">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <!-- Toggle Button (Mobile) -->
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <span class="font-bold text-lg text-indigo-600 md:hidden">Data Guru</span>
                
                <!-- Desktop Title -->
                <div class="hidden md:block">
                    <h2 class="text-lg font-semibold text-gray-800">Manajemen Data Guru</h2>
                </div>
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
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scroll-smooth">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Form Section (Left/Top) - Sticky -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6 transition-all">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <i data-lucide="<?= $edit_data ? 'user-cog' : 'user-plus' ?>" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800"><?= $edit_data ? 'Edit Data Guru' : 'Tambah Guru Baru' ?></h3>
                        </div>

                        <form method="POST" class="space-y-5">
                            <?php if($edit_data): ?>
                                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                    </span>
                                    <input type="text" name="nama" 
                                        placeholder="Nama Lengkap Guru" 
                                        value="<?= $edit_data['nama_lengkap'] ?? '' ?>" 
                                        required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="at-sign" class="w-4 h-4"></i>
                                    </span>
                                    <input type="text" name="username" 
                                        placeholder="Username Login" 
                                        value="<?= $edit_data['username'] ?? '' ?>" 
                                        required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Password 
                                    <?php if($edit_data): ?>
                                        <span class="text-xs font-normal text-gray-400 ml-1">(Kosongkan jika tidak diubah)</span>
                                    <?php endif; ?>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </span>
                                    <input type="password" name="password" 
                                        placeholder="••••••••" 
                                        <?= $edit_data ? '' : 'required' ?>
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Wali Kelas Untuk</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </span>
                                    <select name="kelas_id" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm bg-white">
                                        <option value="">-- Bukan Wali Kelas --</option>
                                        <?php
                                        $k = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                        while ($row = mysqli_fetch_assoc($k)) {
                                            $selected = ($edit_data && $edit_data['kelas_id'] == $row['id']) ? 'selected' : '';
                                            echo "<option value='{$row['id']}' $selected>{$row['nama_kelas']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button type="submit" name="<?= $edit_data ? 'update' : 'tambah' ?>" 
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all hover:shadow-md flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <?= $edit_data ? 'Simpan Perubahan' : 'Simpan Guru' ?>
                                </button>
                                
                                <?php if($edit_data): ?>
                                    <a href="guru.php" class="px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors shadow-sm">
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
                                <i data-lucide="list" class="w-4 h-4 text-gray-400"></i> Daftar Guru
                            </h3>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                                Total: <?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='guru'")) ?>
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Nama Lengkap</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Username</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Wali Kelas</th>
                                        <th class="px-6 py-3 font-semibold text-right tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php
                                    $q = mysqli_query($conn, "SELECT users.*, kelas.nama_kelas FROM users LEFT JOIN kelas ON users.kelas_id = kelas.id WHERE role='guru' ORDER BY users.nama_lengkap");
                                    if (mysqli_num_rows($q) > 0):
                                        while ($row = mysqli_fetch_assoc($q)):
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors group">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-100 shadow-sm">
                                                    <?= substr($row['nama_lengkap'], 0, 1) ?>
                                                </div>
                                                <?= $row['nama_lengkap'] ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono text-gray-700">
                                                <?= $row['username'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($row['nama_kelas']): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i data-lucide="graduation-cap" class="w-3 h-3"></i>
                                                    <?= $row['nama_kelas'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs italic pl-2">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="guru.php?edit=<?= $row['id'] ?>" class="p-1.5 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 border border-transparent hover:border-amber-200 transition-all shadow-sm" title="Edit">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </a>
                                                <a href="guru.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data guru ini? Tindakan ini tidak bisa dibatalkan.')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 border border-transparent hover:border-red-200 transition-all shadow-sm" title="Hapus">
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
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="p-3 bg-gray-50 rounded-full">
                                                    <i data-lucide="user-x" class="w-6 h-6 opacity-40"></i>
                                                </div>
                                                <span class="text-sm">Belum ada data guru. Silakan tambah baru.</span>
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