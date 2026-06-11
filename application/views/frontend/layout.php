<!DOCTYPE html>
<html>

<head>
	<title>MIBA | Data Transaksi Siswa</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $this->config->item('app_name') ?> <?php echo isset($title) ? ' | ' . $title : null; ?></title>
	<link rel="icon" type="image/png" href="<?php echo media_url('img/logo.png') ?>">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/AdminLTE.min.css">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/style.css">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/frontend-style.css">
	<link rel="stylesheet" href="<?php echo media_url() ?>/css/load-font-googleapis.css">

	<script src="<?php echo media_url() ?>/js/jquery.min.js"></script>


</head>

<body>

	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="col">
			<div class="">
				<div class="navbar-header">
					<ul class="nav navbar-nav navbar-left">
						<a href="manage/" class="navbar-brand"><span class='glyphicon glyphicon-stats' aria-hidden='true'></span> &nbsp;<b> <?php echo $this->config->item('app_name') ?></b></a>
					</ul>
				</div>
			</div>
		</div>
	</nav>
	<br><br><br>
	<?php $this->load->view('frontend/home') ?> <?php $this->load->view('frontend/footer') ?>
	<script src="<?php echo media_url() ?>/js/bootstrap.min.js">
	</script>
</body>

</html>