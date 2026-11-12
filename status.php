<?php
/**
 * Read-only monitoring. No shell, no DB connection, no configuration changes.
 * Ubuntu/Linux /proc stats + localhost TCP connect tests only.
 * Accessible only through access.php's company-IP allowlist.
 * Compatible with PHP 7.3.
 */
require __DIR__ . '/access.php';
amnote_guard();
header('Content-Type: application/json; charset=UTF-8');

function amnote_file($path) {
    return is_readable($path) ? @file_get_contents($path) : false;
}
function amnote_meminfo() {
    $raw = amnote_file('/proc/meminfo');
    if ($raw === false) return null;
    $mem = array();
    if (preg_match_all('/^([A-Za-z_]+):\s*(\d+)\s*kB/m', $raw, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) $mem[$m[1]] = (int)$m[2] * 1024;
    }
    if (!isset($mem['MemTotal']) || !isset($mem['MemAvailable'])) return null;
    return array(
        'total' => $mem['MemTotal'],
        'used' => max(0, $mem['MemTotal'] - $mem['MemAvailable']),
        'available' => $mem['MemAvailable'],
        'swapTotal' => isset($mem['SwapTotal']) ? $mem['SwapTotal'] : 0,
        'swapFree' => isset($mem['SwapFree']) ? $mem['SwapFree'] : 0
    );
}
function amnote_cpu_counters() {
    $raw = amnote_file('/proc/stat');
    if ($raw === false || !preg_match('/^cpu\s+([^\n]+)/', $raw, $m)) return null;
    $parts = preg_split('/\s+/', trim($m[1]));
    $values = array_map('intval', $parts);
    $total = array_sum($values);
    $idle = (isset($values[3]) ? $values[3] : 0) + (isset($values[4]) ? $values[4] : 0);
    return array('total' => $total, 'idle' => $idle);
}
function amnote_network_counters() {
    $raw = amnote_file('/proc/net/dev');
    if ($raw === false) return null;
    $rx = 0; $tx = 0;
    foreach (explode("\n", $raw) as $line) {
        if (strpos($line, ':') === false) continue;
        $tokens = explode(':', $line, 2);
        $iface = trim($tokens[0]);
        if ($iface === 'lo') continue;
        $parts = preg_split('/\s+/', trim($tokens[1]));
        if (count($parts) < 9) continue;
        $rx += (float)$parts[0];
        $tx += (float)$parts[8];
    }
    return array('rx' => $rx, 'tx' => $tx);
}
function amnote_tcp_open($port) {
    $errno = 0; $errstr = '';
    $socket = @stream_socket_client('tcp://127.0.0.1:' . (int)$port, $errno, $errstr, 0.25, STREAM_CLIENT_CONNECT);
    if ($socket === false) return false;
    fclose($socket);
    return true;
}

$diskTotal = @disk_total_space('/');
$diskFree = @disk_free_space('/');
$load = function_exists('sys_getloadavg') ? sys_getloadavg() : array();
$uptimeRaw = amnote_file('/proc/uptime');
$uptime = $uptimeRaw !== false ? (float)explode(' ', trim($uptimeRaw))[0] : null;

// TCP-only checks; an open port is NOT proof that the application works.
$ports = array(
    'apache' => 80,
    'cockpit' => 9090,
    'netdata' => 19999,
    'supervisor' => 9002,
    'api-tcgp' => 8989,
    'api-queue' => 8888,
    'api-mail' => 8080,
    'api-excel' => 8383,
    'api-firmbanking' => 5555,
    'api-firmbanking5000' => 5000,
    'php73' => 9000,
    'php83' => 9001
);
$portsState = array();
foreach ($ports as $id => $port) $portsState[$id] = amnote_tcp_open($port);

$result = array(
    'checkedAt' => gmdate('c'),
    'hostname' => gethostname(),
    'uptimeSeconds' => $uptime,
    'load' => array_slice($load, 0, 3),
    'cpu' => amnote_cpu_counters(),
    'memory' => amnote_meminfo(),
    'disk' => $diskTotal !== false && $diskFree !== false
        ? array('total' => $diskTotal, 'used' => $diskTotal - $diskFree, 'free' => $diskFree)
        : null,
    'network' => amnote_network_counters(),
    'ports' => $portsState,
    'checkType' => 'local-tcp-port-only'
);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
