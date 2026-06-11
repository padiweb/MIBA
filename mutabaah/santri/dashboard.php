<?php
require '../config.php';
cekLogin(['santri']);
$uid = $_SESSION['user_id'];

// Ambil Info Gender (Penting untuk fitur Jamaah & Haidh)
$u_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jenis_kelamin, nama_lengkap FROM users WHERE id='$uid'"));
$is_male = ($u_info['jenis_kelamin'] == 'L');
$is_female = ($u_info['jenis_kelamin'] == 'P');

// --- 1. PENGATURAN TANGGAL ---
$set = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));
$min_date = $set['tgl_mulai'];
$max_date = $set['tgl_selesai'];
$today_str = date('Y-m-d');

$tgl = isset($_GET['tgl']) ? $_GET['tgl'] : $today_str;
if ($tgl < $min_date) $tgl = $min_date;
if ($tgl > $max_date) $tgl = $max_date;
if ($tgl > $today_str) $tgl = $today_str;

// --- 2. LOGIKA SIMPAN ---
if (isset($_POST['save'])) {
    $tgl_laporan = $_POST['tgl_laporan'];
    $sedang_haidh = isset($_POST['status_haidh']) ? 1 : 0; // Cek input toggle haidh

    if ($tgl_laporan >= $min_date && $tgl_laporan <= $max_date && $tgl_laporan <= $today_str) {
        // Reset logs hari itu
        mysqli_query($conn, "DELETE FROM logs WHERE user_id='$uid' AND tanggal='$tgl_laporan'");
        
        // A. Simpan Kegiatan Manual (Input User)
        if(!empty($_POST['keg'])) {
            foreach($_POST['keg'] as $kid) {
                $val_jamaah = 0;
                if (isset($_POST['is_jamaah']) && isset($_POST['is_jamaah'][$kid])) {
                    $val_jamaah = 1;
                }
                mysqli_query($conn, "INSERT INTO logs (user_id, kegiatan_id, tanggal, is_jamaah) VALUES ('$uid', '$kid', '$tgl_laporan', '$val_jamaah')");
            }
        }

        // B. Auto-Insert Udzur Haidh (Khusus Perempuan)
        // Jika sedang haidh, kegiatan yang 'ada_udzur=1' otomatis dianggap terlaksana
        if ($is_female && $sedang_haidh) {
            $q_udzur = mysqli_query($conn, "SELECT id FROM kegiatan WHERE ada_udzur=1");
            while($u = mysqli_fetch_assoc($q_udzur)) {
                // Cek agar tidak double
                $cek = mysqli_query($conn, "SELECT id FROM logs WHERE user_id='$uid' AND kegiatan_id='".$u['id']."' AND tanggal='$tgl_laporan'");
                if(mysqli_num_rows($cek) == 0) {
                    // Insert sebagai 'done' tanpa jamaah (karena udzur)
                    mysqli_query($conn, "INSERT INTO logs (user_id, kegiatan_id, tanggal, is_jamaah) VALUES ('$uid', '".$u['id']."', '$tgl_laporan', 0)");
                }
            }
        }
        
        // Redirect
        echo "<script>
            alert('Alhamdulillah! Laporan berhasil disimpan.');
            window.location.href = 'dashboard.php?tgl=$tgl_laporan&haidh=$sedang_haidh';
        </script>";
        exit;
    }
}

// --- 3. AMBIL DATA ---
$is_haidh_active = isset($_GET['haidh']) ? $_GET['haidh'] : 0;

$done_q = mysqli_query($conn, "SELECT kegiatan_id, is_jamaah FROM logs WHERE user_id='$uid' AND tanggal='$tgl'");
$done_data = []; 
while($r=mysqli_fetch_assoc($done_q)) {
    $done_data[$r['kegiatan_id']] = ['checked' => true, 'is_jamaah' => $r['is_jamaah']];
}

$q_pesan = mysqli_query($conn, "SELECT pesan.*, users.nama_lengkap as pengirim FROM pesan JOIN users ON pesan.pengirim_id = users.id WHERE penerima_id='$uid' ORDER BY created_at DESC");
$inbox = []; $unread_count = 0;
while($p = mysqli_fetch_assoc($q_pesan)) {
    $inbox[] = $p;
    if ($p['is_read'] == 0) $unread_count++;
}

$kegiatan_grouped = ['ibadah_wajib' => [], 'ibadah_sunnah' => [], 'kegiatan_sosial' => []];
$q = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY id ASC");
while($r = mysqli_fetch_assoc($q)) {
    if (!isset($kegiatan_grouped[$r['kategori']])) $kegiatan_grouped[$r['kategori']] = [];
    $kegiatan_grouped[$r['kategori']][] = $r;
}

$cat_config = [
    'ibadah_wajib' => ['title' => 'Ibadah Wajib', 'color' => 'red', 'icon' => '🕌', 'desc' => 'Mutlak, jangan ditinggalkan.'],
    'ibadah_sunnah' => ['title' => 'Ibadah Sunnah', 'color' => 'amber', 'icon' => '✨', 'desc' => 'Penyempurna amal.'],
    'kegiatan_sosial' => ['title' => 'Kegiatan Sosial', 'color' => 'blue', 'icon' => '🤝', 'desc' => 'Bermanfaat bagi sesama.']
];

$cookie_name = "nasihat_harian_" . $uid . "_" . date('Ymd');
$show_nasihat = false;
if (!isset($_COOKIE[$cookie_name])) {
    $show_nasihat = true;
    setcookie($cookie_name, "1", strtotime('tomorrow'), "/");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Santri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        h1, h2, h3, .font-fun { font-family: 'Quicksand', sans-serif; }
        .custom-checkbox input.main-check:checked + div { background-color: #10b981; border-color: #10b981; color: white; }
        .custom-checkbox input.main-check:checked + div .check-icon { opacity: 1; transform: scale(1); }
        
        /* Toggle Switch */
        .toggle-checkbox:checked { right: 0; border-color: #ec4899; }
        .toggle-checkbox:checked + .toggle-label { background-color: #ec4899; }
        
        .msg-content a { color: #2563eb; text-decoration: underline; font-weight: 500; }
        .modal-animate { animation: slideUp 0.4s ease-out forwards; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-20">
        <div class="max-w-3xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="font-fun font-bold text-lg text-emerald-800">Mutaba'ahKu</span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="toggleModal('modalInbox', true)" class="relative p-2 text-gray-500 hover:text-indigo-600 bg-gray-50 rounded-full transition-colors">
                    <i data-lucide="mail" class="w-6 h-6"></i>
                    <?php if($unread_count > 0): ?><span class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full animate-pulse"></span><?php endif; ?>
                </button>
                <a href="../logout.php" class="text-red-500"><i data-lucide="log-out" class="w-5 h-5"></i></a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto p-4 pb-24">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-fun">Ahlan, <span class="text-emerald-600"><?= htmlspecialchars($_SESSION['nama']) ?></span>!</h1>
                <?php if($is_male): ?>
                    <p class="text-gray-500 text-sm mt-1">Laki-laki sejati sholatnya berjamaah!</p>
                <?php else: ?>
                    <p class="text-gray-500 text-sm mt-1">Wanita sholehah perhiasan dunia.</p>
                <?php endif; ?>
            </div>
            <form method="GET" class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200 w-fit">
                <i data-lucide="calendar" class="w-5 h-5 text-gray-400 ml-1"></i>
                <input type="date" name="tgl" value="<?= $tgl ?>" min="<?= $min_date ?>" max="<?= $today_str < $max_date ? $today_str : $max_date ?>" class="bg-transparent text-sm font-semibold text-gray-700 outline-none cursor-pointer" onchange="this.form.submit()">
            </form>
        </div>

        <form method="POST" id="formLaporan">
            <input type="hidden" name="tgl_laporan" value="<?= $tgl ?>">

            <!-- FITUR HAIDH (Khusus Perempuan) -->
            <?php if($is_female): ?>
            <div class="mb-6 bg-pink-50 border border-pink-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="bg-pink-100 p-2 rounded-full text-pink-600"><i data-lucide="droplets" class="w-5 h-5"></i></div>
                    <div>
                        <h3 class="font-bold text-pink-800 text-sm">Sedang Haidh?</h3>
                        <p class="text-xs text-pink-600">Aktifkan jika sedang berhalangan (Udzur Syar'i).</p>
                    </div>
                </div>
                <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                    <input type="checkbox" name="status_haidh" id="toggleHaidh" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer right-0" <?= $is_haidh_active ? 'checked' : '' ?> onchange="toggleHaidhState(this)">
                    <label for="toggleHaidh" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                </div>
            </div>
            <!-- Style Toggle Custom -->
            <style>
                #toggleHaidh:checked { right: 0; border-color: #ec4899; }
                #toggleHaidh:checked + label { background-color: #ec4899; }
                #toggleHaidh:not(:checked) { right: 50%; border-color: #d1d5db; }
            </style>
            <?php endif; ?>

            <!-- Container Kegiatan -->
            <div id="kegiatanContainer">
                <?php foreach($kegiatan_grouped as $kategori => $items): 
                    if(empty($items)) continue;
                    $conf = $cat_config[$kategori] ?? ['title'=>$kategori, 'color'=>'gray', 'icon'=>'📝', 'desc'=>''];
                    $col = $conf['color'];
                    $borderItem = "border-{$col}-100 hover:border-{$col}-300";
                ?>
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3 px-1">
                        <span class="text-xl"><?= $conf['icon'] ?></span>
                        <div><h2 class="font-bold text-lg text-gray-800 font-fun"><?= $conf['title'] ?></h2><p class="text-xs text-gray-500"><?= $conf['desc'] ?></p></div>
                    </div>
                    <div class="space-y-3">
                        <?php foreach($items as $r): 
                            $isChecked = isset($done_data[$r['id']]);
                            $isJamaahChecked = $isChecked ? ($done_data[$r['id']]['is_jamaah'] == 1) : false;
                            
                            $showJamaahOption = ($is_male && $r['kategori'] == 'ibadah_wajib');
                            
                            // Tandai item yang punya udzur untuk JS
                            $isUdzurItem = isset($r['ada_udzur']) && $r['ada_udzur'] == 1 ? 'true' : 'false';
                        ?>
                        <label class="custom-checkbox cursor-pointer block relative group item-kegiatan" data-udzur="<?= $isUdzurItem ?>">
                            <!-- Checkbox Utama -->
                            <input type="checkbox" name="keg[]" value="<?= $r['id'] ?>" class="sr-only main-check chk-amal" <?= $isChecked ? 'checked' : '' ?> data-jamaah-target="jamaah-opt-<?= $r['id'] ?>">
                            
                            <div class="bg-white border-2 <?= $borderItem ?> rounded-xl p-4 transition-all duration-200 hover:shadow-md item-box">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center bg-white check-circle">
                                            <svg class="check-icon w-4 h-4 text-emerald-600 opacity-0 transform scale-50 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-700 text-sm judul-kegiatan"><?= $r['nama_kegiatan'] ?></h3>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">+<?= $r['poin'] ?></span>
                                </div>

                                <!-- Opsi Jamaah -->
                                <?php if($showJamaahOption): ?>
                                <div id="jamaah-opt-<?= $r['id'] ?>" class="mt-3 pt-3 border-t border-gray-100 jamaah-container <?= $isChecked ? '' : 'hidden' ?>">
                                    <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg hover:bg-slate-100 transition">
                                        <input type="checkbox" name="is_jamaah[<?= $r['id'] ?>]" value="1" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500" <?= $isJamaahChecked ? 'checked' : '' ?>>
                                        <span class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                            <i data-lucide="users" class="w-3 h-3"></i> Berjamaah?
                                        </span>
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="fixed bottom-6 left-0 right-0 px-4 flex justify-center z-10">
                <button type="submit" name="save" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-full shadow-lg flex items-center gap-2 max-w-sm w-full justify-center transition-transform hover:scale-105">
                    <span>💾 Simpan Laporan</span>
                </button>
            </div>
        </form>
    </main>

    <!-- Modal Inbox -->
    <div id="modalInbox" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="toggleModal('modalInbox', false)"></div>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg modal-animate h-[70vh] flex flex-col relative z-10">
                <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2"><i data-lucide="mail" class="w-5 h-5"></i> Pesan Guru</h3>
                    <button onclick="toggleModal('modalInbox', false)" class="text-indigo-200 hover:text-white"><i data-lucide="x" class="w-6 h-6"></i></button>
                </div>
                <div class="p-4 bg-gray-50 flex-1 overflow-y-auto space-y-3">
                    <?php if(empty($inbox)): ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-400"><i data-lucide="inbox" class="w-12 h-12 mb-2 opacity-50"></i><p>Tidak ada pesan.</p></div>
                    <?php else: foreach($inbox as $msg): 
                        $isRead = $msg['is_read']; $cls = $isRead ? 'bg-white border-gray-200' : 'bg-blue-50 border-blue-200 shadow-sm'; ?>
                        <div onclick="bacaPesan(<?= $msg['id'] ?>, this)" class="<?= $cls ?> border rounded-xl p-4 cursor-pointer hover:shadow-md relative">
                            <?php if(!$isRead): ?><span class="absolute top-3 right-3 w-2 h-2 bg-red-500 rounded-full indicator-dot animate-pulse"></span><?php endif; ?>
                            <h4 class="font-bold text-gray-800 text-sm mb-1"><?= htmlspecialchars($msg['judul']) ?></h4>
                            <p class="text-sm text-gray-600 msg-content" data-content="<?= htmlspecialchars($msg['isi']) ?>"></p>
                            <div class="mt-2 pt-2 border-t border-gray-100 flex justify-between text-xs text-gray-400"><span><?= $msg['pengirim'] ?></span><span><?= date('d M H:i', strtotime($msg['created_at'])) ?></span></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nasihat & Niat -->
    <div id="modalNiat" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-sm w-full text-center relative z-10 modal-animate">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 mb-4"><span class="text-2xl">☝️</span></div>
            <h3 class="font-bold text-lg mb-2">Tajdidun Niat</h3>
            <p class="text-sm text-gray-600 mb-4">Jujur & Ikhlas karena Allah?</p>
            <div class="flex gap-2"><button onclick="batalCentang()" class="flex-1 bg-gray-200 py-2 rounded">Batal</button><button onclick="yakinCentang()" class="flex-1 bg-emerald-600 text-white py-2 rounded">Ya</button></div>
        </div>
        <div class="fixed inset-0 bg-black/50" onclick="batalCentang()"></div>
    </div>

    <div id="modalAI" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative z-10 modal-animate border-t-4 border-emerald-500">
            <div class="text-center mb-4"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 mb-3 text-3xl">👳‍♂️</div><h3 class="font-bold text-lg">Nasihat Pagi</h3></div>
            <div id="aiContent" class="text-sm text-gray-600 bg-gray-50 p-4 rounded-xl max-h-60 overflow-y-auto mb-4 leading-relaxed"><div class="flex justify-center items-center gap-2 py-4 text-emerald-600">Loading...</div></div>
            <button onclick="toggleModal('modalAI', false)" id="btnTutupAI" class="hidden w-full bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold">✅ Siap Mengamalkan</button>
        </div>
        <div class="fixed inset-0 bg-black/50"></div>
    </div>

    <script>
        lucide.createIcons();
        function toggleModal(id, show) { document.getElementById(id).classList.toggle('hidden', !show); }
        
        // Linkify
        function linkify(text) { return text.replace(/(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, '<a href="$1" target="_blank" class="text-blue-600 underline">$1</a>'); }
        document.querySelectorAll('.msg-content').forEach(el => el.innerHTML = linkify(el.getAttribute('data-content')));

        // Baca Pesan
        function bacaPesan(id, el) {
            el.classList.remove('bg-blue-50', 'border-blue-200'); el.classList.add('bg-white', 'border-gray-200');
            const dot = el.querySelector('.indicator-dot'); if(dot) dot.remove();
            fetch('../api/baca_pesan.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: 'id='+id });
        }

        // Logic Checkbox Jamaah (Show/Hide option)
        document.querySelectorAll('.main-check').forEach(chk => {
            chk.addEventListener('change', function() {
                const targetId = this.getAttribute('data-jamaah-target');
                const jamaahDiv = document.getElementById(targetId);
                if (jamaahDiv) {
                    if (this.checked) jamaahDiv.classList.remove('hidden');
                    else {
                        jamaahDiv.classList.add('hidden');
                        const jCheck = jamaahDiv.querySelector('input[type="checkbox"]');
                        if(jCheck) jCheck.checked = false;
                    }
                }
            });
        });

        // Logic Toggle Haidh (Visual Update) - DIPERBAIKI (Hapus CSS Global)
        function toggleHaidhState(toggle) {
            const isHaidh = toggle.checked;
            
            // Loop semua item kegiatan
            document.querySelectorAll('.item-kegiatan').forEach(item => {
                const isUdzur = item.getAttribute('data-udzur') === 'true';
                const mainInput = item.querySelector('.main-check');
                const box = item.querySelector('.item-box');
                const labelTitle = item.querySelector('.judul-kegiatan');
                
                if(isUdzur) {
                    if(isHaidh) {
                        // Jika Udzur aktif (Haidh): Disable input, visual coret
                        mainInput.disabled = true;
                        // Visual changes
                        box.classList.add('bg-gray-100', 'border-gray-200');
                        box.classList.remove('bg-white');
                        labelTitle.classList.add('line-through', 'text-gray-400');
                        
                        // Tambah Badge Udzur jika belum ada
                        if(!labelTitle.innerHTML.includes('Udzur')) {
                             labelTitle.innerHTML += ' <span class="text-xs bg-pink-100 text-pink-500 px-1 rounded no-underline ml-1 badge-udzur">Udzur</span>';
                        }
                    } else {
                        // Reset
                        mainInput.disabled = false;
                        // Hapus visual changes
                        box.classList.remove('bg-gray-100', 'border-gray-200');
                        box.classList.add('bg-white');
                        labelTitle.classList.remove('line-through', 'text-gray-400');
                        
                        // Hapus badge udzur
                        const badge = labelTitle.querySelector('.badge-udzur');
                        if(badge) badge.remove();
                    }
                }
            });
        }
        
        // Init Haidh State saat load
        document.addEventListener("DOMContentLoaded", function() {
            const toggle = document.getElementById('toggleHaidh');
            if(toggle) toggleHaidhState(toggle);
        });

        // Interceptor Niat
        let targetCheckbox = null;
        document.querySelectorAll('.chk-amal').forEach(item => {
            item.addEventListener('click', function(e) {
                // Hanya cegah jika sedang checked (bukan uncheck) DAN input tidak disabled (efek haidh)
                if (this.checked && !this.disabled) { 
                    e.preventDefault(); 
                    targetCheckbox = this; 
                    toggleModal('modalNiat', true); 
                }
            });
        });
        window.yakinCentang = function() { 
            if(targetCheckbox) { 
                targetCheckbox.checked = true; 
                // Trigger event change manual agar logic jamaah jalan
                targetCheckbox.dispatchEvent(new Event('change'));
            }
            toggleModal('modalNiat', false); targetCheckbox = null; 
        }
        window.batalCentang = function() { toggleModal('modalNiat', false); targetCheckbox = null; }

        // AI Nasihat (Logika Cookie)
        if(<?= $show_nasihat?'true':'false' ?>) {
            toggleModal('modalAI', true);
            fetch('../api/generate_nasihat.php').then(r=>r.json()).then(d => {
                const c = document.getElementById('aiContent');
                if(d.success) { c.innerHTML = d.message.replace(/\n/g, '<br>'); document.getElementById('btnTutupAI').classList.remove('hidden'); }
                else { c.innerHTML = '<p class="text-center text-red-500">Gagal memuat nasihat.</p>'; setTimeout(()=>toggleModal('modalAI', false), 2000); }
            }).catch(() => toggleModal('modalAI', false));
        }
    </script>
</body>
</html>