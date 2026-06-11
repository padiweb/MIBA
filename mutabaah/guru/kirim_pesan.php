<?php
require '../config.php';
cekLogin(['guru']);

$guru_id = $_SESSION['user_id'];
$kelas_id = $_SESSION['kelas_id'];

// Ambil daftar santri di kelas ini
$q_santri = mysqli_query($conn, "SELECT id, nama_lengkap FROM users WHERE role='santri' AND kelas_id='$kelas_id' ORDER BY nama_lengkap");
$santri_list = [];
while($r = mysqli_fetch_assoc($q_santri)) {
    $santri_list[] = $r;
}

// Handle Kirim Pesan
if (isset($_POST['kirim'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $target = $_POST['target']; // 'all' atau ID santri spesifik

    if ($target == 'all') {
        // Kirim ke semua santri di kelas
        foreach ($santri_list as $s) {
            $penerima = $s['id'];
            mysqli_query($conn, "INSERT INTO pesan (pengirim_id, penerima_id, judul, isi) VALUES ('$guru_id', '$penerima', '$judul', '$isi')");
        }
        echo "<script>alert('Pesan massal berhasil dikirim!'); window.location='dashboard.php';</script>";
    } else {
        // Kirim ke satu santri
        $penerima = (int)$target;
        mysqli_query($conn, "INSERT INTO pesan (pengirim_id, penerima_id, judul, isi) VALUES ('$guru_id', '$penerima', '$judul', '$isi')");
        echo "<script>alert('Pesan berhasil dikirim!'); window.location='dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTQ | Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="max-w-2xl mx-auto p-6 mt-10">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-indigo-600 p-6 flex justify-between items-center">
                <h2 class="text-white text-xl font-bold flex items-center gap-2">
                    <i data-lucide="send" class="w-6 h-6"></i> Kirim Pesan / Tugas
                </h2>
                <a href="dashboard.php" class="text-indigo-200 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </a>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                
                <!-- Pilihan Penerima -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Penerima</label>
                    <select name="target" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="all">📢 Semua Santri (Pesan Massal)</option>
                        <optgroup label="Pilih Perorangan">
                            <?php foreach($santri_list as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <!-- Judul Pesan -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Judul Pesan / Tugas</label>
                    <input type="text" name="judul" required placeholder="Contoh: Tugas Tambahan Hafalan" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>

                <!-- Isi Pesan -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Isi Pesan</label>
                    <textarea name="isi" rows="5" required placeholder="Tulis pesan atau link tugas di sini..." 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                    <p class="text-xs text-slate-400 mt-1">*Anda bisa menyertakan link quiz atau video di sini.</p>
                </div>

                <!-- Tombol -->
                <div class="pt-4 flex justify-end gap-3">
                    <a href="dashboard.php" class="px-4 py-2 rounded-lg text-slate-600 hover:bg-slate-100 font-medium">Batal</a>
                    <button type="submit" name="kirim" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-lg hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5">
                        Kirim Sekarang
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>