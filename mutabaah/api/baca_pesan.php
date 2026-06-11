<?php
require '../config.php';
cekLogin(['santri']);

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $uid = $_SESSION['user_id'];
    // Pastikan pesan milik santri ini
    mysqli_query($conn, "UPDATE pesan SET is_read=1 WHERE id=$id AND penerima_id=$uid");
    echo json_encode(['success' => true]);
}
?>