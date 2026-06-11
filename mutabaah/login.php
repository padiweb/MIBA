<?php
require 'config.php';

// Logika PHP tetap sama
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kelas_id'] = $user['kelas_id'];

        // Redirect sesuai role
        if ($user['role'] == 'superadmin') header("Location: superadmin/dashboard.php");
        else if ($user['role'] == 'guru') header("Location: guru/dashboard.php");
        else header("Location: santri/dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= htmlspecialchars($nama_sekolah) ?></title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all hover:scale-[1.01] duration-300">
        
        <!-- Header Dinamis -->
        <div class="bg-emerald-600 p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-white opacity-10 transform -skew-y-6 origin-top-left"></div>
            <!-- Menggunakan variabel dari config.php -->
            <h1 class="text-2xl font-bold text-white relative z-10 leading-tight">
                <?= htmlspecialchars($nama_sekolah) ?>
            </h1>
            <p class="text-emerald-100 mt-2 text-sm relative z-10 font-medium">
                <?= htmlspecialchars($nama_kegiatan) ?>
            </p>
        </div>

        <!-- Form Container -->
        <div class="p-8">
            <h2 class="text-xl font-bold text-gray-800 text-center mb-6">Ahlan Wa Sahlan</h2>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r text-sm flex items-start gap-2" role="alert">
                    <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input type="text" name="username" id="username" required 
                            class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                            placeholder="Masukkan username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input type="password" name="password" id="password" required 
                            class="w-full pl-10 pr-12 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                            placeholder="••••••••">
                        
                        <!-- Tombol Toggle Password -->
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                    <span>Masuk Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400">
                    &copy; <?= date('Y') ?> Aplikasi Mutaba'ah.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Inisialisasi Icon
        lucide.createIcons();

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Ganti icon ke 'eye-off' (mata dicoret)
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                // Kembalikan icon ke 'eye' (mata terbuka)
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            // Refresh icon agar perubahan terlihat
            lucide.createIcons();
        }
    </script>
</body>
</html>