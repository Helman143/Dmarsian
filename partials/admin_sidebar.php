<?php // Usage: $active = 'dashboard'|'student'|'collection'|'payment'|'posts'|'enroll'|'trial'|'settings'|'profile'; ?>
<div class="sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebar" role="navigation" aria-label="Main Sidebar">
  <div class="logo d-flex align-items-center gap-2">
    <img src="Picture/Logo2.png" alt="D'MARSIANS Logo" class="logo-img img-fluid" style="max-width:56px;height:auto;">
    <h2 class="m-0">D'MARSIANS<br>TAEKWONDO<br>SYSTEM</h2>
  </div>
  <nav>
    <a href="admin_dashboard.php" class="<?= isset($active) && $active==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i><span>DASHBOARD</span></a>
    <a href="admin_student_management.php" class="<?= isset($active) && $active==='student'?'active':'' ?>"><i class="fas fa-user-graduate"></i><span>STUDENT MANAGEMENT</span></a>
    <a href="admin_collection.php" class="<?= isset($active) && $active==='collection'?'active':'' ?>"><i class="fas fa-money-bill-wave"></i><span>COLLECTION</span></a>
    <a href="admin_payment.php" class="<?= isset($active) && $active==='payment'?'active':'' ?>"><i class="fas fa-credit-card"></i><span>PAYMENT</span></a>
    <a href="admin_post_management.php" class="<?= isset($active) && $active==='posts'?'active':'' ?>"><i class="fas fa-bullhorn"></i><span>POST MANAGEMENT</span></a>
    <div class="dropdown">
      <a href="#" class="dropdown-toggle"><i class="fas fa-chart-bar"></i><span>ENROLLMENT REPORT</span></a>
      <div class="dropdown-content">
        <a href="admin_enrollment.php" class="<?= isset($active) && $active==='enroll'?'active':'' ?>"><i class="fas fa-user-plus"></i><span>ENROLLMENT</span></a>
        <a href="admin_trial_session.php" class="<?= isset($active) && $active==='trial'?'active':'' ?>"><i class="fas fa-users"></i><span>TRIAL SESSION</span></a>
      </div>
    </div>
    <a href="admin_settings.php" class="<?= isset($active) && $active==='settings'?'active':'' ?>"><i class="fas fa-cogs"></i><span>ADMIN SETTINGS</span></a>
    <a href="admin_profile.php" class="<?= isset($active) && $active==='profile'?'active':'' ?>"><i class="fas fa-user-circle"></i><span>PROFILE</span></a>
  </nav>
  <div class="logout-container">
    <a href="admin_logout.php" class="logout"><i class="fas fa-power-off"></i><span>Logout</span></a>
  </div>
</div>

<script>
// Ensure the Bootstrap offcanvas backdrop matches our "success" theme.
// Bootstrap creates the backdrop dynamically; we tag it so CSS can style it reliably.
(function () {
  function tagBackdrop() {
    const backdrop = document.querySelector('.offcanvas-backdrop');
    if (!backdrop) return;
    backdrop.classList.add('bg-success', 'backdrop-success');
  }

  document.addEventListener('show.bs.offcanvas', function (e) {
    if (e && e.target && e.target.id === 'sidebar') {
      // Backdrop may not exist yet on "show" so defer once.
      setTimeout(tagBackdrop, 0);
    }
  });

  document.addEventListener('shown.bs.offcanvas', function (e) {
    if (e && e.target && e.target.id === 'sidebar') tagBackdrop();
  });
})();
</script>


