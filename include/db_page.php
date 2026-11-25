<?php
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"]) || !function_exists('um_is_admin') || !um_is_admin($_SESSION["mikhmon"])) {
    echo "<script>window.location='./admin.php?id=login'</script>";
    exit;
}

require_once __DIR__ . '/db_init.php';
require_once __DIR__ . '/db_import.php';
require_once __DIR__ . '/../lib/routeros_api.class.php';

$db = db_init();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['init_db'])) {
        db_init();
        $msg = 'Database file initialized.';
    } elseif (isset($_POST['import_routers'])) {
        $n = db_import_routers();
        $msg = 'Imported/updated ' . $n . ' routers.';
    } elseif (isset($_POST['import_users'])) {
        $n = db_import_users();
        $msg = 'Imported ' . $n . ' users.';
    } elseif (isset($_POST['import_hotspot'])) {
        set_time_limit(300);
        $rid = (int)$_POST['router_id'];
        $res = db_import_hotspot_users($rid);
        if ($res['ok']) {
            $msg = 'Hotspot users imported: ' . $res['imported'] . ', skipped: ' . $res['skipped'];
        } else {
            $err = $res['error'];
        }
    } elseif (isset($_POST['import_sales'])) {
        set_time_limit(600);
        $rid = (int)$_POST['router_id'];
        $res = db_import_sales_scripts($rid);
        if ($res['ok']) {
            $msg = 'Sales scripts imported: ' . $res['imported'] . ', skipped: ' . $res['skipped'] . ', total scripts: ' . $res['total_scripts'];
        } else {
            $err = $res['error'];
        }
    }
}

$routers = $db->all('routers');
$counts = array(
    'users' => $db->count('users'),
    'routers' => $db->count('routers'),
    'vouchers' => $db->count('vouchers'),
    'sales' => $db->count('sales'),
    'voucher_profiles' => $db->count('voucher_profiles'),
);
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-database"></i> Database Web (File-based)</h3>
      </div>
      <div class="card-body">
        <?php if ($msg !== '') { ?>
          <div class="alert alert-success"><?= htmlspecialchars($msg); ?></div>
        <?php } ?>
        <?php if ($err !== '') { ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err); ?></div>
        <?php } ?>

        <h4>Ringkasan</h4>
        <table class="table table-bordered">
          <tr><th>Tabel</th><th>Jumlah</th></tr>
          <?php foreach ($counts as $k => $v) { ?>
            <tr><td><?= htmlspecialchars($k); ?></td><td><?= (int)$v; ?></td></tr>
          <?php } ?>
        </table>

        <h4>Inisialisasi</h4>
        <form method="post" style="margin-bottom:15px;">
          <button type="submit" name="init_db" class="btn btn-primary">Init DB Files</button>
          <button type="submit" name="import_routers" class="btn btn-primary">Import Routers dari config.php</button>
          <button type="submit" name="import_users" class="btn btn-primary">Import Users dari users.json</button>
        </form>

        <h4>Import Data Router (tanpa mengubah router)</h4>
        <form method="post" style="margin-bottom:15px;">
          <div class="form-group">
            <label>Router</label>
            <select name="router_id" class="form-control" style="max-width:320px;">
              <?php foreach ($routers as $r) { ?>
                <option value="<?= (int)$r['id']; ?>"><?= htmlspecialchars($r['session_name'] . ' - ' . $r['display_name']); ?></option>
              <?php } ?>
            </select>
          </div>
          <button type="submit" name="import_hotspot" class="btn btn-primary">Import Hotspot Users</button>
          <button type="submit" name="import_sales" class="btn btn-warning">Import Sales Scripts</button>
        </form>

        <p class="text-muted" style="font-size:12px;">
          Import bersifat read-only ke router. Data router tidak diubah. Import sales scripts bisa memakan waktu pada router dengan banyak data.
        </p>
      </div>
    </div>
  </div>
</div>
?>