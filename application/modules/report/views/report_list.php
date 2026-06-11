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
						<?php echo form_open(current_url(), array('method' => 'get')) ?> <br>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group date " data-date="" data-date-format="yyyy-mm-dd">
										<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										<input class="form-control" type="text" name="ds" readonly="readonly" <?php echo (isset($q['ds'])) ? 'value="' . $q['ds'] . '"' : '' ?> placeholder="Tanggal Awal" required>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group date " data-date="" data-date-format="yyyy-mm-dd">
										<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										<input class="form-control" type="text" name="de" readonly="readonly" <?php echo (isset($q['de'])) ? 'value="' . $q['de'] . '"' : '' ?> placeholder="Tanggal Akhir" required>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter Data</button>
								<?php if (isset($q['ds']) && isset($q['de'])) { ?>
									<a class="btn btn-success" href="<?php echo site_url('manage/report/report' . '/?' . http_build_query($q)) ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
								<?php } ?>
							</div>
						</div>
						<?php echo form_close(); ?>
					</div>
					
					<!-- PERBAIKAN: Menambahkan kondisi untuk menampilkan data hanya setelah filter -->
					<?php if (isset($q['ds']) && isset($q['de'])): ?>
					<div class="box-body">
						<div class="alert alert-info">
							Menampilkan data dari tanggal <strong><?php echo date('d F Y', strtotime($q['ds'])); ?></strong> 
							sampai <strong><?php echo date('d F Y', strtotime($q['de'])); ?></strong>
						</div>
						
						<div class="table-responsive">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>No</th>
										<th>Jenis Pembayaran</th>
										<th>Nama Siswa</th>
										<th>Kelas</th>
										<th>Tanggal</th>
										<th>Jumlah</th>
										<th>Keterangan</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									$no = 1;
									$total_penerimaan = 0;
									$total_pengeluaran = 0;
									
									// Data pembayaran bulanan
									if (!empty($bulan)) {
										foreach ($bulan as $row) {
											$total_penerimaan += $row['bulan_bill'];
											echo '<tr>
												<td>' . $no++ . '</td>
												<td>' . $row['pos_name'] . ' - ' . $row['month_name'] . '</td>
												<td>' . $row['student_full_name'] . '</td>
												<td>' . $row['class_name'] . '</td>
												<td>' . date('d-m-Y', strtotime($row['bulan_date_pay'])) . '</td>
												<td style="text-align:right;">' . number_format($row['bulan_bill']) . '</td>
												<td>Pembayaran Bulanan</td>
											</tr>';
										}
									}
									
									// Data pembayaran bebas
									if (!empty($dom)) {
										foreach ($dom as $row) {
											$total_penerimaan += $row['bebas_pay_bill'];
											echo '<tr>
												<td>' . $no++ . '</td>
												<td>' . $row['pos_name'] . ' - Pembayaran Bebas</td>
												<td>' . $row['student_full_name'] . '</td>
												<td>' . $row['class_name'] . '</td>
												<td>' . date('d-m-Y', strtotime($row['bebas_pay_input_date'])) . '</td>
												<td style="text-align:right;">' . number_format($row['bebas_pay_bill']) . '</td>
												<td>' . $row['bebas_pay_desc'] . '</td>
											</tr>';
										}
									}
									
									// Data kredit
									if (!empty($kredit)) {
										foreach ($kredit as $row) {
											$total_penerimaan += $row['kredit_value'];
											echo '<tr>
												<td>' . $no++ . '</td>
												<td>Penerimaan Lainnya</td>
												<td>-</td>
												<td>-</td>
												<td>' . date('d-m-Y', strtotime($row['kredit_date'])) . '</td>
												<td style="text-align:right;">' . number_format($row['kredit_value']) . '</td>
												<td>' . $row['kredit_desc'] . '</td>
											</tr>';
										}
									}
									
									// Data debit
									if (!empty($debit)) {
										foreach ($debit as $row) {
											$total_pengeluaran += $row['debit_value'];
											echo '<tr>
												<td>' . $no++ . '</td>
												<td>Pengeluaran</td>
												<td>-</td>
												<td>-</td>
												<td>' . date('d-m-Y', strtotime($row['debit_date'])) . '</td>
												<td style="text-align:right;">' . number_format($row['debit_value']) . '</td>
												<td>' . $row['debit_desc'] . '</td>
											</tr>';
										}
									}
									
									// Tampilkan pesan jika tidak ada data
									if (empty($bulan) && empty($dom) && empty($kredit) && empty($debit)) {
										echo '<tr><td colspan="7" class="text-center">Tidak ada data transaksi pada periode yang dipilih</td></tr>';
									}
									?>
								</tbody>
								<?php if (!empty($bulan) || !empty($dom) || !empty($kredit) || !empty($debit)): ?>
								<tfoot>
									<tr>
										<th colspan="5" style="text-align:right;">Total Penerimaan:</th>
										<th style="text-align:right;"><?php echo number_format($total_penerimaan); ?></th>
										<th></th>
									</tr>
									<tr>
										<th colspan="5" style="text-align:right;">Total Pengeluaran:</th>
										<th style="text-align:right;"><?php echo number_format($total_pengeluaran); ?></th>
										<th></th>
									</tr>
									<tr>
										<th colspan="5" style="text-align:right;">Saldo:</th>
										<th style="text-align:right;"><?php echo number_format($total_penerimaan - $total_pengeluaran); ?></th>
										<th></th>
									</tr>
								</tfoot>
								<?php endif; ?>
							</table>
						</div>
					</div>
					<?php else: ?>
					<div class="box-body">
						<div class="alert alert-warning">
							<h4><i class="icon fa fa-info"></i> Informasi</h4>
							Silakan pilih periode tanggal terlebih dahulu dan klik tombol <strong>Filter Data</strong> untuk menampilkan laporan keuangan.
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>

<!-- PERBAIKAN: Menambahkan script untuk datepicker -->
<script>
$(document).ready(function() {
	// Inisialisasi datepicker
	$('.input-group.date').datepicker({
		format: "yyyy-mm-dd",
		todayBtn: "linked",
		autoclose: true,
		todayHighlight: true
	});
});
</script>