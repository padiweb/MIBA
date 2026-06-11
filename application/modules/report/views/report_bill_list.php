<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo isset($title) ? '' . $title : null; ?>
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('manage') ?>"><i class="fa fa-th"></i> Home</a></li>
            <li class="active"><?php echo isset($title) ? '' . $title : null; ?></li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header">
                        <?php echo form_open(current_url(), array('method' => 'get', 'id' => 'filter-form')) ?>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <select class="form-control" name="p" id="period-select" required>
                                        <option value="">-- Pilih Tahun Pelajaran --</option>
                                        <?php foreach ($period as $row) : ?>
                                            <option <?php echo (isset($q['p']) and $q['p'] == $row['period_id']) ? 'selected' : '' ?> value="<?php echo $row['period_id'] ?>"><?php echo $row['period_start'] . '/' . $row['period_end'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Unit Sekolah</label>
                                    <select class="form-control" name="k" id="majors-select" <?php echo (isset($q['c']) && !empty($q['c'])) ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Unit Sekolah --</option>
                                        <?php foreach ($majors as $row) : ?>
                                            <option <?php echo (isset($q['k']) and $q['k'] == $row['majors_id']) ? 'selected' : '' ?> value="<?php echo $row['majors_id'] ?>"><?php echo $row['majors_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kelas</label>
                                    <select class="form-control" name="c" id="class-select" <?php echo (isset($q['k']) && !empty($q['k'])) ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php 
                                        // Tampilkan kelas berdasarkan unit sekolah yang dipilih (jika ada)
                                        if (isset($q['k'])) {
                                            foreach ($class as $row) {
                                                if (isset($row['majors_id']) && $row['majors_id'] == $q['k']) {
                                                    echo '<option ' . ((isset($q['c']) and $q['c'] == $row['class_id']) ? 'selected' : '') . ' value="' . $row['class_id'] . '">' . $row['class_name'] . '</option>';
                                                }
                                            }
                                        } else {
                                            // Tampilkan semua kelas jika belum memilih unit sekolah
                                            foreach ($class as $row) {
                                                echo '<option ' . ((isset($q['c']) and $q['c'] == $row['class_id']) ? 'selected' : '') . ' value="' . $row['class_id'] . '">' . $row['class_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-top:25px;">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter Data</button>
                                    <div style="margin-top:10px;">
                                        <?php if ($q and !empty($py) && isset($q['p']) && !empty($q['p'])) { ?>
                                            <a class="btn btn-success" href="<?php echo site_url('manage/report/report_bill_detail' . '/?' . http_build_query($q)) ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                                            <button type="button" class="btn btn-info" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
                
                <?php if ($q and !empty($py) && isset($q['p']) && !empty($q['p'])) { ?>
                    <div class="box box-success">
                        <div class="box-body table-responsive">
                            <table class="table table-responsive table-hover table-bordered" style="white-space: nowrap;">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">Kelas</th>
                                    <th rowspan="2">Nama</th>
                                    <?php foreach ($py as $row) : ?>
                                        <th colspan="<?php echo count($month) ?>">
                                            <center><?php echo $row['pos_name'] . ' - T.P ' . $row['period_start'] . '/' . $row['period_end']; ?></center>
                                        </th>
                                    <?php endforeach ?>
                                    <?php foreach ($bebas as $key) : ?>
                                        <th rowspan="2">
                                            <center><?php echo $key['pos_name'] . ' - T.P ' . $key['period_start'] . '/' . $key['period_end']; ?></center>
                                        </th>
                                    <?php endforeach ?>
                                    <!-- TAMBAHAN: Kolom Total Dibayar dan Kekurangan -->
                                    <th rowspan="2">Total Dibayar</th>
                                    <th rowspan="2">Kekurangan</th>
                                </tr>
                                <tr>
                                    <?php 
                                    // Urutkan bulan dari Juli sampai Juni
                                    $sortedMonths = [];
                                    $monthOrder = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
                                    
                                    foreach ($monthOrder as $order) {
                                        foreach ($month as $key) {
                                            if (isset($key['month_name']) && $key['month_name'] == $order) {
                                                $sortedMonths[] = $key;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Hanya tampilkan bulan yang ada datanya
                                    foreach ($sortedMonths as $key) : ?>
                                        <?php if (isset($key['month_name'])) : ?>
                                            <th><?php echo $key['month_name'] ?></th>
                                        <?php endif; ?>
                                    <?php endforeach ?>
                                </tr>
                
                                <?php 
                                $no = 1;
                                $grand_total_dibayar = 0;
                                $grand_total_kekurangan = 0;
                                
                                foreach ($student as $row) : 
                                    $total_dibayar_siswa = 0;
                                    $total_tagihan_siswa = 0;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo isset($row['class_name']) ? $row['class_name'] : ''; ?></td>
                                        <td><?php echo isset($row['student_full_name']) ? $row['student_full_name'] : ''; ?></td>
                                        
                                        <?php 
                                        // Data pembayaran bulanan
                                        foreach ($sortedMonths as $sortedMonth) :
                                            $display_value = '-';
                                            $color = 'black';
                                            
                                            if (isset($sortedMonth['month_name'])) {
                                                $found = false;
                                                foreach ($bulan as $key) {
                                                    if (isset($key['student_student_id']) && 
                                                        isset($key['month_name']) && 
                                                        $key['student_student_id'] == $row['student_student_id'] && 
                                                        $key['month_name'] == $sortedMonth['month_name']) {
                                                        
                                                        $found = true;
                                                        
                                                        // Hitung total dibayar
                                                        if ($key['bulan_status'] == 1) {
                                                            $display_value = 'Lunas';
                                                            $color = '#00E640';
                                                            $total_dibayar_siswa += $key['bulan_bill'];
                                                        } else {
                                                            $display_value = number_format($key['bulan_bill'], 0, ',', '.');
                                                            $color = 'red';
                                                            $total_dibayar_siswa += isset($key['bulan_pay_amount']) ? $key['bulan_pay_amount'] : 0;
                                                        }
                                                        $total_tagihan_siswa += $key['bulan_bill'];
                                                        
                                                        break;
                                                    }
                                                }
                                                
                                                if (!$found) {
                                                    $display_value = '-';
                                                    $color = 'black';
                                                }
                                            }
                                        ?>
                                            <td style="color:<?php echo $color; ?>">
                                                <?php echo $display_value; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        
                                        <?php 
                                        // Data pembayaran bebas
                                        foreach ($bebas as $bebas_item) : 
                                            $display_value = '-';
                                            $color = 'black';
                                            $dibayar_bebas = 0;
                                            $tagihan_bebas = 0;
                                            
                                            foreach ($free as $key) {
                                                if (isset($key['student_student_id']) && 
                                                    $key['student_student_id'] == $row['student_student_id']) {
                                                    
                                                    $dibayar_bebas = isset($key['bebas_total_pay']) ? $key['bebas_total_pay'] : 0;
                                                    $tagihan_bebas = isset($key['bebas_bill']) ? $key['bebas_bill'] : 0;
                                                    
                                                    if ($tagihan_bebas > 0) {
                                                        if ($dibayar_bebas >= $tagihan_bebas) {
                                                            $display_value = 'Lunas';
                                                            $color = '#00E640';
                                                        } else {
                                                            $display_value = number_format($tagihan_bebas - $dibayar_bebas, 0, ',', '.');
                                                            $color = 'red';
                                                        }
                                                    }
                                                    
                                                    $total_dibayar_siswa += $dibayar_bebas;
                                                    $total_tagihan_siswa += $tagihan_bebas;
                                                    
                                                    break;
                                                }
                                            }
                                        ?>
                                            <td style="text-align:center;color:<?php echo $color; ?>">
                                                <?php echo $display_value; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        
                                        <!-- TAMBAHAN: Kolom Total Dibayar dan Kekurangan -->
                                        <td style="color:green; font-weight:bold; text-align:right;">
                                            <?php echo number_format($total_dibayar_siswa, 0, ',', '.'); ?>
                                        </td>
                                        <td style="color:red; font-weight:bold; text-align:right;">
                                            <?php 
                                            $total_kekurangan = $total_tagihan_siswa - $total_dibayar_siswa;
                                            echo number_format($total_kekurangan, 0, ',', '.'); 
                                            ?>
                                        </td>
                                    </tr>
                                <?php 
                                    $grand_total_dibayar += $total_dibayar_siswa;
                                    $grand_total_kekurangan += $total_kekurangan;
                                endforeach; ?>
                                
                                <!-- TAMBAHAN: Baris Grand Total -->
                                <tr style="background-color: #f9f9f9; font-weight: bold;">
                                    <td colspan="<?php echo 3 + count($sortedMonths) + count($bebas); ?>" style="text-align: right;">Grand Total:</td>
                                    <td style="color:green; text-align:right;"><?php echo number_format($grand_total_dibayar, 0, ',', '.'); ?></td>
                                    <td style="color:red; text-align:right;"><?php echo number_format($grand_total_kekurangan, 0, ',', '.'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <?php if (!isset($q['p']) || empty($q['p'])) : ?>
                            Silakan pilih <strong>Tahun Pelajaran</strong> terlebih dahulu untuk menampilkan data rekapitulasi.
                        <?php else : ?>
                            Tidak ada data yang ditemukan untuk filter yang dipilih.
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>

<style>
@media print {
    .content-header, .box-header, .alert, .btn {
        display: none !important;
    }
    .box-success {
        border: none !important;
        box-shadow: none !important;
    }
    table {
        width: 100% !important;
        font-size: 12px !important;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Pastikan jQuery sudah dimuat
    if (typeof jQuery == 'undefined') {
        // Jika jQuery belum dimuat, muat secara manual
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        document.head.appendChild(script);
        
        // Tunggu jQuery dimuat kemudian jalankan fungsi
        script.onload = function() {
            initializeFilter();
        };
    } else {
        initializeFilter();
    }
    
    function initializeFilter() {
        var majorsSelect = $('#majors-select');
        var classSelect = $('#class-select');
        
        // Fungsi untuk sinkronisasi dropdown
        function syncDropdowns() {
            // Jika kelas dipilih, nonaktifkan sekolah
            if (classSelect.val() && classSelect.val() !== '')