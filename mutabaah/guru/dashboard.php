<?php
require '../config.php';
cekLogin(['guru']);

// --- 1. CONFIG WAKTU & TARGET ---
$set = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));
$start_date_obj = new DateTime($set['tgl_mulai']);
$end_date_obj = new DateTime($set['tgl_selesai']);
$today = new DateTime();

// Konversi tanggal batas ke format string yang aman untuk SQL
$start_date_sql = $set['tgl_mulai'];
$end_date_sql = $set['tgl_selesai'];

$interval_total = $start_date_obj->diff($end_date_obj);
$total_hari = $interval_total->days + 1;

if ($today < $start_date_obj) { $hari_berjalan = 0; } 
elseif ($today > $end_date_obj) { $hari_berjalan = $total_hari; } 
else {
    $interval_berjalan = $start_date_obj->diff($today);
    $hari_berjalan = $interval_berjalan->days + 1;
}
$sisa_hari = max(0, $total_hari - $hari_berjalan);
$kelas_id = $_SESSION['kelas_id'];

// Ambil Nama Kelas Guru (Untuk Header Laporan)
$nama_kelas_guru = "-";
if (!empty($kelas_id)) {
    $q_k = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id='$kelas_id'");
    if($r_k = mysqli_fetch_assoc($q_k)) $nama_kelas_guru = $r_k['nama_kelas'];
}

// Target Poin Harian (Dinamis dari DB)
$q_poin = mysqli_query($conn, "SELECT kategori, SUM(poin) as total FROM kegiatan GROUP BY kategori");
$max_poin = ['ibadah_wajib' => 0, 'ibadah_sunnah' => 0, 'kegiatan_sosial' => 0];
while($r = mysqli_fetch_assoc($q_poin)) { $max_poin[$r['kategori']] = $r['total']; }

$target_wajib = $max_poin['ibadah_wajib'] * $hari_berjalan;
$target_sunnah = $max_poin['ibadah_sunnah'] * $hari_berjalan;
$target_sosial = $max_poin['kegiatan_sosial'] * $hari_berjalan;

// --- 2. CONFIG PAGINATION ---
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung Total Santri
$total_santri = 0;
if (!empty($kelas_id)) {
    $q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='santri' AND kelas_id='$kelas_id'");
    $total_santri = mysqli_fetch_assoc($q_count)['total'];
}
$total_pages = ceil($total_santri / $limit);
$stats['total_santri'] = $total_santri;

// --- 3. QUERY UTAMA (OPTIMIZED & FILTER WAKTU) ---
$data_santri = [];
if (!empty($kelas_id)) {
    $sql = "SELECT 
                u.id, u.nama_lengkap, u.jenis_kelamin, u.no_hp_ortu, kls.nama_kelas,
                
                COALESCE(SUM(CASE 
                    WHEN k.kategori = 'ibadah_wajib' THEN 
                        CASE WHEN u.jenis_kelamin = 'L' AND l.is_jamaah = 0 THEN k.poin * 0.5 ELSE k.poin END
                    ELSE 0 
                END), 0) as poin_wajib,
                
                COALESCE(SUM(CASE WHEN k.kategori = 'ibadah_sunnah' THEN k.poin ELSE 0 END), 0) as poin_sunnah,
                COALESCE(SUM(CASE WHEN k.kategori = 'kegiatan_sosial' THEN k.poin ELSE 0 END), 0) as poin_sosial,
                
                COUNT(l.id) as total_logs,
                COUNT(CASE WHEN DATE(l.created_at) = l.tanggal THEN 1 END) as ontime_logs
                
            FROM users u
            LEFT JOIN kelas kls ON u.kelas_id = kls.id
            LEFT JOIN logs l ON u.id = l.user_id 
                -- FILTER WAKTU UTAMA: Hanya log dalam rentang liburan
                AND l.tanggal BETWEEN '$start_date_sql' AND '$end_date_sql'
            LEFT JOIN kegiatan k ON l.kegiatan_id = k.id
            WHERE u.role = 'santri' AND u.kelas_id = '$kelas_id'
            GROUP BY u.id
            ORDER BY u.nama_lengkap ASC
            LIMIT $offset, $limit";

    $q_main = mysqli_query($conn, $sql);
    if($q_main) {
        while($row = mysqli_fetch_assoc($q_main)) {
            $data_santri[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTQ | Mutaba'ah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .loading-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .modal-animate { animation: slideUp 0.3s ease-out forwards; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* --- CSS KHUSUS CETAK (PRINT) --- */
        @media print {
            /* Sembunyikan elemen non-cetak */
            nav, .btn-no-print, .pagination-control, .no-print-area { 
                display: none !important; 
            }
            
            /* Reset Layout untuk kertas */
            body { background-color: white; font-size: 11pt; color: black; }
            main { padding: 0; margin: 0; max-width: 100%; box-shadow: none; }
            
            /* Tampilkan Header Laporan */
            #printHeader { display: block !important; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            
            /* Atur Tabel agar rapi saat dicetak */
            .overflow-x-auto { overflow: visible !important; }
            table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10pt; }
            th, td { border: 1px solid #000 !important; padding: 6px !important; text-align: left; }
            th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; text-align: center; }
            td.text-center { text-align: center; }
            
            /* Sembunyikan kolom Aksi saat print */
            th:last-child, td:last-child { display: none; }
            
            /* Ubah warna badge menjadi teks biasa agar hemat tinta */
            .bg-indigo-100, .bg-emerald-100, .bg-amber-100, .bg-red-100, .bg-blue-100 { 
                background-color: transparent !important; 
                color: #000 !important; 
                border: none !important;
                font-weight: normal; 
                padding: 0;
            }
            
            /* Page Break */
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="text-slate-800">

    <!-- Navbar (Akan hilang saat print) -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm no-print-area">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-md">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg text-slate-800 leading-tight">Mutaba'ah Guru</h1>
                        <p class="text-xs text-slate-500 font-medium">Panel Monitoring Santri</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Tombol Cetak Laporan -->
                    <button onclick="window.print()" class="hidden md:flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors border border-slate-300 btn-no-print">
                        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
                    </button>

                    <a href="kirim_pesan.php" class="hidden md:flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors btn-no-print">
                        <i data-lucide="send" class="w-4 h-4"></i> Kirim Pesan
                    </a>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($_SESSION['nama']) ?></p>
                        <p class="text-xs text-slate-500">Wali Kelas</p>
                    </div>
                    <a href="../logout.php" class="p-2 text-slate-400 hover:text-red-600 transition-colors bg-slate-50 rounded-full hover:bg-red-50" title="Keluar">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- HEADER KHUSUS PRINT (Hanya muncul saat print) -->
        <div id="printHeader" class="hidden mb-6">
            <div class="text-center border-b-2 border-black pb-4 mb-4">
                <h1 class="text-2xl font-bold uppercase tracking-wide">Laporan Aktivitas Mutaba'ah Santri</h1>
                <p class="text-sm">Periode Liburan: <?= date('d F Y', strtotime($set['tgl_mulai'])) ?> s.d <?= date('d F Y', strtotime($set['tgl_selesai'])) ?></p>
            </div>
            
            <div class="grid grid-cols-2 gap-8 mb-6 text-sm">
                <div>
                    <table class="w-full">
                        <tr><td class="py-1 font-bold w-32 border-none">Nama Guru</td><td class="border-none">: <?= htmlspecialchars($_SESSION['nama']) ?></td></tr>
                        <tr><td class="py-1 font-bold border-none">Kelas</td><td class="border-none">: <?= htmlspecialchars($nama_kelas_guru) ?></td></tr>
                        <tr><td class="py-1 font-bold border-none">Total Santri</td><td class="border-none">: <?= $total_santri ?></td></tr>
                    </table>
                </div>
                <div class="text-right">
                    <table class="w-full">
                        <tr><td class="py-1 font-bold w-32 border-none">Durasi Libur</td><td class="border-none text-right">: <?= $total_hari ?> Hari</td></tr>
                        <tr><td class="py-1 font-bold border-none">Hari Berjalan</td><td class="border-none text-right">: <?= $hari_berjalan ?> Hari</td></tr>
                        <tr><td class="py-1 font-bold border-none">Sisa Waktu</td><td class="border-none text-right">: <?= $sisa_hari ?> Hari</td></tr>
                    </table>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-2 italic">Dicetak pada: <?= date('d F Y, H:i') ?></div>
        </div>

        <!-- Info Banner (No Print) -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden no-print-area">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-1">Assalamu'alaikum, Ustadz/Ustadzah!</h2>
                <p class="text-blue-100 text-sm">Hari ini adalah hari ke-<strong><?= $hari_berjalan ?></strong> dari total <strong><?= $total_hari ?></strong> hari liburan.</p>
            </div>
            <div class="relative z-10 flex items-center gap-4 bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20">
                <div class="text-right">
                    <p class="text-xs text-blue-200 uppercase font-semibold">Target Wajib Saat Ini</p>
                    <p class="text-2xl font-bold"><?= $target_wajib ?> <span class="text-sm font-normal">Poin</span></p>
                </div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <i data-lucide="target" class="w-8 h-8"></i>
                </div>
            </div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Statistik Cards (No Print) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print-area">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Santri</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?= $total_santri ?></p>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-600">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Durasi Liburan</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?= $total_hari ?> <span class="text-sm font-normal text-slate-400">Hari</span></p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Sisa Waktu</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?= $sisa_hari ?> <span class="text-sm font-normal text-slate-400">Hari Lagi</span></p>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Tabel Monitoring -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 no-print-area">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Laporan Aktivitas Santri</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        <?= !empty($kelas_id) ? "Menampilkan halaman $page dari $total_pages" : "Pantau perkembangan ibadah santri." ?>
                    </p>
                </div>
                <?php if(empty($kelas_id)): ?>
                    <span class="text-red-500 text-sm font-medium bg-red-50 px-3 py-1 rounded-full border border-red-100">Belum ada kelas</span>
                <?php else: ?>
                    <span class="text-indigo-600 text-sm font-medium bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 btn-no-print">Kelas Anda</span>
                <?php endif; ?>
            </div>

            <?php if(!empty($kelas_id)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-semibold tracking-wider">Nama Santri</th>
                            <th class="px-6 py-4 font-semibold text-center border-l border-slate-100">Wajib <br><span class="text-[10px] text-slate-400 normal-case font-normal no-print-area">(Target: 100%)</span></th>
                            <th class="px-6 py-4 font-semibold text-center border-l border-slate-100">Sunnah <br><span class="text-[10px] text-slate-400 normal-case font-normal no-print-area">(Target: 75%)</span></th>
                            <th class="px-6 py-4 font-semibold text-center border-l border-slate-100">Sosial <br><span class="text-[10px] text-slate-400 normal-case font-normal no-print-area">(Target: 75%)</span></th>
                            <th class="px-6 py-4 font-semibold text-center border-l border-slate-100 text-indigo-600">Disiplin <br><span class="text-[10px] text-slate-400 normal-case font-normal btn-no-print"></span></th>
                            <th class="px-6 py-4 font-semibold text-center border-l border-slate-100">Status</th>
                            <th class="px-6 py-4 font-semibold text-right btn-no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($data_santri as $s): 
                            $wajib = $s['poin_wajib'];
                            $sunnah = $s['poin_sunnah'];
                            $sosial = $s['poin_sosial'];
                            $disiplin = ($s['total_logs'] > 0) ? round(($s['ontime_logs'] / $s['total_logs']) * 100) : 0;
                            
                            $is_aman = ($wajib >= $target_wajib);
                            $cls_wajib = $is_aman ? 'text-emerald-600 bg-emerald-50 font-bold' : 'text-red-600 bg-red-50 font-bold';
                            
                            $p_sunnah = ($target_sunnah > 0) ? ($sunnah / $target_sunnah)*100 : 0;
                            $cls_sunnah = ($p_sunnah>=75)?'text-emerald-600':(($p_sunnah>=50)?'text-amber-600':'text-red-600');
                            
                            $p_sosial = ($target_sosial > 0) ? ($sosial / $target_sosial)*100 : 0;
                            $cls_sosial = ($p_sosial>=75)?'text-emerald-600':(($p_sosial>=50)?'text-amber-600':'text-red-600');
                            
                            $cls_disiplin = ($disiplin >= 90) ? 'text-emerald-600 bg-emerald-50' : (($disiplin >= 70) ? 'text-blue-600 bg-blue-50' : 'text-amber-600 bg-amber-50');

                            $status_txt = $is_aman ? 'Aman' : 'Kurang';
                            $status_badge = $is_aman ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200 animate-pulse';
                            $status_icon = $is_aman ? 'check-circle' : 'alert-triangle';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm group-hover:border-indigo-200 btn-no-print">
                                        <?= substr($s['nama_lengkap'], 0, 2) ?>
                                    </div>
                                    <div>
                                        <?= $s['nama_lengkap'] ?>
                                        <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="<?= $s['jenis_kelamin']=='L'?'user':'user-circle' ?>" class="w-3 h-3 btn-no-print"></i>
                                            <?= $s['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center border-l border-slate-100">
                                <span class="px-2.5 py-1 rounded-md text-xs <?= $cls_wajib ?> border border-transparent shadow-sm">
                                    <?= $wajib ?> <span class="text-[10px] font-normal opacity-70 btn-no-print">/ <?= $target_wajib ?></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center border-l border-slate-100 text-sm font-medium <?= $cls_sunnah ?>">
                                <?= round($p_sunnah) ?>%
                            </td>
                            <td class="px-6 py-4 text-center border-l border-slate-100 text-sm font-medium <?= $cls_sosial ?>">
                                <?= round($p_sosial) ?>%
                            </td>
                            <td class="px-6 py-4 text-center border-l border-slate-100">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold border border-transparent <?= $cls_disiplin ?>">
                                    <?= $disiplin ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center border-l border-slate-100">
                                <span class="<?= $status_badge ?> px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1.5 border">
                                    <i data-lucide="<?= $status_icon ?>" class="w-3 h-3 btn-no-print"></i> <?= $status_txt ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right btn-no-print">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="detail_santri.php?id=<?= $s['id'] ?>" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <button onclick="siapkanPesan(
                                        '<?= $s['nama_lengkap'] ?>',
                                        '<?= $s['nama_kelas'] ?>',
                                        '<?= $_SESSION['nama'] ?>',
                                        <?= $wajib ?>,
                                        <?= $sunnah ?>,
                                        <?= $sosial ?>,
                                        <?= $disiplin ?>,
                                        <?= $total_hari ?>,
                                        '<?= $status_txt ?>',
                                        '<?= $s['no_hp_ortu'] ?>',
                                        this
                                    )" class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all relative" title="Buat Pesan WA">
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; 
                        if (empty($data_santri)) echo "<tr><td colspan='7' class='px-6 py-12 text-center text-slate-400'><div class='flex flex-col items-center gap-2'><i data-lucide='inbox' class='w-12 h-12 stroke-1 opacity-50'></i><p>Belum ada data santri di kelas ini.</p></div></td></tr>"; 
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls (No Print) -->
            <?php if($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-center gap-2 bg-gray-50/30 no-print-area pagination-control">
                <?php if($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>" class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-white hover:text-indigo-600 transition-colors">Sebelumnya</a>
                <?php endif; ?>
                
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 text-sm border rounded <?= ($i==$page) ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-200 hover:bg-white hover:text-indigo-600' ?> transition-colors"><?= $i ?></a>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>" class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-white hover:text-indigo-600 transition-colors">Selanjutnya</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal AI Review (No Print) -->
    <div id="modalReview" class="hidden fixed inset-0 z-50 overflow-y-auto btn-no-print" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-animate">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-yellow-300"></i> AI Message Generator
                    </h3>
                    <button onclick="closeModal()" class="text-indigo-100 hover:text-white transition-colors bg-white/10 p-1 rounded-lg hover:bg-white/20">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <p class="text-sm text-slate-500 mb-2 font-medium">Review pesan sebelum dikirim:</p>
                    <textarea id="areaPesan" class="w-full h-48 p-4 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none leading-relaxed shadow-inner font-sans"></textarea>
                    <input type="hidden" id="nomorTujuan">
                </div>

                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                    <button type="button" onclick="kirimWA()" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 focus:outline-none transition-all hover:scale-[1.02] active:scale-95">
                        <i data-lucide="send" class="w-4 h-4"></i> Kirim WhatsApp
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full sm:w-auto inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none sm:mt-0 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        async function siapkanPesan(nama, kelas, guru, wajib, sunnah, sosial, disiplin, total_hari, status_text, no_hp, btn) {
            const originalIcon = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin text-emerald-600"></i>`;
            lucide.createIcons();

            try {
                let res = await fetch('../api/generate_pesan.php', { method: 'POST', body: JSON.stringify({nama, kelas, guru, wajib, sunnah, sosial, disiplin, total_hari, status_text}) });
                let data = await res.json();
                if(data.success) {
                    document.getElementById('areaPesan').value = data.message;
                    document.getElementById('nomorTujuan').value = no_hp;
                    const modal = document.getElementById('modalReview');
                    modal.classList.remove('hidden');
                } else { alert('Gagal: ' + (data.error || 'Unknown error')); }
            } catch(e) { 
                console.error(e);
                alert('Terjadi kesalahan koneksi.'); 
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalIcon;
                lucide.createIcons();
            }
        }

        function kirimWA() {
            let msg = document.getElementById('areaPesan').value;
            let no = document.getElementById('nomorTujuan').value;
            if(!no) { alert('Nomor HP tidak valid!'); return; }
            window.open(`https://wa.me/${no}?text=${encodeURIComponent(msg)}`, '_blank');
            closeModal();
        }

        function closeModal() {
            document.getElementById('modalReview').classList.add('hidden');
        }
    </script>
</body>
</html>