<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo isset($title) ? '' . $title : null; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('manage') ?>"><i class="fa fa-th"></i> Home</a></li>
            <li class="active"><?php echo isset($title) ? '' . $title : null; ?></li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Transaksi Bulanan</h3>
                        <a href="<?php echo site_url('manage/payout?n=' . $payment['period_period_id'] . '&r=' . $student['student_nis']) ?>" class="btn btn-danger btn-xs pull-right"><i class="fa fa-random"></i> <b>&nbsp;KEMBALI</b></a>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover table-striped table-bordered">
                            <tbody>
                                <?php foreach ($bulan as $row) : ?>
                                    <tr>
                                        <td class="text-left"><b><?php echo $row['month_name']; ?></b></td>
                                        <input type="hidden" name="bulan_id[]" value="<?php echo $row['bulan_id'] ?>">
                                        <td class="<?php echo ($row['bulan_status'] == 1) ? 'danger' : 'success' ?> text-center">
                                            <a href="<?php echo ($row['bulan_status'] == 0) ? site_url('manage/payout/pay/' . $row['payment_payment_id'] . '/' . $row['student_student_id'] . '/' . $row['bulan_id']) : site_url('manage/payout/not_pay/' . $row['payment_payment_id'] . '/' . $row['student_student_id'] . '/' . $row['bulan_id']) ?>" onclick="return confirm('<?php echo ($row['bulan_status'] == 0) ? 'Anda Akan Melakukan Pembayaran bulan ' . $row['month_name'] . '?' : 'Anda Akan Menghapus Pembayaran bulan ' . $row['month_name'] . '?' ?>')" class="btn btn-xs btn-danger">
                                                <b><?php echo ($row['bulan_status'] == 1) ? '(' . pretty_date($row['bulan_date_pay'], 'd/m/y', false) . ')' : number_format($row['bulan_bill'], 0, ',', '.') ?></b></a>
                                        </td>
                                        <td class="<?php echo ($row['bulan_status'] == 1) ? 'success' : 'danger' ?> text-center">
                                            <a data-toggle="modal" data-target="#addDesc<?php echo $row['bulan_id'] ?>" class="btn btn-xs btn-success"><i class="fa fa-file-text-o margin-r-5"></i><b>Tambah Keterangan</b></a>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="addDesc<?php echo $row['bulan_id'] ?>" role="dialog">
                                        <div class="modal-dialog modal-md">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Tambah Keterangan</h4>
                                                </div>
                                                <?php echo form_open('manage/payout/update_pay_desc/', array('method' => 'post')); ?>
                                                <div class="modal-body">
                                                    <input type="hidden" name="bulan_id" value="<?php echo $row['bulan_id'] ?>">
                                                    <input type="hidden" name="student_student_id" value="<?php echo $row['student_student_id'] ?>">
                                                    <input type="hidden" name="student_nis" value="<?php echo $row['student_nis'] ?>">
                                                    <input type="hidden" name="period_period_id" value="<?php echo $row['period_period_id'] ?>">
                                                    <input type="hidden" name="payment_payment_id" value="<?php echo $row['payment_payment_id'] ?>">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label>Keterangan *</label>
                                                            <input type="text" required="" name="bulan_pay_desc" value="<?php echo $row['bulan_pay_desc'] ?>" class="form-control" placeholder="Keterangan">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success"><i class="fa fa-save margin-r-5"></i><b>SIMPAN DATA</b></button>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i><b> TUTUP</b></button>
                                                </div>
                                            </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Detail Identitas</h3>
                        <?php if ($this->session->userdata('uroleid') == SUPERUSER) { ?>
                            <a href="<?php echo site_url('manage/payment/edit_payment_bulan/' . $row['payment_payment_id'] . '/' . $row['student_student_id']) ?>" class="btn btn-primary btn-xs pull-right"><i class="fa fa-edit"></i><b>&nbsp;Edit Tarif Pembayaran</b></a>
                        <?php } ?>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Jenis Pembayaran</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" value="<?php echo $payment['pos_name'] . ' - T.P ' . $payment['period_start'] . '/' . $payment['period_end'] ?>" readonly="">
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Tahun Pelajaran</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" value="<?php echo $payment['period_start'] . '/' . $payment['period_end'] ?>" readonly="">
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Tipe Pembayaran</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" value="<?php echo ($payment['payment_type'] == 'BULAN' ? 'Bulanan' : 'Bebas') ?>" readonly="">
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">NIM</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" readonly="" value="<?php echo $student['student_nis'] ?>">
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Nama</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" readonly="" value="<?php echo $student['student_full_name'] ?>">
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Kelas</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" readonly="" value="<?php echo $student['class_name'] ?>">
                            </div>
                        </div>
                        <br>
                        <?php if (majors() == 'senior') { ?>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Unit Sekolah</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" readonly="" value="<?php echo $student['majors_name'] ?>">
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
</section>
</div>