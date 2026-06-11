<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_set extends CI_Controller
{

    public function __construct()
    {
        parent::__construct(TRUE);
        if ($this->session->userdata('logged') == NULL) {
            header("Location:" . site_url('manage/auth/login') . "?location=" . urlencode($_SERVER['REQUEST_URI']));
        }
        $this->load->model(array('payment/Payment_model', 'student/Student_model', 'period/Period_model', 'pos/Pos_model', 'bulan/Bulan_model', 'bebas/Bebas_model', 'bebas/Bebas_pay_model', 'setting/Setting_model', 'kredit/Kredit_model', 'debit/Debit_model', 'logs/Logs_model'));
    }

    // payment view in list
    public function index($offset = NULL)
    {
        // Apply Filter
        // Get $_GET variable
        $q = $this->input->get(NULL, TRUE);

        $data['q'] = $q;

        $params = array();

        // Date start
        if (isset($q['ds']) && !empty($q['ds']) && $q['ds'] != '') {
            $params['date_start'] = $q['ds'];
        }

        // Date end
        if (isset($q['de']) && !empty($q['de']) && $q['de'] != '') {
            $params['date_end'] = $q['de'];
        }

        // Get data dengan filter
        $data['bulan'] = $this->Bulan_model->get($params);
        $data['bebas'] = $this->Bebas_model->get($params);
        $data['free'] = $this->Bebas_pay_model->get($params);
        $data['kredit'] = $this->Kredit_model->get($params);
        $data['debit'] = $this->Debit_model->get($params);

        $data['title'] = 'Laporan Keuangan';
        $data['main'] = 'report/report_list';
        $this->load->view('manage/layout', $data);
    }

    public function report_bill()
    {
        $q = $this->input->get(NULL, TRUE);

        $data['q'] = $q;

        $params = array();
        $param = array();
        $stu = array();
        $free = array();

        if (isset($q['p']) && !empty($q['p']) && $q['p'] != '') {
            $params['period_id'] = $q['p'];
            $param['period_id'] = $q['p'];
            $stu['period_id'] = $q['p'];
            $free['period_id'] = $q['p'];
        }

        if (isset($q['c']) && !empty($q['c']) && $q['c'] != '') {
            $params['class_id'] = $q['c'];
            $param['class_id'] = $q['c'];
            $stu['class_id'] = $q['c'];
            $free['class_id'] = $q['c'];
        }

        if (isset($q['k']) && !empty($q['k']) && $q['k'] != '') {
            $params['majors_id'] = $q['k'];
            $param['majors_id'] = $q['k'];
            $stu['majors_id'] = $q['k'];
            $free['majors_id'] = $q['k'];
        }

        // FILTER SISWA AKTIF - Hanya tampilkan siswa dengan status aktif
        $params['student_status'] = 1;
        $param['student_status'] = 1;
        $stu['student_status'] = 1;
        $free['student_status'] = 1;

        $param['paymentt'] = TRUE;
        $params['grup'] = TRUE;
        $stu['group'] = TRUE;

        $data['period'] = $this->Period_model->get($params);
        $data['class'] = $this->Student_model->get_class($params);
        $data['majors'] = $this->Student_model->get_majors($params);
        $data['student'] = $this->Bulan_model->get($stu);
        
        // PERBAIKAN: Menambahkan field yang diperlukan
        $free['select'] = 'bebas.*, pos.pos_id, pos.pos_name, period.period_start, period.period_end';
        $data['bulan'] = $this->Bulan_model->get($free);
        $data['month'] = $this->Bulan_model->get($params);
        
        // PERBAIKAN: Menambahkan field yang diperlukan
        $param['select'] = 'bulan.*, pos.pos_id, pos.pos_name, period.period_start, period.period_end';
        $data['py'] = $this->Bulan_model->get($param);
        
        // PERBAIKAN: Menambahkan field yang diperlukan
        $params['select'] = 'bebas.*, pos.pos_id, pos.pos_name, period.period_start, period.period_end';
        $data['bebas'] = $this->Bebas_model->get($params);
        
        // PERBAIKAN: Menambahkan field yang diperlukan
        $free['select'] = 'bebas.*, pos.pos_id, pos.pos_name, period.period_start, period.period_end';
        $data['free'] = $this->Bebas_model->get($free);

        $config['suffix'] = '?' . http_build_query($_GET, '', "&");

        $data['title'] = 'Rekapitulasi';
        $data['main'] = 'report/report_bill_list';
        $this->load->view('manage/layout', $data);
    }

    public function report()
    {
        // Apply Filter
        // Get $_GET variable
        $q = $this->input->get(NULL, TRUE);

        $data['q'] = $q;

        $params = array();

        // Date start
        if (isset($q['ds']) && !empty($q['ds']) && $q['ds'] != '') {
            $params['date_start'] = $q['ds'];
        }

        // Date end
        if (isset($q['de']) && !empty($q['de']) && $q['de'] != '') {
            $params['date_end'] = $q['de'];
        }

        $params['status'] = 1;

        $data['bulan'] = $this->Bulan_model->get($params);
        $data['bebas'] = $this->Bebas_model->get($params);
        $data['free'] = $this->Bebas_pay_model->get($params);
        $data['kredit'] = $this->Kredit_model->get($params);
        $data['debit'] = $this->Debit_model->get($params);
        $data['setting_school'] = $this->Setting_model->get(array('id' => SCHOOL_NAME));

        // Load library PHPExcel
        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        
        // Set properties
        $objPHPExcel->getProperties()
            ->setCreator($this->session->userdata('ufullname'))
            ->setTitle("Laporan Keuangan")
            ->setSubject("Laporan Keuangan");
        
        // Create worksheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        // Set header
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->setCellValue('A2', $data['setting_school']['setting_value']);
        $sheet->setCellValue('A3', 'Tanggal Laporan: ' . pretty_date($q['ds'], 'd F Y', false) . ' s/d ' . pretty_date($q['de'], 'd F Y', false));
        $sheet->setCellValue('A4', 'Tanggal Unduh: ' . pretty_date(date('Y-m-d h:i:s'), 'd F Y, H:i', false));
        $sheet->setCellValue('C4', 'Pengunduh: ' . $this->session->userdata('ufullname'));

        // Set column headers
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'PEMBAYARAN');
        $sheet->setCellValue('C5', 'NAMA SISWA');
        $sheet->setCellValue('D5', 'KELAS');
        $sheet->setCellValue('E5', 'TANGGAL');
        $sheet->setCellValue('F5', 'PENERIMAAN');
        $sheet->setCellValue('G5', 'PENGELUARAN');
        $sheet->setCellValue('H5', 'KETERANGAN');

        // Style untuk header
        $headerStyle = array(
            'font' => array('bold' => true),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
        );
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);

        // Fill data
        $row = 6;
        $no = 1;
        
        if (!empty($data['bulan'])) {
            foreach ($data['bulan'] as $item) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $item['pos_name'] . ' - T.P ' . $item['period_start'] . '/' . $item['period_end'] . ' - ' . '(' . $item['month_name'] . ')');
                $sheet->setCellValue('C' . $row, $item['student_full_name']);
                $sheet->setCellValue('D' . $row, $item['class_name']);
                $sheet->setCellValue('E' . $row, pretty_date($item['bulan_date_pay'], 'm/d/Y', FALSE));
                $sheet->setCellValue('F' . $row, $item['bulan_bill']);
                $sheet->setCellValue('G' . $row, '-');
                $sheet->setCellValue('H' . $row, $item['bulan_pay_desc']);
                $row++;
                $no++;
            }
        }

        // Auto size columns
        foreach(range('A','H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Output file
        $filename = 'LAPORAN_KEUANGAN_' . date('dmY') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    // Rekapituliasi
    public function report_bill_detail()
    {
        $q = $this->input->get(NULL, TRUE);

        $data['q'] = $q;

        $params = array();
        $param = array();
        $stu = array();
        $free = array();

        if (isset($q['p']) && !empty($q['p']) && $q['p'] != '') {
            $params['period_id'] = $q['p'];
            $param['period_id'] = $q['p'];
            $stu['period_id'] = $q['p'];
            $free['period_id'] = $q['p'];
        }

        if (isset($q['c']) && !empty($q['c']) && $q['c'] != '') {
            $params['class_id'] = $q['c'];
            $param['class_id'] = $q['c'];
            $stu['class_id'] = $q['c'];
            $free['class_id'] = $q['c'];
        }

        if (isset($q['k']) && !empty($q['k']) && $q['k'] != '') {
            $params['majors_id'] = $q['k'];
            $param['majors_id'] = $q['k'];
            $stu['majors_id'] = $q['k'];
            $free['majors_id'] = $q['k'];
        }

        // FILTER SISWA AKTIF - Hanya tampilkan siswa dengan status aktif
        $params['student_status'] = 1;
        $param['student_status'] = 1;
        $stu['student_status'] = 1;
        $free['student_status'] = 1;

        $param['paymentt'] = TRUE;
        $params['grup'] = TRUE;
        $stu['group'] = TRUE;

        $data['period'] = $this->Period_model->get($params);
        $data['class'] = $this->Student_model->get_class($stu);
        $data['majors'] = $this->Student_model->get_majors($stu);
        $data['student'] = $this->Bulan_model->get($stu);
        $data['bulan'] = $this->Bulan_model->get($free);
        $data['month'] = $this->Bulan_model->get($params);
        $data['py'] = $this->Bulan_model->get($param);
        $data['bebas'] = $this->Bebas_model->get($params);
        $data['free'] = $this->Bebas_model->get($free);

        $data['setting_school'] = $this->Setting_model->get(array('id' => SCHOOL_NAME));

        // Load library PHPExcel
        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        
        // Set properties
        $objPHPExcel->getProperties()
            ->setCreator($this->session->userdata('ufullname'))
            ->setTitle("Rekapitulasi Pembayaran")
            ->setSubject("Rekapitulasi Pembayaran");
        
        // Create worksheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Rekapitulasi');

        // Set header
        $sheet->setCellValue('A1', 'REKAPITULASI PEMBAYARAN SISWA');
        $sheet->setCellValue('A2', $data['setting_school']['setting_value']);
        
        foreach ($data['period'] as $period) {
            $year = $period['period_start'] . '/' . $period['period_end'];
            $periode = ($q['p'] == $period['period_id']) ? $year : '';
            $sheet->setCellValue('A3', 'Periode Laporan: ' . $periode);
        }
        
        $sheet->setCellValue('A4', 'Tanggal Unduh: ' . pretty_date(date('Y-m-d h:i:s'), 'd F Y, H:i', false));
        $sheet->setCellValue('C4', 'Pengunduh: ' . $this->session->userdata('ufullname'));

        // Merge cells for headers
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', 'NO');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', 'KELAS');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('C5', 'NAMA SISWA');

        // Header style
        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '000000'))
        );
        $sheet->getStyle('A5:C6')->applyFromArray($headerStyle);

        // Judul Pembayaran Bulanan
        $monthCount = count($data['month']);
        $lastColumn = PHPExcel_Cell::stringFromColumnIndex(3 + $monthCount - 1);
        $sheet->mergeCells('D5:' . $lastColumn . '5');
        
        foreach ($data['py'] as $row) {
            $sheet->setCellValue('D5', $row['pos_name'] . ' - T.P ' . $row['period_start'] . '/' . $row['period_end']);
        }
        $sheet->getStyle('D5:' . $lastColumn . '5')->applyFromArray($headerStyle);

        // Header untuk bulan
        $colIndex = 3; // Dimulai dari kolom D (index 3)
        foreach ($data['month'] as $key) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . '6', $key['month_name']);
            $sheet->getStyle($colLetter . '6')->applyFromArray($headerStyle);
            $colIndex++;
        }

        // Header untuk pembayaran bebas
        $bebasStartCol = $colIndex;
        foreach ($data['bebas'] as $row) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
            $sheet->mergeCells($colLetter . '5:' . $colLetter . '6');
            $sheet->setCellValue($colLetter . '5', $row['pos_name'] . ' - T.P ' . $row['period_start'] . '/' . $row['period_end']);
            $sheet->getStyle($colLetter . '5:' . $colLetter . '6')->applyFromArray($headerStyle);
            $colIndex++;
        }

        // Isi data siswa
        $rowIndex = 7;
        $no = 1;
        
        foreach ($data['student'] as $student) {
            $sheet->setCellValue('A' . $rowIndex, $no);
            $sheet->setCellValue('B' . $rowIndex, (majors() == 'senior') ? 
                $student['class_name'] . '-' . $student['majors_short_name'] : $student['class_name']);
            $sheet->setCellValue('C' . $rowIndex, $student['student_full_name']);

            // Data pembayaran bulanan
            $colIndex = 3;
            foreach ($data['month'] as $month) {
                $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                $value = '';
                
                foreach ($data['bulan'] as $bulan) {
                    if ($bulan['student_student_id'] == $student['student_student_id'] && 
                        $bulan['month_name'] == $month['month_name']) {
                        $value = ($bulan['bulan_status'] == 1) ? 'Lunas' : $bulan['bulan_bill'];
                        break;
                    }
                }
                
                $sheet->setCellValue($colLetter . $rowIndex, $value);
                $colIndex++;
            }

            // Data pembayaran bebas
            foreach ($data['free'] as $free) {
                if ($free['student_student_id'] == $student['student_student_id']) {
                    $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                    $value = ($free['bebas_bill'] == $free['bebas_total_pay']) ? 
                        'Lunas' : ($free['bebas_bill'] - $free['bebas_total_pay']);
                    $sheet->setCellValue($colLetter . $rowIndex, $value);
                    $colIndex++;
                }
            }

            $rowIndex++;
            $no++;
        }

        // Auto size columns
        $lastColumn = PHPExcel_Cell::stringFromColumnIndex($colIndex - 1);
        foreach(range('A', $lastColumn) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set border untuk semua data
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $sheet->getStyle('A5:' . $lastColumn . ($rowIndex-1))->applyFromArray($styleArray);

        // Output file
        $kelas = 'PEMBAYARAN_SISWA';
        foreach ($data['class'] as $row) {
            if ($q['c'] == $row['class_id']) {
                $kelas = $row['class_name'];
                break;
            }
        }

        $filename = 'REKAPITULASI_' . $kelas . '_' . date('dmY') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
}

/* End of file Report_set.php */
/* Location: ./application/modules/report/controllers/Report_set.php */