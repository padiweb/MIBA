<aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu" data-widget="tree">
      <li class="<?php echo ($this->uri->segment(2) == 'dashboard' or $this->uri->segment(2) == NULL) ? 'active' : '' ?>">
        <a href="<?php echo site_url('student'); ?>">
          <i class="fa fa-th"></i> <span>DASHBOARD</span>
          <span class="pull-right-container"></span>
        </a>
      </li>
      <li class="<?php echo ($this->uri->segment(2) == 'profile') ? 'active' : '' ?>">
        <a href="<?php echo site_url('student/profile'); ?>">
          <i class="fa fa-user"></i> <span>PROFIL</span>
          <span class="pull-right-container"></span>
        </a>
      </li>
      <li>
        <a href="<?php echo site_url('student/auth/logout?location=' . htmlspecialchars($_SERVER['REQUEST_URI'])) ?>">
          <i class="fa fa-sign-out"></i> <span>KELUAR</span>
          <span class="pull-right-container"></span>
        </a>
      </li>
    </ul>
  </section>
</aside>