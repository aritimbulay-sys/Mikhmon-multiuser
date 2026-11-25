<?php
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
    echo "<script>window.location='./admin.php?id=login'</script>";
    exit;
}

require_once __DIR__ . '/db_init.php';
require_once __DIR__ . '/db_import.php';
require_once __DIR__ . '/users_manager.php';

$db = db_init();
$username = $_SESSION["mikhmon"];
$user = $db->findOne('users', array('username' => $username));
$isAdmin = function_exists('um_is_admin') && um_is_admin($username);

if ($isAdmin) {
    $routers = $db->all('routers');
} else {
    $access = $db->find('user_router_access', array('user_id' => $user ? $user['id'] : 0));
    $rids = array();
    foreach ($access as $a) {
        $rids[] = $a['router_id'];
    }
    $routers = array();
    foreach ($db->all('routers') as $r) {
        if (in_array($r['id'], $rids)) {
            $routers[] = $r;
        }
    }
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_voucher'])) {
    $router_id = (int)$_POST['router_id'];
    $profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
    $manual_profile = trim($_POST['manual_profile']);
    $manual_price = (int)$_POST['manual_price'];
    $manual_validity = trim($_POST['manual_validity']);
    $input_username = trim($_POST['username']);
    $input_password = trim($_POST['password']);
    $qty = (int)$_POST['qty'];
    if ($qty < 1) {
        $qty = 1;
    }
    if ($qty > 50) {
        $qty = 50;
    }

    $router = $db->findOne('routers', array('id' => $router_id));
    if (!$router) {
        $err = 'Router not found';
    } else {
        $owns = $isAdmin;
        if (!$owns && $user) {
            $acc = $db->findOne('user_router_access', array('user_id' => $user['id'], 'router_id' => $router_id));
            if ($acc) {
                $owns = true;
            }
        }
        if (!$owns) {
            $err = 'No access to this router';
        } else {
            $profile = null;
            if ($profile_id > 0) {
                $profile = $db->findOne('voucher_profiles', array('id' => $profile_id));
            }
            if (!$profile && $manual_profile !== '') {
                $pid = $db->insert('voucher_profiles', array(
                    'router_id' => $router['id'],
                    'name' => $manual_profile,
                    'price' => $manual_price,
                    'validity' => $manual_validity,
                    'rate_limit' => '',
                    'shared_users' => 1,
                    'exp_mode' => 'remc',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                $profile = $db->findOne('voucher_profiles', array('id' => $pid));
            }
            if ($profile) {
                $API = db_router_client($router);
                $profileExists = false;
                if ($API) {
                    $rprofiles = $API->comm('/ip/hotspot/user/profile/print');
                    foreach ($rprofiles as $rp) {
                        if (isset($rp['name']) && $rp['name'] === $profile['name']) {
                            $profileExists = true;
                            break;
                        }
                    }
                    $API->disconnect();
                }
                if (!$profileExists) {
                    $err = 'Profile not found on router: ' . $profile['name'];
                    $profile = null;
                }
            }
            if (!$profile) {
                $err = $err !== '' ? $err : 'Profile not found';
            } else {
                $created = 0;
                for ($i = 0; $i < $qty; $i++) {
                    if ($input_username !== '' && $qty === 1) {
                        $uname = $input_username;
                    } else {
                        $uname = 'vc' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
                    }
                    $pass = $input_password !== '' ? $input_password : $uname;
                    $existing = $db->findOne('vouchers', array('router_id' => $router['id'], 'username' => $uname));
                    if ($existing) {
                        $err = 'Username already exists: ' . $uname;
                        break;
                    }
                    $comment = 'vc-' . date('md') . '-' . $uname;
                    $voucherId = $db->insert('vouchers', array(
                        'router_id' => $router['id'],
                        'profile_id' => $profile['id'],
                        'username' => $uname,
                        'password' => $pass,
                        'voucher_code' => '',
                        'status' => 'pending_create',
                        'price_snapshot' => (int)$profile['price'],
                        'created_by' => $user ? $user['id'] : null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'activated_at' => null,
                        'expired_at' => null,
                        'router_user_id' => null,
                        'last_sync_at' => null,
                        'sync_error' => null,
                        'comment' => $comment,
                        'notes' => 'Created from web',
                    ));
                    $jobId = $db->insert('sync_jobs', array(
                        'job_type' => 'create_hotspot_user',
                        'router_id' => $router['id'],
                        'entity_type' => 'voucher',
                        'entity_id' => $voucherId,
                        'payload' => json_encode(array(
                            'voucher_id' => $voucherId,
                            'router_id' => $router['id'],
                            'username' => $uname,
                            'password' => $pass,
                            'profile_name' => $profile['name'],
                            'comment' => $comment,
                        )),
                        'status' => 'pending',
                        'priority' => 5,
                        'attempts' => 0,
                        'max_attempts' => 5,
                        'next_run_at' => date('Y-m-d H:i:s'),
                        'locked_by' => null,
                        'locked_at' => null,
                        'last_error' => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ));
                    $created++;
                }
                if ($created > 0 && $err === '') {
                    $msg = $created . ' voucher queued for creation. Run worker to sync to router.';
                }
            }
        }
    }
}

$recentVouchers = $db->find('vouchers', array());
usort($recentVouchers, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$recentVouchers = array_slice($recentVouchers, 0, 20);

$recentJobs = $db->find('sync_jobs', array());
usort($recentJobs, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$recentJobs = array_slice($recentJobs, 0, 20);
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-ticket"></i> Create Voucher (Web)</h3>
      </div>
      <div class="card-body">
        <?php if ($msg !== '') { ?>
          <div class="alert alert-success"><?= htmlspecialchars($msg); ?></div>
        <?php } ?>
        <?php if ($err !== '') { ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err); ?></div>
        <?php } ?>

        <form method="post" autocomplete="off">
          <div class="form-group">
            <label>Router</label>
            <select name="router_id" id="router_id" class="form-control" style="max-width:320px;" required>
              <?php foreach ($routers as $r) { ?>
                <option value="<?= (int)$r['id']; ?>"><?= htmlspecialchars($r['session_name'] . ' - ' . $r['display_name']); ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="form-group">
            <label>Profile (from DB)</label>
            <select name="profile_id" class="form-control" style="max-width:320px;">
              <option value="0">-- manual profile --</option>
              <?php
              foreach ($routers as $r) {
                  $profiles = $db->find('voucher_profiles', array('router_id' => $r['id']));
                  foreach ($profiles as $p) {
                      echo '<option value="' . (int)$p['id'] . '" data-router="' . (int)$r['id'] . '">' . htmlspecialchars($p['name'] . ' (Rp ' . $p['price'] . ')') . '</option>';
                  }
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Manual profile name (if not in DB)</label>
            <input type="text" name="manual_profile" class="form-control" style="max-width:320px;" placeholder="1_Jam_10.000">
          </div>
          <div class="form-group">
            <label>Manual price</label>
            <input type="number" name="manual_price" class="form-control" style="max-width:320px;" value="0">
          </div>
          <div class="form-group">
            <label>Manual validity</label>
            <input type="text" name="manual_validity" class="form-control" style="max-width:320px;" placeholder="36h">
          </div>

          <div class="form-group">
            <label>Username (single only, optional)</label>
            <input type="text" name="username" class="form-control" style="max-width:320px;" placeholder="auto-generate if empty">
          </div>
          <div class="form-group">
            <label>Password (optional)</label>
            <input type="text" name="password" class="form-control" style="max-width:320px;" placeholder="default = username">
          </div>
          <div class="form-group">
            <label>Quantity (1-50)</label>
            <input type="number" name="qty" class="form-control" style="max-width:120px;" value="1" min="1" max="50">
          </div>

          <button type="submit" name="create_voucher" class="btn btn-primary">Queue Voucher</button>
        </form>

        <p class="text-muted" style="font-size:12px;">
          Voucher disimpan di database web dengan status pending_create, lalu dikirim ke router oleh worker.
          Jalankan worker: <code>php /var/www/html/include/worker.php --limit=20</code>
        </p>

        <h4>Recent Vouchers</h4>
        <div class="box-bordered" style="max-height:40vh;overflow:auto;">
        <table class="table table-bordered table-hover text-nowrap">
          <thead><tr><th>ID</th><th>Router</th><th>Username</th><th>Profile</th><th>Price</th><th>Status</th><th>Created</th></tr></thead>
          <tbody>
          <?php foreach ($recentVouchers as $v) {
            $r = $db->findOne('routers', array('id' => $v['router_id']));
            $p = $v['profile_id'] ? $db->findOne('voucher_profiles', array('id' => $v['profile_id'])) : null;
          ?>
            <tr>
              <td><?= (int)$v['id']; ?></td>
              <td><?= htmlspecialchars($r ? $r['session_name'] : '-'); ?></td>
              <td><?= htmlspecialchars($v['username']); ?></td>
              <td><?= htmlspecialchars($p ? $p['name'] : '-'); ?></td>
              <td><?= (int)$v['price_snapshot']; ?></td>
              <td><?= htmlspecialchars($v['status']); ?></td>
              <td><?= htmlspecialchars($v['created_at']); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        </div>

        <h4>Recent Jobs</h4>
        <div class="box-bordered" style="max-height:40vh;overflow:auto;">
        <table class="table table-bordered table-hover text-nowrap">
          <thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Attempts</th><th>Next Run</th><th>Error</th></tr></thead>
          <tbody>
          <?php foreach ($recentJobs as $j) { ?>
            <tr>
              <td><?= (int)$j['id']; ?></td>
              <td><?= htmlspecialchars($j['job_type']); ?></td>
              <td><?= htmlspecialchars($j['status']); ?></td>
              <td><?= (int)$j['attempts']; ?></td>
              <td><?= htmlspecialchars($j['next_run_at']); ?></td>
              <td><?= htmlspecialchars($j['last_error']); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>
?>