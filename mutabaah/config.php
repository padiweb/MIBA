<?php
session_start();
$host = 'localhost';
$user = 'u691369715_mutabaah';     // <-- DB User
$pass = '/2W7U^35*dS';     // <-- User Password    
$db   = 'u691369715_mutabaah';     // <-- DB Name

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// --- PENGATURAN GLOBAL APLIKASI ---
// Ambil data pengaturan sekolah & kegiatan sekali saja di sini
$query_setting = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
$app_setting = mysqli_fetch_assoc($query_setting);

// Set variabel global dengan fallback default jika database kosong
$nama_sekolah = $app_setting['nama_sekolah'] ?? 'Mutaba\'ah App';
$nama_kegiatan = $app_setting['nama_kegiatan'] ?? 'Kegiatan Liburan Santri';

// --- KONFIGURASI AI ---
define('GEMINI_API_KEY', 'AIzaSyAb0UrMJJjr2wb06q-T-omwOuzCr5yHaDw');   // diisi Gemini Api_Key

// Fungsi Helper untuk cek login
function cekLogin($role_required = []) {
    // Sesuaikan path redirect jika file ada di dalam folder sub (admin/guru/santri) atau root
    // Deteksi kedalaman folder sederhana
    $path_level = substr_count($_SERVER['PHP_SELF'], '/') - 1; // asumsi root adalah level 1
    $login_path = ($path_level > 1) ? "../login.php" : "login.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: $login_path");
        exit;
    }
    if (!empty($role_required) && !in_array($_SESSION['role'], $role_required)) {
        echo "Akses ditolak!";
        exit;
    }
}
?>