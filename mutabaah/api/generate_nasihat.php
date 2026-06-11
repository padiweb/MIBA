<?php
require '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'santri') {
    http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit;
}

$uid = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$kemarin = date('Y-m-d', strtotime('-1 day'));

$q_tugas = mysqli_query($conn, "SELECT id, nama_kegiatan, kategori FROM kegiatan");
$semua_tugas = [];
while($r = mysqli_fetch_assoc($q_tugas)) { $semua_tugas[$r['id']] = $r; }

$q_logs = mysqli_query($conn, "SELECT kegiatan_id FROM logs WHERE user_id='$uid' AND tanggal='$kemarin'");
$dikerjakan_ids = [];
while($r = mysqli_fetch_assoc($q_logs)) { $dikerjakan_ids[] = $r['kegiatan_id']; }

$list_selesai = [];
$list_terlewat = [];

foreach($semua_tugas as $id => $task) {
    // Mempercantik tampilan kategori di prompt (hilangkan underscore)
    $kategori_nice = ucwords(str_replace('_', ' ', $task['kategori']));
    $detail = $task['nama_kegiatan'] . " (" . $kategori_nice . ")";
    
    if(in_array($id, $dikerjakan_ids)) $list_selesai[] = $detail;
    else $list_terlewat[] = $detail;
}

$str_selesai = empty($list_selesai) ? "Tidak ada amalan." : implode(", ", $list_selesai);
$str_terlewat = empty($list_terlewat) ? "Alhamdulillah lengkap." : implode(", ", $list_terlewat);

$prompt = "Kamu Ustadz pembimbing. Beri nasihat singkat untuk santri **$nama** saat login. Evaluasi kemarin ($kemarin): ✅: $str_selesai. ❌: $str_terlewat. Ingatkan keikhlasan.";

$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;
$data = [ "contents" => [[ "parts" => [[ "text" => $prompt ]] ]] ];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) echo json_encode(['error' => 'Koneksi gagal']);
else {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo json_encode(['success' => true, 'message' => $result['candidates'][0]['content']['parts'][0]['text']]);
    } else {
        echo json_encode(['error' => 'Gagal menyusun nasihat']);
    }
}
curl_close($ch);
?>