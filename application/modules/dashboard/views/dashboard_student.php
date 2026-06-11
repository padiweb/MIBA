<div class="content-wrapper">
  <section class="content-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-success">
          <div class="box-body bg-success">
            <div class="col-md-3 col-sm-6 col-xs-12" style="margin-top: 10px;">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text dash-text">Sisa Tagihan Bulanan</span>
                  <span class="info-box-number"><?php echo 'Rp. ' . number_format($total_bulan, 0, ',', '.') ?></span>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12" style="margin-top: 10px;">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text dash-text">Sisa Tagihan Lainnya</span>
                  <span class="info-box-number"><?php echo 'Rp. ' . number_format($total_bebas - $total_bebas_pay, 0, ',', '.') ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tampilan tabel tagihan pembayaran -->
    <div class="row">
      <div class="col-md-6">
        <div class="box box-info box-solid" style="border: 1px solid #5568f1 !important;">
          <div class="box-header backg with-border">
            <h3 class="box-title">IDENTITAS SISWA</h3>
          </div>
          <div class="box-body">
            <table class="table table-striped">
              <tbody>
                <?php if (!empty($siswa)) : ?>
                  <?php foreach ($siswa as $row) : ?>
                    <tr>
                      <td width="200">Tahun Pelajaran</td>
                      <td width="4">:</td>
                      <td>
                        <?php 
                        $current_period = !empty($period) ? $period[0] : null;
                        echo $current_period ? $current_period['period_start'] . '/' . $current_period['period_end'] : '-';
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td>NIM</td>
                      <td>:</td>
                      <td><?= $row['student_nis'] ?? '-' ?></td>
                    </tr>
                    <tr>
                      <td>Nama</td>
                      <td>:</td>
                      <td><?= $row['student_full_name'] ?? '-' ?></td>
                    </tr>
                    <tr>
                      <td>Nama Ibu Kandung</td>
                      <td>:</td>
                      <td><?= $row['student_name_of_mother'] ?? '-' ?></td>
                    </tr>
                    <tr>
                      <td>Kelas</td>
                      <td>:</td>
                      <td><?= $row['class_name'] ?? '-' ?></td>
                    </tr>
                    <?php if (majors() == 'senior') : ?>
                      <tr>
                        <td>Unit Sekolah</td>
                        <td>:</td>
                        <td><?= $row['majors_name'] ?? '-' ?></td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="3">Data siswa tidak ditemukan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
          
        <!-- List Tagihan Bulanan -->
<div class="box box-info box-solid" style="border: 1px solid #5568f1 !important;">
  <div class="box-header backg with-border">
    <h3 class="box-title">TAGIHAN BULANAN</h3>
  </div>
  <div class="box-body table-responsive">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>No.</th>
          <th>Bulan</th>
          <th>Tahun</th>
          <th>Total Tagihan</th>
          <th>Sudah Dibayar</th>
          <th>Sisa Tagihan</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($bulan)) : ?>
          <?php $i = 1; ?>
          <?php foreach ($bulan as $row) : ?>
            <?php
            $total = $row['bulan_bill'] ?? 0; // Total tagihan
            $pay = $row['bulan_total_pay'] ?? 0; // Total yang sudah dibayar
            $sisa = $total - $pay; // Sisa tagihan
            $status = ($total == $pay) ? 'Lunas' : 'Belum Lunas'; // Status pembayaran
            $mont = ($row['month_month_id'] <= 6) ? $row['period_start'] : $row['period_end']; // Tahun berdasarkan bulan
            ?>
            <tr style="color:<?= ($status == 'Lunas') ? '#00E640' : 'red' ?>">
              <td><?= $i ?></td>
              <td><?= $row['month_name'] ?></td>
              <td><?= $mont ?></td>
              <td><?= 'Rp. ' . number_format($total, 0, ',', '.') ?></td>
              <td><?= 'Rp. ' . number_format($pay, 0, ',', '.') ?></td>
              <td><?= 'Rp. ' . number_format($sisa, 0, ',', '.') ?></td>
              <td>
                <label class="label <?= ($status == 'Lunas') ? 'label-success' : 'label-warning' ?>">
                  <?= $status ?>
                </label>
              </td>
            </tr>
            <?php $i++; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="7" class="text-center">Tidak ada tagihan bulanan.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

        <!-- List Tagihan Bebas -->
        <div class="box box-info box-solid" style="border: 1px solid #2ABB9B !important;">
          <div class="box-header backg with-border">
            <h3 class="box-title">TAGIHAN LAIN</h3>
          </div>
          <div class="box-body table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Jenis Pembayaran</th>
                  <th>Total Tagihan</th>
                  <th>Dibayar</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($bebas)) : ?>
                  <?php $i = 1; ?>
                  <?php foreach ($bebas as $row) : ?>
                    <?php
                    $sisa = $row['bebas_bill'] - $row['bebas_total_pay'];
                    $namePay = $row['pos_name'] . ' - T.A ' . $row['period_start'] . '/' . $row['period_end'];
                    ?>
                    <tr style="color:<?= ($row['bebas_bill'] == $row['bebas_total_pay']) ? '#00E640' : 'red' ?>">
                      <td><?= $i ?></td>
                      <td><?= $namePay ?></td>
                      <td><?= 'Rp. ' . number_format($sisa, 0, ',', '.') ?></td>
                      <td><?= 'Rp. ' . number_format($row['bebas_total_pay'], 0, ',', '.') ?></td>
                      <td>
                        <label class="label <?= ($row['bebas_bill'] == $row['bebas_total_pay']) ? 'label-success' : 'label-warning' ?>">
                          <?= ($row['bebas_bill'] == $row['bebas_total_pay']) ? 'Lunas' : 'Belum Lunas' ?>
                        </label>
                      </td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="5" class="text-center">Tidak ada tagihan lain.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Kalender</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div id="calendar"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>