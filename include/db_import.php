<?php
require_once __DIR__ . '/db_init.php';
require_once __DIR__ . '/users_manager.php';

function db_import_routers() {
    $db = db_init();
    $_SERVER['REQUEST_URI'] = '/x';
    require __DIR__ . '/config.php';

    $count = 0;
    foreach ($data as $session => $vals) {
        if ($session === 'mikhmon') {
            continue;
        }
        $iphost = explode('!', $vals[1])[1];
        $userhost = explode('@|@', $vals[2])[1];
        $passwdhost = explode('#|#', $vals[3])[1];
        $hotspotname = explode('%', $vals[4])[1];
        $dnsname = explode('^', $vals[5])[1];
        $currency = explode('&', $vals[6])[1];
        $areload = explode('*', $vals[7])[1];
        $iface = explode('(', $vals[8])[1];
        $infolp = explode(')', $vals[9])[1];
        $idleto = explode('=', $vals[10])[1];
        $sesname = explode('+', $vals[10])[1];
        $livereport = explode('@!@', $vals[11])[1];

        $existing = $db->findOne('routers', array('session_name' => $session));
        $row = array(
            'session_name' => $session,
            'display_name' => $hotspotname,
            'ip_host' => $iphost,
            'api_port' => 8728,
            'use_tls' => false,
            'use_rest' => false,
            'api_username' => $userhost,
            'api_password_enc' => $passwdhost,
            'currency' => $currency,
            'dns_name' => $dnsname,
            'interface' => $iface,
            'status' => 'unknown',
            'last_seen_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($existing) {
            $db->update('routers', $existing['id'], $row);
        } else {
            $db->insert('routers', $row);
        }
        $count++;
    }
    return $count;
}

function db_import_users() {
    $db = db_init();
    $file = __DIR__ . '/users.json';
    if (!file_exists($file)) {
        return 0;
    }
    $users = json_decode(file_get_contents($file), true);
    if (!is_array($users)) {
        return 0;
    }
    $count = 0;
    foreach ($users as $username => $info) {
        $existing = $db->findOne('users', array('username' => $username));
        $role = isset($info['role']) ? $info['role'] : 'user';
        $password = isset($info['password']) ? $info['password'] : '';
        $row = array(
            'username' => $username,
            'password_hash' => $password,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($existing) {
            $db->update('users', $existing['id'], array('role' => $role));
        } else {
            $db->insert('users', $row);
            $count++;
        }
        $u = $db->findOne('users', array('username' => $username));
        $routers = isset($info['routers']) && is_array($info['routers']) ? $info['routers'] : array();
        foreach ($routers as $ses) {
            $r = $db->findOne('routers', array('session_name' => $ses));
            if ($r) {
                $access = $db->findOne('user_router_access', array('user_id' => $u['id'], 'router_id' => $r['id']));
                if (!$access) {
                    $db->insert('user_router_access', array(
                        'user_id' => $u['id'],
                        'router_id' => $r['id'],
                    ));
                }
            }
        }
    }
    return $count;
}

function db_router_client($router) {
    require_once __DIR__ . '/../lib/routeros_api.class.php';
    $API = new RouterosAPI();
    $API->debug = false;
    $pass = $router['api_password_enc'];
    if (function_exists('decrypt')) {
        $try = decrypt($pass);
        if ($try !== '' && $try !== $pass) {
            $pass = $try;
        }
    }
    if (!$API->connect($router['ip_host'], $router['api_username'], $pass)) {
        return null;
    }
    return $API;
}

function db_import_hotspot_users($router_id) {
    $db = db_init();
    $router = $db->findOne('routers', array('id' => $router_id));
    if (!$router) {
        return array('ok' => false, 'error' => 'Router not found', 'imported' => 0);
    }
    $API = db_router_client($router);
    if (!$API) {
        return array('ok' => false, 'error' => 'Cannot connect to router', 'imported' => 0);
    }
    $users = $API->comm('/ip/hotspot/user/print');
    $API->disconnect();

    $imported = 0;
    $skipped = 0;
    if (!is_array($users)) {
        return array('ok' => true, 'imported' => 0, 'skipped' => 0);
    }
    foreach ($users as $u) {
        $username = isset($u['name']) ? $u['name'] : '';
        if ($username === '') {
            continue;
        }
        $profileName = isset($u['profile']) ? $u['profile'] : '';
        $profile = null;
        if ($profileName !== '') {
            $profile = $db->findOne('voucher_profiles', array('router_id' => $router['id'], 'name' => $profileName));
            if (!$profile) {
                $pid = $db->insert('voucher_profiles', array(
                    'router_id' => $router['id'],
                    'name' => $profileName,
                    'price' => 0,
                    'validity' => '',
                    'rate_limit' => isset($u['limit-uptime']) ? $u['limit-uptime'] : '',
                    'shared_users' => 1,
                    'exp_mode' => '',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                $profile = $db->findOne('voucher_profiles', array('id' => $pid));
            }
        }
        $existing = $db->findOne('vouchers', array('router_id' => $router['id'], 'username' => $username));
        if ($existing) {
            $skipped++;
            continue;
        }
        $db->insert('vouchers', array(
            'router_id' => $router['id'],
            'profile_id' => $profile ? $profile['id'] : null,
            'username' => $username,
            'password' => isset($u['password']) ? $u['password'] : $username,
            'voucher_code' => '',
            'status' => 'imported',
            'price_snapshot' => 0,
            'created_by' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'activated_at' => null,
            'expired_at' => null,
            'router_user_id' => isset($u['.id']) ? $u['.id'] : null,
            'last_sync_at' => date('Y-m-d H:i:s'),
            'sync_error' => null,
            'comment' => isset($u['comment']) ? $u['comment'] : '',
            'notes' => 'Imported from router hotspot users',
        ));
        $imported++;
    }
    return array('ok' => true, 'imported' => $imported, 'skipped' => $skipped);
}

function db_import_sales_scripts($router_id) {
    $db = db_init();
    $router = $db->findOne('routers', array('id' => $router_id));
    if (!$router) {
        return array('ok' => false, 'error' => 'Router not found', 'imported' => 0);
    }
    $API = db_router_client($router);
    if (!$API) {
        return array('ok' => false, 'error' => 'Cannot connect to router', 'imported' => 0);
    }
    $scripts = $API->comm('/system/script/print', array('?comment' => 'mikhmon'));
    $API->disconnect();

    $imported = 0;
    $skipped = 0;
    if (!is_array($scripts)) {
        return array('ok' => true, 'imported' => 0, 'skipped' => 0);
    }
    foreach ($scripts as $s) {
        $name = isset($s['name']) ? $s['name'] : '';
        $parts = explode('-|-', $name);
        if (count($parts) < 9) {
            continue;
        }
        $date = trim($parts[0]);
        $time = trim($parts[1]);
        $username = trim($parts[2]);
        $price = (int)trim($parts[3]);
        $address = trim($parts[4]);
        $mac = trim($parts[5]);
        $validity = trim($parts[6]);
        $profileName = trim($parts[7]);
        $comment = trim($parts[8]);

        if ($username === '') {
            continue;
        }

        $profile = null;
        if ($profileName !== '') {
            $profile = $db->findOne('voucher_profiles', array('router_id' => $router['id'], 'name' => $profileName));
            if (!$profile) {
                $pid = $db->insert('voucher_profiles', array(
                    'router_id' => $router['id'],
                    'name' => $profileName,
                    'price' => $price,
                    'validity' => $validity,
                    'rate_limit' => '',
                    'shared_users' => 1,
                    'exp_mode' => '',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                $profile = $db->findOne('voucher_profiles', array('id' => $pid));
            }
        }

        $existingVoucher = $db->findOne('vouchers', array('router_id' => $router['id'], 'username' => $username));
        if ($existingVoucher) {
            $voucherId = $existingVoucher['id'];
            $skipped++;
        } else {
            $voucherId = $db->insert('vouchers', array(
                'router_id' => $router['id'],
                'profile_id' => $profile ? $profile['id'] : null,
                'username' => $username,
                'password' => $username,
                'voucher_code' => '',
                'status' => 'imported_sale',
                'price_snapshot' => $price,
                'created_by' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'activated_at' => null,
                'expired_at' => null,
                'router_user_id' => null,
                'last_sync_at' => date('Y-m-d H:i:s'),
                'sync_error' => null,
                'comment' => $comment,
                'notes' => 'Imported from router sales script: ' . $name,
            ));
            $imported++;
        }

        $existingSale = $db->findOne('sales', array('voucher_id' => $voucherId));
        if (!$existingSale) {
            $db->insert('sales', array(
                'voucher_id' => $voucherId,
                'router_id' => $router['id'],
                'seller_user_id' => null,
                'amount' => $price,
                'currency' => isset($router['currency']) ? $router['currency'] : '',
                'sold_at' => $date . ' ' . $time,
                'source' => 'router_import',
                'status' => 'confirmed',
                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ));
        }
    }
    return array('ok' => true, 'imported' => $imported, 'skipped' => $skipped, 'total_scripts' => count($scripts));
}
?>