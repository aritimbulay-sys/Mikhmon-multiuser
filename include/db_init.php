<?php
require_once __DIR__ . '/db.php';

function db() {
    static $instance = null;
    if ($instance === null) {
        $instance = new FileDb(__DIR__ . '/dbdata');
    }
    return $instance;
}

function db_init() {
    $db = db();
    $tables = array(
        'users', 'routers', 'user_router_access', 'voucher_profiles',
        'vouchers', 'sales', 'sync_jobs', 'router_users_cache'
    );
    foreach ($tables as $t) {
        $db->ensureTable($t);
    }
    return $db;
}
?>