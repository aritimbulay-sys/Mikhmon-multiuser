<?php
/*
 *  Simple user management page.
 */
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}
include_once(__DIR__ . "/users_manager.php");
um_ensure_session_user();
if (um_role($_SESSION["mikhmon"]) !== "admin") {
    echo "<script>window.location='./admin.php?id=sessions'</script>";
    exit;
}

$available_routers = array();
foreach (file(__DIR__ . '/config.php') as $line) {
    $parts = explode("'", $line);
    $rname = isset($parts[1]) ? $parts[1] : "";
    if ($rname == "" || $rname == "mikhmon" || strpos($rname, "new-") === 0) {
        continue;
    }
    $available_routers[$rname] = $rname;
}
$available_routers = array_values($available_routers);

if (isset($_POST['add_user'])) {
    $nu = trim($_POST['new_user']);
    $np = $_POST['new_pass'];
    $nr = $_POST['new_role'] === "admin" ? "admin" : "user";
    $selected = isset($_POST['new_routers']) && is_array($_POST['new_routers']) ? $_POST['new_routers'] : array();
    $selected = array_values(array_intersect($selected, $available_routers));
    if ($nu !== "" && $np !== "") {
        if ($nr === "user" && count($selected) == 0) {
            echo "<script>alert('Pilih minimal satu router untuk user')</script>";
        } elseif (um_add_user($nu, $np, $nr)) {
            wl_log('tambah_user', $nu . ' (' . $nr . ')');
            if ($nr === "user") {
                foreach ($selected as $sr) {
                    um_add_router($nu, $sr);
                }
            }
            echo "<script>alert('User added'); window.location='./admin.php?id=users'</script>";
            exit;
        } else {
            echo "<script>alert('User already exists or invalid')</script>";
        }
    }
}

if (isset($_POST['save_user'])) {
    $eu = $_POST['edit_user'];
    $ep = $_POST['edit_pass'];
    $er = $_POST['edit_role'] === "admin" ? "admin" : "user";
    $esel = isset($_POST['edit_routers']) && is_array($_POST['edit_routers']) ? $_POST['edit_routers'] : array();
    $esel = array_values(array_intersect($esel, $available_routers));
    $eprot = ($eu === 'admin' || $eu === 'mikhmon' || (isset($useradm) && $eu === $useradm));
    if ($eprot) {
        $er = "admin";
    }
    if (um_get($eu) !== null) {
        if ($er === "user" && count($esel) == 0) {
            echo "<script>alert('Pilih minimal satu router untuk user')</script>";
        } else {
            if ($ep !== "") {
                um_change_password($eu, $ep);
            }
            if (!$eprot) {
                um_set_role($eu, $er);
            }
            if ($er === "user") {
                um_set_routers($eu, $esel);
            }
            wl_log('ubah_user', $eu . ' role=' . $er . ' routers=' . implode(',', $esel) . ($ep !== '' ? ' +password' : ''));
            echo "<script>alert('User updated'); window.location='./admin.php?id=users'</script>";
            exit;
        }
    }
}

if (isset($_GET['del_user'])) {
    $du = $_GET['del_user'];
    if (um_delete_user($du)) {
        wl_log('hapus_user', $du);
        echo "<script>window.location='./admin.php?id=users'</script>";
        exit;
    }
}
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-users"></i> User Management</h3>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['edit_user']) && um_get($_GET['edit_user']) !== null) {
          $eu = $_GET['edit_user'];
          $eudata = um_get($eu);
          $eprot = ($eu === 'admin' || $eu === 'mikhmon' || (isset($useradm) && $eu === $useradm));
          $eurouters = isset($eudata['routers']) && is_array($eudata['routers']) ? $eudata['routers'] : array();
        ?>
        <div class="card" style="margin-bottom:16px;">
          <div class="card-header"><h3 class="card-title">Edit User: <?= htmlspecialchars($eu); ?></h3></div>
          <div class="card-body">
            <form method="post" action="">
              <input type="hidden" name="edit_user" value="<?= htmlspecialchars($eu); ?>">
              <table class="table table-sm">
                <tr><td>Username</td><td><input class="form-control" type="text" value="<?= htmlspecialchars($eu); ?>" disabled></td></tr>
                <tr><td>Password baru</td><td><input class="form-control" type="text" name="edit_pass" placeholder="Kosongkan jika tidak diubah"></td></tr>
                <tr><td>Role</td><td>
                  <?php if ($eprot) { ?>
                    <input class="form-control" type="text" value="Admin (tidak dapat diubah)" disabled>
                    <input type="hidden" name="edit_role" value="admin">
                  <?php } else { ?>
                    <select class="form-control" name="edit_role">
                      <option value="user" <?= $eudata['role'] !== 'admin' ? 'selected' : ''; ?>>User</option>
                      <option value="admin" <?= $eudata['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                  <?php } ?>
                </td></tr>
                <tr><td>Router</td><td>
                  <div style="max-height:170px;overflow:auto;border:1px solid #1f2d4d;border-radius:7px;padding:4px 10px;">
                    <?php if (count($available_routers) == 0) { ?>
                      <div class="muted">Belum ada router</div>
                    <?php } ?>
                    <?php foreach ($available_routers as $ar) { ?>
                      <label style="display:flex;align-items:center;gap:8px;margin:6px 0;cursor:pointer;">
                        <input type="checkbox" name="edit_routers[]" value="<?= htmlspecialchars($ar); ?>" <?= in_array($ar, $eurouters) ? 'checked' : ''; ?>> <?= htmlspecialchars($ar); ?>
                      </label>
                    <?php } ?>
                  </div>
                  <div class="muted" style="font-size:12px;margin-top:4px;">Wajib dipilih untuk role User (boleh lebih dari satu).</div>
                </td></tr>
                <tr><td colspan="2">
                  <button class="btn btn-primary" type="submit" name="save_user" value="1">Simpan</button>
                  <a href="./admin.php?id=users" style="margin-left:10px;">Batal</a>
                </td></tr>
              </table>
            </form>
          </div>
        </div>
        <?php } ?>
        <div class="row">
          <div class="col-6">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Add User</h3></div>
              <div class="card-body">
                <form method="post" action="">
                  <table class="table table-sm">
                    <tr><td>Username</td><td><input class="form-control" type="text" name="new_user" required></td></tr>
                    <tr><td>Password</td><td><input class="form-control" type="text" name="new_pass" required></td></tr>
                    <tr><td>Role</td><td>
                      <select class="form-control" name="new_role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                      </select>
                    </td></tr>
                    <tr><td>Router</td><td>
                      <div style="max-height:170px;overflow:auto;border:1px solid #1f2d4d;border-radius:7px;padding:4px 10px;">
                        <?php if (count($available_routers) == 0) { ?>
                          <div class="muted">Belum ada router</div>
                        <?php } ?>
                        <?php foreach ($available_routers as $ar) { ?>
                          <label style="display:flex;align-items:center;gap:8px;margin:6px 0;cursor:pointer;">
                            <input type="checkbox" name="new_routers[]" value="<?= htmlspecialchars($ar); ?>"> <?= htmlspecialchars($ar); ?>
                          </label>
                        <?php } ?>
                      </div>
                      <div class="muted" style="font-size:12px;margin-top:4px;">Wajib dipilih untuk role User (boleh lebih dari satu). Diabaikan untuk Admin.</div>
                    </td></tr>
                    <tr><td colspan="2"><button class="btn btn-primary" type="submit" name="add_user">Add</button></td></tr>
                  </table>
                </form>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Users</h3></div>
              <div class="card-body">
                <table class="table table-sm">
                  <tr><th>Username</th><th>Role</th><th>Routers</th><th>Action</th></tr>
                  <?php foreach (um_load() as $uname => $udata) { ?>
                    <tr>
                      <td><?= htmlspecialchars($uname); ?></td>
                      <td><?= htmlspecialchars($udata['role']); ?></td>
                      <td><?= htmlspecialchars(implode(', ', $udata['routers'])); ?></td>
                      <td>
                        <a class="btn bg-blue" href="./admin.php?id=users&edit_user=<?= urlencode($uname); ?>" title="Edit"><i class="fa fa-edit"></i></a>
                        <?php if ($uname !== 'admin' && $uname !== $_SESSION['mikhmon']) { ?>
                          <a class="btn bg-danger" href="./admin.php?id=users&del_user=<?= urlencode($uname); ?>" onclick="return confirm('Delete user?')" title="Delete"><i class="fa fa-trash"></i></a>
                        <?php } ?>
                      </td>
                    </tr>
                  <?php } ?>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
