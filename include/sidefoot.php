<?php
$mk_user = isset($_SESSION['mikhmon']) ? $_SESSION['mikhmon'] : '';
$mk_isadmin = $mk_user !== '' && um_is_admin($mk_user);
?>
<div class="mk-sidefoot">
  <div class="mk-me">
    <i class="fa fa-user-circle"></i>
    <div>
      <div class="mk-me-name"><?= htmlspecialchars($mk_user); ?></div>
      <div class="mk-me-role"><?= $mk_isadmin ? 'Admin' : 'User'; ?></div>
    </div>
  </div>
  <a href="./admin.php?id=password"><i class="fa fa-key"></i> Ganti Password</a>
  <a id="logout" href="./admin.php?id=logout"><i class="fa fa-sign-out"></i> Logout</a>
  <button type="button" class="mk-theme-cycle" data-mk-cycle><i class="fa fa-moon-o"></i> <span>Gelap</span></button>
</div>
