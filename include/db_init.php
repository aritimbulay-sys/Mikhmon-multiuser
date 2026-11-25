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

function db_auto_sync() {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $dir = __DIR__ . '/dbdata';
    if (!is_dir($dir)) {
        return;
    }
    $lockFile = $dir . '/.worker.lock';
    $lastFile = $dir . '/.worker_last';
    $fp = @fopen($lockFile, 'c+');
    if (!$fp) {
        return;
    }
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        return;
    }
    $now = time();
    $last = file_exists($lastFile) ? filemtime($lastFile) : 0;
    if ($now - $last >= 30) {
        @touch($lastFile);
        flock($fp, LOCK_UN);
        fclose($fp);
        @exec('timeout 30 php /var/www/html/include/worker.php --limit=10 > /dev/null 2>&1 &');
    } else {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
