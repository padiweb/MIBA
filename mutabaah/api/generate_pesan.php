<?php
require '../config.php';

// Pastikan hanya guru/admin yang bisa akses endpoint ini
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'superadmin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Ambil data JSON dari request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Data tidak valid']);
    exit;
}

$nama = $input['nama'];
$wajib = $input['wajib'];
$sunnah = $input['sunnah'];
$sosial = $input['sosial'];
$disiplin = $input['disiplin'];
$total_hari = $input['total_hari']; // Ini sekarang akan kita interpretasikan sebagai "Hari ke-X" di prompt
$guru = $input['guru'] ?? 'Ustadz/Ustadzah';
$kelas = $input['kelas'] ?? 'Santri';

// --- LOGIKA PERHITUNGAN HARI BERJALAN ---
// Ambil tanggal mulai dari database untuk menghitung hari ke-berapa sekarang
$q_set = mysqli_query($conn, "SELECT tgl_mulai FROM pengaturan LIMIT 1");
$set = mysqli_fetch_assoc($q_set);
$start_date = new DateTime($set['tgl_mulai']);
$today = new DateTime();

// Hitung selisih hari (+1 agar hari pertama dihitung hari ke-1)
$interval = $start_date->diff($today);
$hari_ke = $interval->days + 1;

// Jika belum mulai, set hari ke-1
if ($today < $start_date) {
    $hari_ke = 1;
}

// --- SUSUN PROMPT AI ---
$prompt = "Berperanlah sebagai **$guru**, wali kelas **$kelas**. 
Buatkan pesan WhatsApp personal untuk **Orang Tua/Wali** dari santri bernama **$nama**.

**Konteks Laporan:**
Ini adalah laporan perkembangan amalan harian santri selama liburan yang **sedang berjalan (On-Going)**.
Saat ini baru memasuki **Hari ke-$hari_ke**.

**Data Capaian Sampai Hari Ini:**
- Poin Ibadah Wajib: $wajib
- Poin Ibadah Sunnah: $sunnah
- Poin Kegiatan Sosial: $sosial
- Tingkat Kedisiplinan Lapor: $disiplin%

**Instruksi Penulisan (PENTING):**
1. **Sapaan:** Gunakan 'Assalamu'alaikum Warahmatullahi Wabarakatuh, Bapak/Ibu Wali Santri dari Ananda $nama...'.
2. **Subjek:** Gunakan kata ganti 'Ananda' atau 'Putra/Putri Bapak/Ibu'. JANGAN gunakan 'santri Bapak/Ibu'.
3. **Isi Pesan:** - Informasikan bahwa ini adalah laporan sementara sampai hari ke-$hari_ke.
   - Review capaian ibadah wajibnya. Jika poin masih rendah/kosong, mohon bantuan orang tua untuk mengingatkan kembali pentingnya sholat wajib.
   - Komentari kedisiplinan lapornya. Jika < 50%, himbau agar mengisi laporan setiap hari dan tidak dirapel.
   - Jika poin bagus, berikan apresiasi dan motivasi untuk istiqomah sampai liburan selesai.
4. **Format:** Tulis dalam bentuk paragraf yang luwes dan enak dibaca di WhatsApp. 
5. **Gaya Bahasa:** Sopan, hormat, namun akrab.
6. **LARANGAN:** JANGAN gunakan format markdown seperti huruf tebal (**bold**) atau miring (*italic*) karena akan dikirim sebagai teks biasa. Gunakan huruf kapital atau tanda kutip jika ingin memberi penekanan.";

$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;
$data = [ "contents" => [[ "parts" => [[ "text" => $prompt ]] ]] ];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// Penting untuk localhost XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Koneksi cURL gagal: ' . curl_error($ch)]);
} else {
    $result = json_decode($response, true);
    if (isset($result['error'])) {
        echo json_encode(['error' => 'Google Error: ' . $result['error']['message']]);
    } else if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo json_encode(['success' => true, 'message' => $result['candidates'][0]['content']['parts'][0]['text']]);
    } else {
        echo json_encode(['error' => 'Gagal generate pesan', 'debug' => $result]);
    }
}
curl_close($ch);
?>