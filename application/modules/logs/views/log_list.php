<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			<?php echo isset($title) ? '' . $title : null; ?>
			<small>List</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="<?php echo site_url('manage') ?>"><i class="fa fa-th"></i> Beranda</a></li>
			<li class="active"><?php echo isset($title) ? '' . $title : null; ?></li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box box-success">
					<div class="box-header ">
						<a href="<?php echo site_url('manage') ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> HOME</a>
						<div class="box-tools">
							<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'method' => 'get')) ?>
							<div class="input-group input-group-sm" style="width: 400px; margin-top:5px;">
								<input type="text" id="field" autofocus name="n" <?php echo (isset($f['n'])) ? 'placeholder="' . $f['n'] . '"' : 'placeholder="Cari Data Siswa"' ?> class="form-control">
								<div class="input-group-btn">
									<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
								</div>
							</div>
							<?php echo form_close(); ?>
						</div>
					</div>
					<!-- /.box-header -->
					<div class="box-body table-responsive">
						<table class="table table-hover table-striped table-bordered">
							<tr>
								<th>No</th>
								<th>Tanggal</th>
								<th>Modul</th>
								<th>Aksi</th>
								<th>Keterangan</th>
								<th>Pengguna</th>
							</tr>
							<tbody>
								<?php
								if (!empty($logs)) {
									$i = 1;
									foreach ($logs as $row) :
								?>
										<tr>
											<td><?php echo $i; ?></td>
											<td><?php echo pretty_date($row['log_date'], 'd M Y h:m:s', false) ?></td>
											<td><?php echo $row['log_module']; ?></td>
											<td><?php echo $row['log_action']; ?></td>
											<td><?php echo $row['log_info']; ?></td>
											<td><?php echo $row['user_full_name']; ?></td>
										</tr>
									<?php
										$i++;
									endforeach;
								} else {
									?>
									<tr id="row">
										<td colspan="6" align="center">Data Kosong</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<!-- /.box-body -->
				</div>
				<div>
					<?php echo $this->pagination->create_links(); ?>
				</div>
				<!-- /.box -->
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>