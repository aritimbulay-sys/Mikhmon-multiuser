<?php
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  $me = $_SESSION["mikhmon"];
  $pmsg = "";
  $pok = false;
  if (isset($_POST['change_pass'])) {
    $curp = $_POST['cur_pass'];
    $newp = $_POST['new_pass'];
    $confp = $_POST['conf_pass'];
    if (!um_auth($me, $curp)) {
      $pmsg = "Password lama salah.";
    } elseif (strlen($newp) < 6) {
      $pmsg = "Password baru minimal 6 karakter.";
    } elseif ($newp !== $confp) {
      $pmsg = "Konfirmasi password tidak sama.";
    } elseif ($newp === $curp) {
      $pmsg = "Password baru harus berbeda dari password lama.";
    } elseif (um_change_password($me, $newp)) {
      $pok = true;
      $pmsg = "Password berhasil diganti.";
    } else {
      $pmsg = "Gagal mengganti password.";
    }
  }
}
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-key"></i> Ganti Password</h3>
      </div>
      <div class="card-body">
        <p class="muted" style="margin-top:0;">Login sebagai <b><?= htmlspecialchars($me); ?></b></p>
        <?php if ($pmsg != "") { ?>
          <div class="box-bordered" style="padding:10px 14px;margin-bottom:14px;border-left:3px solid <?= $pok ? '#22c55e' : '#ef4444'; ?> !important;color:<?= $pok ? '#86efac' : '#fca5a5'; ?>;">
            <?= htmlspecialchars($pmsg); ?>
          </div>
        <?php } ?>
        <form method="post" action="" autocomplete="off" style="max-width:420px;">
          <div style="margin-bottom:12px;">
            <label>Password lama</label>
            <input class="form-control" type="password" name="cur_pass" required>
          </div>
          <div style="margin-bottom:12px;">
            <label>Password baru</label>
            <input class="form-control" type="password" name="new_pass" required>
          </div>
          <div style="margin-bottom:12px;">
            <label>Ulangi password baru</label>
            <input class="form-control" type="password" name="conf_pass" required>
          </div>
          <button class="btn bg-blue" type="submit" name="change_pass" value="1"><i class="fa fa-save"></i> Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
