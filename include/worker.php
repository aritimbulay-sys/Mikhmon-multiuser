<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['REQUEST_URI'] = '/worker';
require_once __DIR__ . '/db_init.php';
require_once __DIR__ . '/db_import.php';

$db = db_init();

$limit = 20;
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = (int)substr($arg, 8);
    }
}
if ($limit < 1) {
    $limit = 1;
}

function claim_job($db) {
    return $db->transaction(function () use ($db) {
        $now = date('Y-m-d H:i:s');
        $jobs = $db->all('sync_jobs');
        foreach ($jobs as $job) {
            if ($job['status'] === 'pending' && $job['next_run_at'] <= $now) {
                $db->update('sync_jobs', $job['id'], array(
                    'status' => 'processing',
                    'locked_at' => $now,
                    'locked_by' => 'worker',
                ));
                return $db->findOne('sync_jobs', array('id' => $job['id']));
            }
        }
        return null;
    });
}

function find_router_user($API, $username) {
    $rows = $API->comm('/ip/hotspot/user/print', array('?name' => $username));
    if (is_array($rows) && count($rows) > 0) {
        return $rows[0];
    }
    return null;
}

function mark_success($db, $job) {
    $db->update('sync_jobs', $job['id'], array(
        'status' => 'success',
        'updated_at' => date('Y-m-d H:i:s'),
        'last_error' => null,
    ));
}

function mark_failure($db, $job, $error) {
    $attempts = (int)$job['attempts'] + 1;
    $max = (int)$job['max_attempts'];
    if ($max < 1) {
        $max = 5;
    }
    $delays = array(60, 300, 900, 3600);
    $delay = $delays[min($attempts - 1, count($delays) - 1)];
    $next = date('Y-m-d H:i:s', time() + $delay);
    $status = 'pending';
    if ($attempts >= $max) {
        $status = 'failed';
        $next = null;
    }
    $db->update('sync_jobs', $job['id'], array(
        'status' => $status,
        'attempts' => $attempts,
        'next_run_at' => $next,
        'last_error' => substr($error, 0, 1000),
        'updated_at' => date('Y-m-d H:i:s'),
    ));
}

function process_job($db, $job) {
    $payload = json_decode($job['payload'], true);
    if (!is_array($payload)) {
        throw new Exception('Invalid job payload');
    }

    if ($job['job_type'] === 'create_hotspot_user') {
        $router = $db->findOne('routers', array('id' => $payload['router_id']));
        $voucher = $db->findOne('vouchers', array('id' => $payload['voucher_id']));
        if (!$router || !$voucher) {
            throw new Exception('Router or voucher not found');
        }

        $API = db_router_client($router);
        if (!$API) {
            throw new Exception('Cannot connect to router');
        }

        try {
            $existing = find_router_user($API, $payload['username']);
            if ($existing) {
                $rid = $existing['.id'];
            } else {
                $API->comm('/ip/hotspot/user/add', array(
                    'name' => $payload['username'],
                    'password' => $payload['password'],
                    'profile' => $payload['profile_name'],
                    'comment' => $payload['comment'],
                ));
                $existing = find_router_user($API, $payload['username']);
                if (!$existing) {
                    throw new Exception('User created but not found');
                }
                $rid = $existing['.id'];
            }
        } finally {
            $API->disconnect();
        }

        $db->update('vouchers', $voucher['id'], array(
            'status' => 'active',
            'router_user_id' => $rid,
            'activated_at' => date('Y-m-d H:i:s'),
            'last_sync_at' => date('Y-m-d H:i:s'),
            'sync_error' => null,
        ));

        $existingSale = $db->findOne('sales', array('voucher_id' => $voucher['id']));
        if (!$existingSale) {
            $db->insert('sales', array(
                'voucher_id' => $voucher['id'],
                'router_id' => $router['id'],
                'seller_user_id' => $voucher['created_by'],
                'amount' => $voucher['price_snapshot'],
                'currency' => isset($router['currency']) ? $router['currency'] : '',
                'sold_at' => date('Y-m-d H:i:s'),
                'source' => 'web',
                'status' => 'confirmed',
                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ));
        }

        echo "OK create {$payload['username']} on {$router['session_name']} ({$rid})\n";
        return;
    }

    if ($job['job_type'] === 'fetch_router_users') {
        $router = $db->findOne('routers', array('id' => $payload['router_id']));
        if (!$router) {
            throw new Exception('Router not found');
        }
        $API = db_router_client($router);
        if (!$API) {
            throw new Exception('Cannot connect to router');
        }
        try {
            $users = $API->comm('/ip/hotspot/user/print');
        } finally {
            $API->disconnect();
        }
        if (!is_array($users)) {
            $users = array();
        }
        $db->transaction(function () use ($db, $router, $users) {
            foreach ($users as $u) {
                $username = isset($u['name']) ? $u['name'] : '';
                if ($username === '') {
                    continue;
                }
                $existing = $db->findOne('router_users_cache', array('router_id' => $router['id'], 'username' => $username));
                $row = array(
                    'router_id' => $router['id'],
                    'mikrotik_id' => isset($u['.id']) ? $u['.id'] : null,
                    'username' => $username,
                    'profile_name' => isset($u['profile']) ? $u['profile'] : '',
                    'comment' => isset($u['comment']) ? $u['comment'] : '',
                    'disabled' => isset($u['disabled']) && $u['disabled'] === 'true',
                    'raw_json' => json_encode($u),
                    'fetched_at' => date('Y-m-d H:i:s'),
                );
                if ($existing) {
                    $db->update('router_users_cache', $existing['id'], $row);
                } else {
                    $db->insert('router_users_cache', $row);
                }
            }
            $db->update('routers', $router['id'], array(
                'status' => 'online',
                'last_seen_at' => date('Y-m-d H:i:s'),
            ));
        });
        echo "OK fetch {$router['session_name']}: " . count($users) . " users\n";
        return;
    }

    throw new Exception('Unknown job type: ' . $job['job_type']);
}

$processed = 0;
while ($processed < $limit) {
    $job = claim_job($db);
    if (!$job) {
        break;
    }
    echo "Processing job #{$job['id']} {$job['job_type']}...\n";
    try {
        process_job($db, $job);
        mark_success($db, $job);
    } catch (Exception $e) {
        echo "FAIL job #{$job['id']}: " . $e->getMessage() . "\n";
        mark_failure($db, $job, $e->getMessage());
    }
    $processed++;
}

echo "Done. Processed: $processed\n";
?>