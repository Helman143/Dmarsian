<?php // Usage: $active = 'dashboard'|'student'|'collection'|'payment'|'posts'|'enroll'|'trial'; ?>
<div class="sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" role="navigation" aria-label="Main Sidebar">
  <div class="logo d-flex align-items-center gap-2">
    <img src="Picture/Logo2.png" alt="D'MARSIANS Logo" class="logo-img img-fluid" style="max-width:56px;height:auto;">
    <h2 class="m-0">D'MARSIANS<br>TAEKWONDO<br>SYSTEM</h2>
  </div>
  <nav>
    <a href="dashboard.php" class="<?= isset($active) && $active==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i><span>DASHBOARD</span></a>
    <a href="student_management.php" class="<?= isset($active) && $active==='student'?'active':'' ?>"><i class="fas fa-user-graduate"></i><span>STUDENT MANAGEMENT</span></a>
    <a href="collection.php" class="<?= isset($active) && $active==='collection'?'active':'' ?>"><i class="fas fa-money-bill-wave"></i><span>COLLECTION</span></a>
    <a href="payment.php" class="<?= isset($active) && $active==='payment'?'active':'' ?>"><i class="fas fa-credit-card"></i><span>PAYMENT</span></a>
    <a href="post_management.php" class="<?= isset($active) && $active==='posts'?'active':'' ?>"><i class="fas fa-bullhorn"></i><span>POST MANAGEMENT</span></a>
    <div class="dropdown">
      <a href="#" class="dropdown-toggle"><i class="fas fa-chart-bar"></i><span>ENROLLMENT REPORT</span></a>
      <div class="dropdown-content">
        <a href="enrollment.php" class="<?= isset($active) && $active==='enroll'?'active':'' ?>"><i class="fas fa-user-plus"></i><span>ENROLLMENT</span></a>
        <a href="trial_session.php" class="<?= isset($active) && $active==='trial'?'active':'' ?>"><i class="fas fa-users"></i><span>TRIAL SESSION</span></a>
      </div>
    </div>
  </nav>
  <div class="logout-container">
    <a href="logout.php" class="logout"><i class="fas fa-power-off"></i><span>Logout</span></a>
  </div>
</div>


