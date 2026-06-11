<?php
require '../config.php';
cekLogin(['guru']);

$id = (int)$_GET['id'];
$kelas_guru = $_SESSION['kelas_id'];

// 1. AMBIL DATA SANTRI & PROTEKSI
// Pastikan santri yang dilihat adalah milik kelas guru tersebut
$santri = mysqli_fetch_assoc(mysqli_query($conn, "SELECT users.*, kelas.nama_kelas FROM users LEFT JOIN kelas ON users.kelas_id = kelas.id WHERE users.id=$id AND users.kelas_id='$kelas_guru'"));

if (!$santri) {
    echo "<div class='min-h-screen flex items-center justify-center bg-slate-50'><div class='text-center'><h1 class='text-2xl text-red-500 font-bold mb-2'>Akses Ditolak</h1><p class='text-slate-500'>Data tidak ditemukan atau bukan hak akses Anda.</p><a href='dashboard.php' class='mt-4 inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition'>Kembali ke Dashboard</a></div></div>";
    exit;
}

// 2. CONFIG WAKTU & TARGET
$set = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));
$tgl_mulai_libur = $set['tgl_mulai'];
$tgl_selesai_libur = $set['tgl_selesai'];

// Target Wajib (Jumlah item kegiatan wajib di database)
$q_wajib = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM kegiatan WHERE kategori='ibadah_wajib'"))['t'];

// 3. AMBIL MASTER KEGIATAN (Untuk membandingkan mana yg dikerjakan vs belum)
$master_kegiatan = [];
$q_master = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY kategori ASC, id ASC");
while($k = mysqli_fetch_assoc($q_master)) {
    $master_kegiatan[] = $k;
}

// 4. AMBIL LOGS SANTRI & MAPPING
// Ambil juga kolom is_jamaah
$logs_query = mysqli_query($conn, "SELECT * FROM logs WHERE user_id='$id' ORDER BY tanggal ASC");
$logs_map = []; // Format: [tanggal][kegiatan_id] = data_log
while($l = mysqli_fetch_assoc($logs_query)) {
    $logs_map[$l['tanggal']][$l['kegiatan_id']] = $l;
}

// 5. LOGIKA PERIODE BULAN (Multi-Month View)
$start    = new DateTime($tgl_mulai_libur);
$start->modify('first day of this month'); // Mulai dari awal bulan start
$end      = new DateTime($tgl_selesai_libur);
$end->modify('first day of next month'); // Sampai akhir bulan end

$interval = DateInterval::createFromDateString('1 month');
$period   = new DatePeriod($start, $interval, $end);
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        /* Hover effect pada tanggal */
        .day-cell { transition: all 0.2s ease; }
        .day-cell:hover:not(.disabled) { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 10; border-color: #6366f1; }
        
        /* Animasi Modal */
        .modal-animate { animation: modalUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes modalUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        
        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div>
                        <h1 class="font-bold text-lg text-slate-800 leading-tight">Rincian Amalan</h1>
                        <p class="text-xs text-slate-500 font-medium">Rekapitulasi Harian Santri</p>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Profil Santri -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden group">
            <!-- Dekorasi Background -->
            <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-50 rounded-bl-full -mr-10 -mt-10 z-0 transition-transform group-hover:scale-110 duration-500"></div>
            
            <div class="flex items-center gap-5 z-10">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-2xl font-bold border-4 border-white shadow-lg">
                        <?= substr($santri['nama_lengkap'], 0, 1) ?>
                    </div>
                    <!-- Badge Gender -->
                    <div class="absolute -bottom-1 -right-1 bg-white p-1.5 rounded-full shadow-sm border border-slate-100">
                        <?php if($santri['jenis_kelamin']=='L'): ?>
                            <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                        <?php else: ?>
                            <i data-lucide="user-circle" class="w-4 h-4 text-pink-600"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?= $santri['nama_lengkap'] ?></h2>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500 mt-1.5">
                        <span class="inline-flex items-center gap-1.5 bg-slate-100 px-3 py-1 rounded-full border border-slate-200 text-xs font-semibold text-slate-600">
                            <?= $santri['jenis_kelamin']=='L'?'Santri Putra':'Santri Putri' ?>
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="font-medium text-slate-600">Kelas <?= $santri['nama_kelas'] ?></span>
                    </div>
                </div>
            </div>
            
            <div class="z-10 bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-amber-900 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="bg-amber-100 p-2.5 rounded-full text-amber-600">
                    <i data-lucide="calendar-range" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] text-amber-600 font-bold uppercase tracking-wider">Periode Liburan</p>
                    <p class="font-bold text-sm text-amber-900 mt-0.5">
                        <?= date('d M', strtotime($tgl_mulai_libur)) ?> — <?= date('d M Y', strtotime($tgl_selesai_libur)) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- LOOPING BULAN (Multi-Month Calendar) -->
        <?php foreach ($period as $dt): 
            $year = $dt->format('Y');
            $month = $dt->format('m');
            $month_name = $dt->format('F Y'); // Format: December 2025
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $first_day = date('w', strtotime("$year-$month-01")); // 0 (Minggu) - 6 (Sabtu)
        ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <!-- Header Kalender -->
            <div class="bg-slate-50/80 backdrop-blur px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h3 class="font-bold text-lg text-slate-700 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-indigo-500"></i> <?= $month_name ?>
                </h3>
                <!-- Legend (Hanya muncul di bulan pertama agar tidak repetitive) -->
                <?php if ($dt == $start): ?>
                <div class="flex flex-wrap justify-center gap-3 text-xs font-medium bg-white px-4 py-2 rounded-full border border-slate-200 shadow-sm">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Lengkap</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Cukup</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Kurang</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300"></span> 0 Data</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Nama Hari -->
            <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-[10px] sm:text-xs font-bold text-slate-400 text-center py-3 tracking-widest uppercase">
                <div class="text-red-400">Ahad</div><div>Senin</div><div>Selasa</div><div>Rabu</div><div>Kamis</div><div class="text-emerald-600">Jumat</div><div>Sabtu</div>
            </div>

            <!-- Grid Tanggal -->
            <div class="grid grid-cols-7 bg-slate-200 gap-px border-b border-slate-200">
                
                <!-- Spacer Awal Bulan -->
                <?php for($i=0; $i < $first_day; $i++): ?>
                    <div class="bg-slate-50/50 min-h-[80px] sm:min-h-[100px]"></div>
                <?php endfor; ?>

                <!-- Loop Tanggal -->
                <?php for($day=1; $day <= $days_in_month; $day++): 
                    $current_date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                    $is_holiday_period = ($current_date >= $tgl_mulai_libur && $current_date <= $tgl_selesai_libur);
                    $is_today = ($current_date == date('Y-m-d'));
                    
                    // Hitung Statistik Harian untuk Indikator Warna
                    $daily_log_count = 0;
                    $daily_wajib_done = 0;
                    $daily_sunnah_done = 0;
                    $daily_sosial_done = 0;

                    // Ambil data logs hari ini jika ada
                    $todays_logs = $logs_map[$current_date] ?? [];
                    
                    // GENERATE DATA UNTUK POPUP
                    $popup_data = [];
                    
                    foreach($master_kegiatan as $mk) {
                        $keg_id = $mk['id'];
                        $is_done = isset($todays_logs[$keg_id]);
                        
                        // Hitung Statistik
                        if ($is_done) {
                            $daily_log_count++;
                            if($mk['kategori'] == 'ibadah_wajib') $daily_wajib_done++;
                            elseif($mk['kategori'] == 'ibadah_sunnah') $daily_sunnah_done++;
                            elseif($mk['kategori'] == 'kegiatan_sosial') $daily_sosial_done++;
                        }

                        // Logic Tampilan Poin & Label
                        $poin_display = $mk['poin'];
                        $label_extra = '';
                        $status_class = $is_done ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-50 border-slate-100 opacity-60 grayscale';
                        
                        if ($is_done) {
                            $log_data = $todays_logs[$keg_id];
                            
                            // Cek Jamaah/Munfarid (Khusus Wajib & Laki-laki)
                            if ($mk['kategori'] == 'ibadah_wajib' && $santri['jenis_kelamin'] == 'L') {
                                if ($log_data['is_jamaah'] == 1) {
                                    $label_extra = 'Jamaah';
                                    $status_class = 'bg-indigo-50 border-indigo-200 shadow-sm ring-1 ring-indigo-100'; // Highlight Jamaah
                                } else {
                                    $label_extra = 'Munfarid';
                                    $poin_display = $mk['poin'] * 0.5; // Poin Setengah
                                    $status_class = 'bg-amber-50 border-amber-200 shadow-sm'; // Highlight Munfarid
                                }
                            }
                        }

                        $popup_data[] = [
                            'nama' => $mk['nama_kegiatan'],
                            'kategori' => $mk['kategori'],
                            'poin' => $poin_display,
                            'status' => $is_done ? 'done' : 'missed',
                            'label' => $label_extra,
                            'class' => $status_class
                        ];
                    }

                    // Tentukan Warna Cell Kalender
                    $bg_color = $is_holiday_period ? "bg-white cursor-pointer" : "bg-slate-50 text-slate-400 cursor-not-allowed disabled";
                    $border_color = $is_today ? "ring-2 ring-inset ring-indigo-600 z-20 today-marker shadow-lg" : "hover:ring-2 hover:ring-inset hover:ring-indigo-300";
                    $status_dot = "";
                    $onclick = "";

                    if ($is_holiday_period) {
                        $json_data = htmlspecialchars(json_encode($popup_data), ENT_QUOTES, 'UTF-8');
                        $onclick = "showDetails('$current_date', '$json_data')";
                        
                        if ($daily_log_count > 0) {
                            // Logika Warna Indikator
                            if ($daily_wajib_done < $q_wajib) {
                                $status_dot = "bg-red-500"; // Wajib Bolong
                            } else if ($daily_sunnah_done > 0 || $daily_sosial_done > 0) {
                                $status_dot = "bg-emerald-500"; // Sempurna (Wajib Full + Ada Sunnah)
                            } else {
                                $status_dot = "bg-amber-400"; // Standar (Wajib Full saja)
                            }
                        } else if ($current_date < date('Y-m-d')) {
                            $status_dot = "bg-slate-300"; // Lewat & Kosong
                        }
                    }
                ?>
                    <div class="<?= $bg_color ?> <?= $border_color ?> min-h-[80px] sm:min-h-[100px] p-2 relative flex flex-col justify-between day-cell group transition-all duration-200" 
                         onclick="<?= $onclick ?>">
                        
                        <div class="flex justify-between items-start">
                            <span class="font-bold text-sm <?= $is_today ? 'text-indigo-700' : '' ?>"><?= $day ?></span>
                            <?php if($status_dot): ?>
                                <span class="w-2.5 h-2.5 rounded-full <?= $status_dot ?> shadow-sm ring-2 ring-white transform group-hover:scale-125 transition-transform"></span>
                            <?php endif; ?>
                        </div>

                        <?php if($daily_log_count > 0): ?>
                            <div class="mt-2">
                                <span class="inline-flex items-center text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-bold border border-indigo-100 truncate max-w-full">
                                    <?= $daily_log_count ?> Keg
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
                
                <!-- Spacer Akhir Bulan -->
                <?php 
                $last_day_of_week = date('w', strtotime("$year-$month-$days_in_month"));
                for($i=$last_day_of_week; $i < 6; $i++): ?>
                    <div class="bg-slate-50/50"></div>
                <?php endfor; ?>

            </div>
        </div>
        <?php endforeach; // End Loop Bulan ?>

    </main>

    <!-- Modal Detail -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closeModal()"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 z-10 overflow-y-auto pointer-events-none">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg modal-animate pointer-events-auto flex flex-col max-h-[85vh]">
                    
                    <!-- Header -->
                    <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center shrink-0 sticky top-0 z-10">
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Rincian Kegiatan</p>
                            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2 mt-0.5">
                                <i data-lucide="calendar-check" class="w-5 h-5 text-indigo-600"></i>
                                <span id="modalDateTitle">...</span>
                            </h3>
                        </div>
                        <button class="bg-slate-100 p-2 rounded-full text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors focus:outline-none" onclick="closeModal()">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <!-- Body (Scrollable) -->
                    <div class="p-6 bg-slate-50 overflow-y-auto custom-scroll" id="modalList">
                        <!-- Content injected via JS -->
                    </div>
                    
                    <!-- Footer -->
                    <div class="bg-white px-6 py-4 border-t border-slate-100 flex justify-end shrink-0">
                        <button class="bg-indigo-600 text-white px-5 py-2 text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg active:scale-95" onclick="closeModal()">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // Auto-scroll ke hari ini
        document.addEventListener("DOMContentLoaded", function() {
            const todayEl = document.querySelector('.today-marker');
            if(todayEl) {
                todayEl.scrollIntoView({behavior: "smooth", block: "center"});
            }
        });

        function showDetails(dateStr, dataJson) {
            const data = JSON.parse(dataJson);
            const dateObj = new Date(dateStr);
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('modalDateTitle').innerText = dateObj.toLocaleDateString('id-ID', options);
            
            const list = document.getElementById('modalList');
            list.innerHTML = '';
            
            // Group data by category
            const categories = {
                'ibadah_wajib': { title: 'Ibadah Wajib', icon: 'star', color: 'text-red-600', bgIcon: 'bg-red-100 text-red-600', items: [] },
                'ibadah_sunnah': { title: 'Ibadah Sunnah', icon: 'sparkles', color: 'text-amber-600', bgIcon: 'bg-amber-100 text-amber-600', items: [] },
                'kegiatan_sosial': { title: 'Kegiatan Sosial', icon: 'heart', color: 'text-blue-600', bgIcon: 'bg-blue-100 text-blue-600', items: [] }
            };

            data.forEach(item => {
                if(categories[item.kategori]) {
                    categories[item.kategori].items.push(item);
                }
            });

            // Render HTML
            let hasContent = false;
            for (const [key, cat] of Object.entries(categories)) {
                if (cat.items.length > 0) {
                    hasContent = true;
                    let catHtml = `
                        <div class="mb-6 last:mb-0">
                            <h4 class="text-xs font-bold ${cat.color} mb-3 flex items-center gap-2 uppercase tracking-wide">
                                <span class="p-1 rounded-md bg-white border border-slate-100 shadow-sm"><i data-lucide="${cat.icon}" class="w-3 h-3"></i></span> 
                                ${cat.title}
                            </h4>
                            <div class="space-y-2">
                    `;
                    
                    cat.items.forEach(item => {
                        const isDone = item.status === 'done';
                        const icon = isDone ? 'check-circle-2' : 'circle';
                        // Tampilan item: jika done -> putih & jelas, jika belum -> abu-abu & redup
                        
                        // Label Jamaah/Munfarid
                        let labelHtml = '';
                        if (item.label) {
                            let labelColor = item.label === 'Jamaah' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-amber-100 text-amber-700 border-amber-200';
                            labelHtml = `<span class="text-[10px] font-bold px-1.5 py-0.5 rounded border ${labelColor} ml-2 uppercase tracking-wider shadow-sm">${item.label}</span>`;
                        }

                        let scoreHtml = '';
                        if (isDone) {
                             scoreHtml = `<span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100 shadow-sm">+${item.poin}</span>`;
                        } else {
                             scoreHtml = `<span class="text-xs font-bold text-slate-300 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">-</span>`;
                        }

                        catHtml += `
                            <div class="flex items-center justify-between p-3 rounded-xl border ${item.class} transition-all hover:scale-[1.01]">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="${icon}" class="w-5 h-5 ${isDone ? 'text-emerald-500' : 'text-slate-300'}"></i>
                                    <span class="text-sm font-medium ${isDone ? 'text-slate-700' : 'text-slate-400'} flex items-center">
                                        ${item.nama} ${labelHtml}
                                    </span>
                                </div>
                                ${scoreHtml}
                            </div>
                        `;
                    });
                    
                    catHtml += `</div></div>`;
                    list.innerHTML += catHtml;
                }
            }
            
            if (!hasContent) {
                list.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <div class="bg-slate-100 p-4 rounded-full mb-3">
                            <i data-lucide="list-x" class="w-8 h-8 opacity-50"></i>
                        </div>
                        <p class="text-sm font-medium">Tidak ada data kegiatan.</p>
                    </div>
                `;
            }

            document.getElementById('detailModal').classList.remove('hidden');
            lucide.createIcons(); // Refresh icons
        }

        function closeModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }
    </script>
</body>
</html>