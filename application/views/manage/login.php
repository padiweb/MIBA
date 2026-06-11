<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIBA | Login</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo media_url('img/logo.png') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Menggunakan font Poppins sebagai default */
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom styles untuk efek glassmorphism yang lebih baik */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.2); /* Latar belakang semi-transparan */
            backdrop-filter: blur(10px); /* Efek blur pada elemen di belakang */
            -webkit-backdrop-filter: blur(10px); /* Dukungan untuk Safari */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Border tipis */
        }
        
        /* Placeholder styling */
        .form-input::placeholder {
            color: #9ca3af; /* Warna placeholder abu-abu */
        }
    </style>
</head>
<body class="bg-gray-200">

    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-sky-500 to-indigo-600">
        
        <div class="w-full max-w-sm">
            <div class="glassmorphism rounded-2xl shadow-xl p-8">
                
                <!-- Logo Perusahaan -->
                <div class="flex justify-center mb-6">
                    <?php if (isset($setting_logo) and $setting_logo['setting_value'] == NULL) { ?>
                        <img src="<?php echo media_url('img/logo.png') ?>" alt="Logo" class="h-20">
                    <?php } else { ?>
                        <img src="<?php echo upload_url('school/' . $setting_logo['setting_value']) ?>" alt="Logo" class="h-20">
                    <?php } ?>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-white">Manajemen Ibnu Abbas</h1>
                    <p class="text-gray-200">Ahlan Wa Sahlan!</p>
                </div>
                
                <!-- Form Login Manajemen -->
                <?php echo form_open('manage/auth/login', array('class' => 'space-y-4')); ?>
                    
                    <div>
                        <label for="email" class="sr-only">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-300"></i>
                            </div>
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                placeholder="Email" 
                                required 
                                autofocus
                                class="form-input w-full pl-10 pr-4 py-2 bg-white bg-opacity-30 border border-transparent rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white transition duration-300">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-300"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                placeholder="Password" 
                                required
                                class="form-input w-full pl-10 pr-4 py-2 bg-white bg-opacity-30 border border-transparent rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white transition duration-300">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-sky-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-opacity-75 transition duration-300 transform hover:scale-105">
                        LOGIN
                    </button>
                    
                <?php echo form_close(); ?>

                <!-- Pemisah -->
                <div class="my-6 flex items-center">
                    <div class="flex-grow border-t border-gray-200 opacity-50"></div>
                    <span class="mx-4 text-xs font-medium text-white">MENU SANTRI</span>
                    <div class="flex-grow border-t border-gray-200 opacity-50"></div>
                </div>

                <!-- Tombol Menu Siswa -->
                <div class="space-y-4">
                    <a href="<?php echo base_url() ?>student" class="block w-full text-center bg-white bg-opacity-30 text-white font-semibold py-2 px-4 rounded-lg hover:bg-opacity-40 transition duration-300">
                        <i class="fas fa-user-graduate mr-2"></i> Login Santri
                    </a>
                    <a href="<?php echo base_url() ?>home" class="block w-full text-center bg-white bg-opacity-30 text-white font-semibold py-2 px-4 rounded-lg hover:bg-opacity-40 transition duration-300">
                        <i class="fas fa-search-dollar mr-2"></i> Cek Pembayaran
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-white mt-8 opacity-75">
                <p>Copyright Padiweb. © Ibnu Abbas 2024</p>
                <p>Version 1.0.2</p>
            </div>
        </div>
        
    </div>
</body>
</html>

