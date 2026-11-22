<?php
/*
 *  User management helpers for Mikhmon multi-user.
 */
if (substr($_SERVER["REQUEST_URI"], -16) == "users_manager.php") { header("Location:./"); };

function um_data_file() {
    return __DIR__ . '/users.json';
}

function um_load() {
    $file = um_data_file();
    if (!file_exists($file)) {
        return array();
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return array();
    }
    return $data;
}

function um_save($data) {
    $file = um_data_file();
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return true;
}

function um_get($user) {
    $data = um_load();
    if (isset($data[$user]) && is_array($data[$user])) {
        return $data[$user];
    }
    return null;
}

function um_role($user) {
    if ($user === 'admin' || $user === 'mikhmon') {
        return 'admin';
    }
    $u = um_get($user);
    if (is_array($u) && isset($u['role'])) {
        return $u['role'];
    }
    return 'user';
}

function um_is_admin($user) {
    return um_role($user) === 'admin';
}

function um_is_hash($value) {
    return strpos($value, '$2y$') === 0;
}

function um_set_password($user, $pass) {
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    $data[$user]['password'] = password_hash($pass, PASSWORD_DEFAULT);
    um_save($data);
    return true;
}

function um_change_password($user, $pass) {
    if (!um_set_password($user, $pass)) {
        return false;
    }
    global $useradm, $passadm;
    if (isset($useradm) && $user === $useradm && function_exists('encrypt') && isset($passadm)) {
        $cfg = __DIR__ . '/config.php';
        $newenc = encrypt($pass);
        $content = file_get_contents($cfg);
        $content = str_replace("mikhmon>|>" . $passadm, "mikhmon>|>" . $newenc, $content);
        file_put_contents($cfg, $content);
        $passadm = $newenc;
    }
    return true;
}

function um_auth($user, $pass) {
    $u = um_get($user);
    if (is_array($u) && isset($u['password'])) {
        $stored = $u['password'];
        if (um_is_hash($stored)) {
            if (password_verify($pass, $stored)) {
                return true;
            }
        } elseif ($stored === $pass) {
            um_set_password($user, $pass);
            return true;
        }
    }
    // legacy config admin
    global $useradm, $passadm;
    if (isset($useradm) && $user === $useradm) {
        if (function_exists('decrypt') && isset($passadm) && $pass === decrypt($passadm)) {
            return true;
        }
    }
    return false;
}

function um_add_user($user, $pass, $role = 'user') {
    $user = trim($user);
    if ($user === '' || $pass === '') {
        return false;
    }
    $data = um_load();
    if (isset($data[$user])) {
        return false;
    }
    $data[$user] = array(
        'password' => password_hash($pass, PASSWORD_DEFAULT),
        'role' => ($role === 'admin' ? 'admin' : 'user'),
        'routers' => array(),
    );
    um_save($data);
    return true;
}

function um_delete_user($user) {
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    if ($user === 'admin' || $user === 'mikhmon') {
        return false;
    }
    unset($data[$user]);
    um_save($data);
    return true;
}

function um_owns($user, $session) {
    if ($user === 'admin' || $user === 'mikhmon') {
        return true;
    }
    if (um_role($user) === 'admin') {
        return true;
    }
    $u = um_get($user);
    if (is_array($u) && isset($u['routers']) && is_array($u['routers'])) {
        return in_array($session, $u['routers']);
    }
    return false;
}

function um_add_router($user, $router) {
    if ($user === 'admin' || $user === 'mikhmon' || um_role($user) === 'admin') {
        // admins see all routers; no need to track, but track anyway for non-admin only
    }
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    if (!isset($data[$user]['routers']) || !is_array($data[$user]['routers'])) {
        $data[$user]['routers'] = array();
    }
    if (!in_array($router, $data[$user]['routers'])) {
        $data[$user]['routers'][] = $router;
    }
    um_save($data);
    return true;
}

function um_remove_router($user, $router) {
    $data = um_load();
    if (isset($data[$user]['routers']) && is_array($data[$user]['routers'])) {
        $data[$user]['routers'] = array_values(array_diff($data[$user]['routers'], array($router)));
    }
    um_save($data);
    return true;
}

function um_remove_router_all($router) {
    $data = um_load();
    foreach ($data as $u => $info) {
        if (isset($info['routers']) && is_array($info['routers'])) {
            $data[$u]['routers'] = array_values(array_diff($info['routers'], array($router)));
        }
    }
    um_save($data);
    return true;
}

function um_add_logo($user, $file) {
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    foreach ($data as $u => $info) {
        if (isset($info['logos']) && is_array($info['logos'])) {
            $data[$u]['logos'] = array_values(array_diff($info['logos'], array($file)));
        }
    }
    if (!isset($data[$user]['logos']) || !is_array($data[$user]['logos'])) {
        $data[$user]['logos'] = array();
    }
    $data[$user]['logos'][] = $file;
    um_save($data);
    return true;
}

function um_owns_logo($user, $file) {
    if (um_role($user) === 'admin') {
        return true;
    }
    $u = um_get($user);
    return is_array($u) && isset($u['logos']) && is_array($u['logos']) && in_array($file, $u['logos']);
}

function um_remove_logo_all($file) {
    $data = um_load();
    foreach ($data as $u => $info) {
        if (isset($info['logos']) && is_array($info['logos'])) {
            $data[$u]['logos'] = array_values(array_diff($info['logos'], array($file)));
        }
    }
    um_save($data);
    return true;
}

function um_set_role($user, $role) {
    if ($user === 'admin' || $user === 'mikhmon') {
        return false;
    }
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    $data[$user]['role'] = ($role === 'admin' ? 'admin' : 'user');
    um_save($data);
    return true;
}

function um_set_routers($user, $routers) {
    $data = um_load();
    if (!isset($data[$user])) {
        return false;
    }
    $data[$user]['routers'] = array_values(array_unique($routers));
    um_save($data);
    return true;
}

function um_my_routers($user) {
    $out = array();
    $cfg = __DIR__ . '/config.php';
    if (!file_exists($cfg)) {
        return $out;
    }
    foreach (file($cfg) as $line) {
        $p = explode("'", $line);
        $r = isset($p[1]) ? $p[1] : '';
        if ($r == '' || $r == 'mikhmon' || strpos($r, 'new-') === 0) {
            continue;
        }
        if (um_owns($user, $r)) {
            $out[$r] = $r;
        }
    }
    return array_values($out);
}

function um_ensure_session_user() {
    if (!isset($_SESSION['mikhmon']) || $_SESSION['mikhmon'] === '') {
        return;
    }
    $user = $_SESSION['mikhmon'];
    if (!isset($_SESSION['mikhmon_user'])) {
        $_SESSION['mikhmon_user'] = $user;
    }
    if (!isset($_SESSION['mikhmon_role'])) {
        $_SESSION['mikhmon_role'] = um_role($user);
    }
    // ensure legacy config admin exists in users.json
    global $useradm, $passadm;
    if (isset($useradm) && $user === $useradm) {
        if (um_get($user) === null) {
            $pass = (function_exists('decrypt') && isset($passadm)) ? decrypt($passadm) : '';
            um_add_user($user, $pass, 'admin');
        }
    }
}

// Backwards-compat aliases
function getUsersData() { return um_load(); }
function saveUsersData($data) { return um_save($data); }
function assignRouterToUser($username, $router) { return um_add_router($username, $router); }
function removeRouterFromUser($username, $router) { return um_remove_router($username, $router); }
function removeRouterFromAllUsers($router) { return um_remove_router_all($router); }
function createUser($username, $password, $role = 'user') { return um_add_user($username, $password, $role); }
function deleteUser($username) { return um_delete_user($username); }
function isUserAdmin($username) { return um_is_admin($username); }
