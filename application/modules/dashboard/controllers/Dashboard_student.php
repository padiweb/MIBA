<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_student extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Cek session login siswa
        if ($this->session->userdata('logged_student') == NULL) {
            header("Location:" . site_url('student/auth/login') . "?location=" . urlencode($_SERVER['REQUEST_URI']));
        }
        
        // Load model yang diperlukan
        $this->load->model(array(
            'student/Student_model', 
            'bulan/Bulan_model', 
            'setting/Setting_model', 
            'bebas/Bebas_model', 
            'information/Information_model', 
            'bebas/Bebas_pay_model', 
            'period/Period_model'
        ));
    }

    public function index() {
        // Ambil ID siswa dari session
        $student_id = $this->session->userdata('uid_student');

        // Ambil data siswa dengan join class dan majors
        $data['siswa'] = $this->Student_model->get(array(
            'student_id' => $student_id,
            'join' => ['class', 'majors'] // Join dengan tabel class dan majors
        ));

        // Pastikan format array konsisten
        if (isset($data['siswa']['student_id'])) {
            $data['siswa'] = array($data['siswa']); // Konversi ke array multidimensi
        }

        // Ambil periode aktif
        $data['period'] = $this->Period_model->get(array('period_status' => 1));

        // Ambil data tagihan bulanan
    // $data['bulan'] = $this->Bulan_model->get(array(
    //     'student_id' => $student_id,
    //     'status' => 0,
    //     'period_status' => 1,
    //     'join' => ['month', 'period', 'pos'] // Join dengan tabel terkait
    // ));
    
    // Ambil data tagihan bulanan
$data['bulan'] = $this->Bulan_model->get(array(
    'student_id' => $student_id, // Pastikan ini sesuai dengan field di database
    'status' => 0, // Status tagihan (0 = belum lunas, 1 = lunas)
    'period_status' => 1, // Hanya ambil periode aktif
    'join' => ['month', 'period', 'pos'] // Join dengan tabel terkait
));

// Debug data tagihan bulanan
// echo '<pre>';
// print_r($data['bulan']);
// echo '</pre>';
// die();

    // Ambil data untuk card pembayaran bulanan
    $data['student'] = $this->Bulan_model->get(array(
        'student_id' => $student_id,
        'join' => ['period', 'pos'] // Join untuk kebutuhan tampilan
    ));

        // Ambil data tagihan bebas
        $data['bebas'] = $this->Bebas_model->get(array(
            'student_id' => $student_id,
            'period_status' => 1,
            'join' => ['period', 'pos'] // Join dengan tabel terkait
        ));

        // Hitung total tagihan
        $data['total_bulan'] = array_sum(array_column($data['bulan'], 'bulan_bill'));
        $data['total_bebas'] = array_sum(array_column($data['bebas'], 'bebas_bill'));
        $data['total_bebas_pay'] = array_sum(array_column($data['bebas'], 'bebas_total_pay'));

        // Data untuk card pembayaran
        $data['student'] = $this->Bulan_model->get(array(
            'student_id' => $student_id,
            'join' => ['period', 'pos'] // Join untuk kebutuhan tampilan
        ));

        // Data informasi
        $data['information'] = $this->Information_model->get(array('information_publish' => 1));

        // Debug data (nonaktifkan saat production)
        // echo '<pre>'; print_r($data); die();

        // Load view
        $data['title'] = 'Dashboard';
        $data['main'] = 'dashboard/dashboard_student';
        $this->load->view('student/layout', $data);
    }
}