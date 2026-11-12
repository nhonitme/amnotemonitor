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


/** Rich, read-only server inventory. Sensitive hardware serials and MAC addresses are deliberately excluded. */
function amnote_small_text($path) {
    $raw = amnote_file($path);
    if ($raw === false) return null;
    $value = trim($raw);
    return $value !== '' ? $value : null;
}
function amnote_os_release() {
    $raw = amnote_file('/etc/os-release');
    if ($raw === false) return array('name' => null, 'version' => null);
    $vars = array();
    foreach (explode("\n", $raw) as $line) {
        if (preg_match('/^([A-Z_]+)=(.*)$/', trim($line), $m)) {
            $value = trim($m[2]);
            if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
                $value = stripcslashes(substr($value, 1, -1));
            }
            $vars[$m[1]] = $value;
        }
    }
    return array('name' => isset($vars['PRETTY_NAME']) ? $vars['PRETTY_NAME'] : null,
                 'version' => isset($vars['VERSION_ID']) ? $vars['VERSION_ID'] : null);
}
function amnote_cpu_details() {
    $raw = amnote_file('/proc/cpuinfo');
    if ($raw === false) return null;
    $model = null; $mhz = null; $coresPerSocket = null; $siblings = null; $sockets = array();
    if (preg_match('/^model name\s*:\s*(.+)$/m', $raw, $m)) $model = trim($m[1]);
    if (preg_match('/^cpu MHz\s*:\s*(.+)$/m', $raw, $m)) $mhz = (float)$m[1];
    if (preg_match('/^cpu cores\s*:\s*(\d+)$/m', $raw, $m)) $coresPerSocket = (int)$m[1];
    if (preg_match('/^siblings\s*:\s*(\d+)$/m', $raw, $m)) $siblings = (int)$m[1];
    preg_match_all('/^processor\s*:/m', $raw, $logicalMatches);
    preg_match_all('/^physical id\s*:\s*(\d+)$/m', $raw, $socketMatches);
    foreach ($socketMatches[1] as $item) $sockets[$item] = true;
    return array('model' => $model, 'logicalCpus' => count($logicalMatches[0]) ?: null,
                 'sockets' => count($sockets) ?: null, 'coresPerSocket' => $coresPerSocket,
                 'threadsPerSocket' => $siblings, 'currentMhz' => $mhz);
}
function amnote_board_details() {
    return array(
        'vendor' => amnote_small_text('/sys/class/dmi/id/board_vendor'),
        'model' => amnote_small_text('/sys/class/dmi/id/board_name'),
        'version' => amnote_small_text('/sys/class/dmi/id/board_version'),
        'machineVendor' => amnote_small_text('/sys/class/dmi/id/sys_vendor'),
        'machineModel' => amnote_small_text('/sys/class/dmi/id/product_name')
    );
}
function amnote_block_devices() {
    $entries = @glob('/sys/block/*');
    if (!is_array($entries)) return array();
    $rows = array();
    foreach ($entries as $entry) {
        $device = basename($entry);
        if (!preg_match('/^(?:sd[a-z]+|vd[a-z]+|xvd[a-z]+|hd[a-z]+|nvme\d+n\d+|md\d+|dm-\d+)$/', $device)) continue;
        $sectors = amnote_small_text($entry . '/size');
        if ($sectors === null || !ctype_digit($sectors) || (float)$sectors <= 0) continue;
        $rot = amnote_small_text($entry . '/queue/rotational');
        $type = strpos($device, 'nvme') === 0 ? 'NVMe / SSD' :
            ($rot === '0' ? 'Không quay (kernel báo)' : ($rot === '1' ? 'Đĩa quay (kernel báo)' : 'Chưa xác định'));
        if (preg_match('/^(?:vd|xvd)/', $device)) $type = 'Ổ ảo / virtio';
        if (strpos($device, 'md') === 0) $type = 'Thiết bị RAID (md)';
        if (strpos($device, 'dm-') === 0) $type = 'Thiết bị ánh xạ (DM/LVM)';
        $rows[] = array(
            'name' => $device, 'size' => (float)$sectors * 512,
            'type' => $type,
            'vendor' => amnote_small_text($entry . '/device/vendor'),
            'model' => amnote_small_text($entry . '/device/model')
        );
        if (count($rows) >= 24) break;
    }
    return $rows;
}
function amnote_mdraid() {
    $raw = amnote_file('/proc/mdstat');
    if ($raw === false) return null;
    $arrays = array();
    foreach (explode("\n", $raw) as $line) {
        if (preg_match('/^(md\d+)\s*:\s*(.*)$/', trim($line), $m)) {
            $arrays[] = array('device' => $m[1], 'summary' => trim($m[2]));
        }
    }
    return array('arrays' => $arrays, 'description' => count($arrays) ? 'Phát hiện md RAID từ /proc/mdstat' :
        'Không thấy md RAID; không loại trừ RAID phần cứng, LVM hoặc RAID khác');
}
function amnote_mounts() {
    $raw = amnote_file('/proc/mounts');
    $rows = array(); $seen = array();
    if ($raw === false) return $rows;
    $fileSystems = array('ext2'=>true,'ext3'=>true,'ext4'=>true,'xfs'=>true,'btrfs'=>true,'zfs'=>true,'f2fs'=>true,'vfat'=>true,'ntfs'=>true,'fuseblk'=>true);
    foreach (explode("\n", $raw) as $line) {
        $cols = preg_split('/\s+/', trim($line));
        if (count($cols) < 3 || !isset($fileSystems[$cols[2]])) continue;
        $source = str_replace('\\040', ' ', $cols[0]);
        $target = str_replace('\\040', ' ', $cols[1]);
        if (strpos($source, '/dev/') !== 0 || isset($seen[$target]) || !is_dir($target)) continue;
        $seen[$target] = true;
        $total = @disk_total_space($target); $free = @disk_free_space($target);
        if ($total === false || $free === false) continue;
        $rows[] = array('device' => $source, 'mount' => $target, 'fs' => $cols[2],
                        'total' => $total, 'used' => max(0,$total-$free), 'free' => $free);
        if (count($rows) >= 16) break;
    }
    // Filesystem root may be overlay/non-/dev on containerized hosts; still show its usable capacity.
    if (!isset($seen['/'])) {
        $total = @disk_total_space('/'); $free = @disk_free_space('/');
        if ($total !== false && $free !== false) {
            $rootSrc = 'Chưa xác định'; $rootFs = 'Chưa xác định';
            foreach (explode("\n", $raw) as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 3 && $parts[1] === '/') {
                    $rootSrc = $parts[0]; $rootFs = $parts[2]; break;
                }
            }
            $rows[] = array('device' => $rootSrc, 'mount' => '/', 'fs' => $rootFs,
                            'total' => $total, 'used' => max(0,$total-$free), 'free' => $free);
        }
    }
    usort($rows, function($a,$b){
        if ($a['mount'] === '/') return -1;
        if ($b['mount'] === '/') return 1;
        return strcmp($a['mount'],$b['mount']);
    });
    return $rows;
}
function amnote_interfaces() {
    $dirs = @glob('/sys/class/net/*');
    $rows = array();
    if (!is_array($dirs)) return $rows;
    foreach ($dirs as $dir) {
        $name = basename($dir);
        if ($name === 'lo') continue;
        $rows[] = array('name' => $name, 'state' => amnote_small_text($dir . '/operstate'),
                        'speedMbps' => amnote_small_text($dir . '/speed'),
                        'mtu' => amnote_small_text($dir . '/mtu'));
        if (count($rows) >= 20) break;
    }
    return $rows;
}
function amnote_metadata() {
    $defaults = array('position' => null, 'datacenter' => null, 'rack' => null,
                      'serverRole' => null, 'provider' => null,
                      'backupNotes' => null, 'storageNotes' => null);
    $path = __DIR__ . '/server-meta.php';
    if (!is_file($path)) return $defaults;
    $values = require $path;
    if (!is_array($values)) return $defaults;
    foreach ($defaults as $key => $unused) {
        if (isset($values[$key]) && is_scalar($values[$key])) {
            $text = trim((string)$values[$key]);
            $defaults[$key] = $text !== '' ? substr($text,0,500) : null;
        }
    }
    return $defaults;
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
    'system' => array(
        'os' => amnote_os_release(), 'kernel' => php_uname('r'),
        'architecture' => php_uname('m'), 'phpVersion' => PHP_VERSION,
        'board' => amnote_board_details(), 'cpuDetails' => amnote_cpu_details(),
        'blockDevices' => amnote_block_devices(), 'raid' => amnote_mdraid(),
        'mounts' => amnote_mounts(), 'interfaces' => amnote_interfaces(),
        'meta' => amnote_metadata()
    ),
    'checkType' => 'local-tcp-port-only'
);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
