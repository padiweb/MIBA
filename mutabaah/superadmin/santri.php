<?php
require '../config.php';
cekLogin(['superadmin']);
$edit_data = null;

// --- HANDLE TAMBAH SANTRI ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $kelas = $_POST['kelas_id'] ?: 'NULL';
    $hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $jk = $_POST['jk'];
    
    // Validasi Username Unik
    $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user'");
    if(mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Gagal: Username sudah digunakan oleh pengguna lain!'); window.location='santri.php';</script>";
    } else {
        $query = "INSERT INTO users (username, password, nama_lengkap, role, kelas_id, no_hp_ortu, jenis_kelamin) 
                  VALUES ('$user', '$pass', '$nama', 'santri', $kelas, '$hp', '$jk')";
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Santri berhasil ditambahkan'); window.location='santri.php';</script>";
        } else {
            echo "<script>alert('Gagal: ".mysqli_error($conn)."');</script>";
        }
    }
}

// --- HANDLE UPDATE SANTRI ---
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $kelas = $_POST['kelas_id'] ?: 'NULL';
    $hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $jk = $_POST['jk'];

    // Cek username unik (kecuali milik user itu sendiri)
    $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user' AND id != $id");
    if(mysqli_num_rows($cek_user) > 0) {
         echo "<script>alert('Gagal: Username sudah digunakan oleh user lain!'); window.location='santri.php';</script>";
    } else {
        $sql_pass = "";
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql_pass = ", password='$pass'";
        }
        $query = "UPDATE users SET nama_lengkap='$nama', username='$user', kelas_id=$kelas, no_hp_ortu='$hp', jenis_kelamin='$jk' $sql_pass WHERE id=$id AND role='santri'";
        
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Data Santri berhasil diupdate'); window.location='santri.php';</script>";
        } else {
            echo "<script>alert('Gagal Update: ".mysqli_error($conn)."');</script>";
        }
    }
}

// --- AMBIL DATA EDIT ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id=$id AND role='santri'");
    $edit_data = mysqli_fetch_assoc($q);
}

// --- HANDLE HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='santri'");
    header("Location: santri.php");
}

// --- CONFIG PAGINASI & FILTER ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter Kelas
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';
$where_clause = "WHERE role='santri'";
if (!empty($filter_kelas)) {
    $where_clause .= " AND users.kelas_id = '$filter_kelas'";
}

// Hitung Total Data (untuk paginasi)
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $where_clause");
$total_data = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_data / $limit);

// Query Data Utama
$q_santri = mysqli_query($conn, "SELECT users.*, kelas.nama_kelas 
                                 FROM users 
                                 LEFT JOIN kelas ON users.kelas_id = kelas.id 
                                 $where_clause 
                                 ORDER BY kelas.nama_kelas ASC, users.nama_lengkap ASC 
                                 LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTQ | Mutaba'ah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
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
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-gray-600 focus:outline-none"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
            <nav class="px-4 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="home" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i> Dashboard
                </a>
                
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Data</div>
                
                <a href="kelas.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i> Data Kelas
                </a>
                
                <a href="guru.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="users" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i> Data Guru
                </a>

                <!-- Active State -->
                <a href="santri.php" class="flex items-center gap-3 px-4 py-3 text-indigo-600 bg-indigo-50 rounded-lg font-medium transition-colors shadow-sm">
                    <i data-lucide="user-check" class="w-5 h-5"></i> Data Santri
                </a>

                <a href="kegiatan.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="list-todo" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i> Master Kegiatan
                </a>

                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sistem</div>

                <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors group">
                    <i data-lucide="settings" class="w-5 h-5 text-gray-400 group-hover:text-indigo-500"></i> Pengaturan Sistem
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-100">
            <a href="../logout.php" class="flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors w-full group">
                <i data-lucide="log-out" class="w-5 h-5 group-hover:scale-110 transition-transform"></i> <span class="font-medium">Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="hidden md:block"><h2 class="text-lg font-semibold text-gray-800">Manajemen Santri</h2></div>
                <span class="font-bold text-lg text-indigo-600 md:hidden">Data Santri</span>
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
        <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 scroll-smooth">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Form Section (Sticky) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6 transition-all">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><i data-lucide="<?= $edit_data ? 'user-cog' : 'user-plus' ?>" class="w-5 h-5"></i></div>
                            <h3 class="text-lg font-bold text-gray-800"><?= $edit_data ? 'Edit Data Santri' : 'Tambah Santri Baru' ?></h3>
                        </div>

                        <form method="POST" class="space-y-4">
                            <?php if($edit_data): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= $edit_data['nama_lengkap'] ?? '' ?>" required class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="jk" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                    <option value="L" <?= ($edit_data['jenis_kelamin']??'')=='L'?'selected':'' ?>>Laki-laki</option>
                                    <option value="P" <?= ($edit_data['jenis_kelamin']??'')=='P'?'selected':'' ?>>Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                                <select name="kelas_id" required class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php
                                    $k = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                    while ($row = mysqli_fetch_assoc($k)) {
                                        $selected = ($edit_data && $edit_data['kelas_id'] == $row['id']) ? 'selected' : '';
                                        echo "<option value='{$row['id']}' $selected>{$row['nama_kelas']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input type="text" name="username" value="<?= $edit_data['username'] ?? '' ?>" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <input type="password" name="password" placeholder="••••••" <?= $edit_data ? '' : 'required' ?> class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No HP Ortu</label>
                                <input type="text" name="no_hp" value="<?= $edit_data['no_hp_ortu'] ?? '' ?>" required placeholder="628..." class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="submit" name="<?= $edit_data ? 'update' : 'tambah' ?>" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                    <i data-lucide="save" class="w-4 h-4"></i> <?= $edit_data ? 'Simpan' : 'Tambah' ?>
                                </button>
                                <?php if($edit_data): ?><a href="santri.php" class="px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors shadow-sm text-center">Batal</a><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="lg:col-span-2">
                    
                    <!-- Toolbar: Import & Filter -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex gap-3 w-full sm:w-auto">
                            <button onclick="document.getElementById('modalImport').style.display='flex'" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
                                <i data-lucide="upload" class="w-4 h-4"></i> Import
                            </button>
                            <a href="template_santri.csv" download class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
                                <i data-lucide="download" class="w-4 h-4"></i> Template
                            </a>
                        </div>
                        
                        <!-- Filter Kelas Form -->
                        <form method="GET" class="w-full sm:w-auto">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="filter" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <select name="filter_kelas" onchange="this.form.submit()" class="pl-10 pr-8 py-2 w-full sm:w-48 bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block">
                                    <option value="">Semua Kelas</option>
                                    <?php
                                    $k_filter = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                    while ($row_k = mysqli_fetch_assoc($k_filter)) {
                                        $selected = ($filter_kelas == $row_k['id']) ? 'selected' : '';
                                        echo "<option value='{$row_k['id']}' $selected>{$row_k['nama_kelas']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i data-lucide="list" class="w-4 h-4 text-gray-400"></i> Daftar Santri
                            </h3>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                                Total: <?= $total_data ?>
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Nama Lengkap</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Username</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">Kelas</th>
                                        <th class="px-6 py-3 font-semibold tracking-wider">No HP</th>
                                        <th class="px-6 py-3 font-semibold text-right tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php
                                    if (mysqli_num_rows($q_santri) > 0):
                                        while ($row = mysqli_fetch_assoc($q_santri)):
                                            $bgAvatar = $row['jenis_kelamin'] == 'L' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100';
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors group">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs border <?= $bgAvatar ?> shadow-sm">
                                                    <?= substr($row['nama_lengkap'], 0, 1) ?>
                                                </div>
                                                <div>
                                                    <?= $row['nama_lengkap'] ?>
                                                    <span class="text-xs text-gray-400 block"><?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 font-mono text-xs bg-gray-50 rounded-sm inline-block my-3 ml-4"><?= $row['username'] ?></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                <?= $row['nama_kelas'] ?? '-' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 text-xs font-mono"><?= $row['no_hp_ortu'] ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                <a href="santri.php?edit=<?= $row['id'] ?>" class="p-1.5 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 border border-transparent hover:border-amber-200 transition-all shadow-sm" title="Edit"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                                <a href="santri.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data santri ini?')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 border border-transparent hover:border-red-200 transition-all shadow-sm" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400"><div class="flex flex-col items-center gap-2"><i data-lucide="user-x" class="w-8 h-8 opacity-50"></i><span class="text-sm">Belum ada data santri.</span></div></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2 bg-gray-50/30">
                            <?php if($page > 1): ?>
                                <a href="?page=<?= $page-1 ?>&filter_kelas=<?= $filter_kelas ?>" class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-white hover:text-indigo-600 transition-colors">Previous</a>
                            <?php endif; ?>
                            
                            <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>&filter_kelas=<?= $filter_kelas ?>" class="px-3 py-1 text-sm border rounded <?= ($i==$page) ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-200 hover:bg-white hover:text-indigo-600' ?> transition-colors"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if($page < $total_pages): ?>
                                <a href="?page=<?= $page+1 ?>&filter_kelas=<?= $filter_kelas ?>" class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-white hover:text-indigo-600 transition-colors">Next</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Import CSV -->
    <div id="modalImport" class="modal hidden items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modalImport').style.display='none'"></div>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative m-4 z-10">
            <button onclick="document.getElementById('modalImport').style.display='none'" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3"><i data-lucide="file-spreadsheet" class="w-6 h-6"></i></div>
                <h3 class="text-lg font-bold text-gray-900">Import Data Santri</h3>
                <p class="text-sm text-gray-500 mt-1">Upload file CSV sesuai template.</p>
            </div>
            <form action="import_santri.php" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-500 transition-colors cursor-pointer relative bg-gray-50 hover:bg-emerald-50">
                    <input type="file" name="file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="pointer-events-none">
                        <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2 group-hover:text-emerald-500"></i>
                        <span class="text-sm text-gray-600 font-medium block">Klik untuk pilih file CSV</span>
                        <span class="text-xs text-gray-400 mt-1 block">(Max 2MB)</span>
                    </div>
                </div>
                <button type="submit" name="import" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg transition-colors shadow-md flex items-center justify-center gap-2 transform active:scale-95"><i data-lucide="check-circle" class="w-4 h-4"></i> Proses Import</button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>