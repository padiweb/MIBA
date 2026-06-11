<?php
require '../config.php';
cekLogin(['superadmin']);

if (isset($_POST['import'])) {
    $fileName = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['size'] > 0) {
        $file = fopen($fileName, "r");
        
        // Lewati baris pertama (Header)
        fgetcsv($file); 
        
        $sukses = 0;
        $gagal = 0;
        $pesan_gagal = "";
        $baris = 2; // Mulai dari baris ke-2

        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            // Validasi jumlah kolom
            if (count($column) < 6) {
                if (array_filter($column)) { 
                    $gagal++;
                    $pesan_gagal .= "Baris $baris: Format kolom tidak lengkap.\\n";
                }
                $baris++;
                continue;
            }

            // Ambil data & Trim
            $nama = mysqli_real_escape_string($conn, trim($column[0]));
            $username = mysqli_real_escape_string($conn, trim($column[1]));
            $raw_password = trim($column[2]);
            $jk = strtoupper(trim($column[3])); 
            $nama_kelas = mysqli_real_escape_string($conn, trim($column[4]));
            $hp = mysqli_real_escape_string($conn, trim($column[5]));

            if (empty($nama) || empty($username) || empty($raw_password)) {
                $gagal++;
                $pesan_gagal .= "Baris $baris: Data wajib kosong.\\n";
                $baris++;
                continue;
            }

            $password = password_hash($raw_password, PASSWORD_DEFAULT);
            if ($jk !== 'L' && $jk !== 'P') $jk = 'L';

            // Cari ID Kelas
            $q_kelas = mysqli_query($conn, "SELECT id FROM kelas WHERE nama_kelas LIKE '$nama_kelas'");
            $kelas_row = mysqli_fetch_assoc($q_kelas);
            
            if ($kelas_row) {
                $kelas_id = $kelas_row['id'];
                
                // Cek username
                $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
                if (mysqli_num_rows($check) == 0) {
                    $sql = "INSERT INTO users (username, password, nama_lengkap, role, kelas_id, no_hp_ortu, jenis_kelamin) 
                            VALUES ('$username', '$password', '$nama', 'santri', '$kelas_id', '$hp', '$jk')";
                    
                    if (mysqli_query($conn, $sql)) {
                        $sukses++;
                    } else {
                        $gagal++;
                        $pesan_gagal .= "Baris $baris: Gagal DB.\\n";
                    }
                } else {
                    $gagal++;
                    $pesan_gagal .= "Baris $baris: Username '$username' ada.\\n";
                }
            } else {
                $gagal++;
                $pesan_gagal .= "Baris $baris: Kelas '$nama_kelas' tdk ditemukan.\\n";
            }
            $baris++;
        }
        fclose($file);
        
        // Susun Pesan
        $msg_content = "Import Selesai!\\nSukses: $sukses\\nGagal: $gagal";
        if ($gagal > 0) {
            $msg_content .= "\\n\\nDetail Error:\\n" . $pesan_gagal;
        }

        // Gunakan json_encode untuk escape string agar aman di JS
        $alert_msg = json_encode($msg_content); 

        echo "<!DOCTYPE html><html><head><title>Processing...</title></head><body>";
        echo "<script>
            alert($alert_msg); 
            window.location.href='santri.php';
        </script>";
        echo "</body></html>";
    } else {
        echo "<script>alert('File kosong.'); window.location='santri.php';</script>";
    }
}
?>