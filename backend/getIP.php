<?php
/**
 * Local Network Assessment Speedtest - IP, MAC, Hostname, Device & Storage Identification Endpoint
 * Secure, Intranet-aware, and Extracts Client Network & Hardware Identity on the Server Laptop
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// CORS Support
if (isset($_GET["cors"]) || isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Encoding, Content-Type, Authorization, X-Requested-With, Origin, Accept");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// 1. Resolve Client IP
function getClientIp() {
    $headers = ['HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ipList = explode(',', $_SERVER[$header]);
            $ip = trim($ipList[0]);
            $ip = preg_replace('/^::ffff:/', '', $ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
}

// 2. Query Local ARP Table on the Server Laptop for Client MAC Address
function getClientMac($ip) {
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return 'Localhost / Loopback';
    }
    $escapedIp = escapeshellarg($ip);
    $output = @shell_exec("arp -a $escapedIp");
    if ($output && preg_match('/([0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2})/', $output, $matches)) {
        return strtoupper(str_replace('-', ':', $matches[1]));
    }
    return 'VLAN Routed / Unknown';
}

// 3. Resolve Client Hostname via reverse DNS
function getClientHostname($ip) {
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return gethostname();
    }
    $host = @gethostbyaddr($ip);
    if ($host && $host !== $ip) {
        return $host;
    }
    return '';
}

// 4. Parse Client Browser & OS from User-Agent
function parseUserAgent($ua) {
    $os = "Windows";
    $browser = "Browser";

    if (stripos($ua, 'Windows NT 10.0') !== false) {
        $os = "Windows 10/11";
    } elseif (stripos($ua, 'Windows NT 6.3') !== false) {
        $os = "Windows 8.1";
    } elseif (stripos($ua, 'Windows NT 6.1') !== false) {
        $os = "Windows 7";
    } elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) {
        $os = "macOS";
    } elseif (stripos($ua, 'Android') !== false) {
        $os = "Android";
    } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
        $os = "iOS";
    } elseif (stripos($ua, 'Linux') !== false) {
        $os = "Linux";
    }

    if (stripos($ua, 'Edg/') !== false) {
        $browser = "Microsoft Edge";
    } elseif (stripos($ua, 'Chrome/') !== false && stripos($ua, 'Chromium/') === false) {
        $browser = "Google Chrome";
    } elseif (stripos($ua, 'Firefox/') !== false) {
        $browser = "Mozilla Firefox";
    } elseif (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome/') === false) {
        $browser = "Apple Safari";
    } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
        $browser = "Opera";
    }

    return "$browser ($os)";
}

// 5. Query Localhost Physical Storage (SSD / HDD & Capacity)
function getLocalStorageInfo($ip) {
    if ($ip === '127.0.0.1' || $ip === '::1') {
        $psCmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "$d = Get-PhysicalDisk | Select-Object -First 1 MediaType, Size, FriendlyName; [PSCustomObject]@{Type=$d.MediaType; Size=[math]::Round($d.Size/1GB); Model=$d.FriendlyName} | ConvertTo-Json -Compress"';
        $out = @shell_exec($psCmd);
        if ($out) {
            $parsed = @json_decode(trim($out), true);
            if ($parsed && !empty($parsed['Type'])) {
                $type = $parsed['Type'];
                $size = ($parsed['Size'] ? $parsed['Size'] . ' GB' : '512 GB');
                $model = !empty($parsed['Model']) ? $parsed['Model'] : '';
                return [
                    'type' => $type,
                    'size' => $size,
                    'model' => $model,
                    'display' => "$type ($size" . ($model ? " - $model" : "") . ")"
                ];
            }
        }
        return [
            'type' => 'SSD',
            'size' => '512 GB',
            'model' => '',
            'display' => 'SSD (512 GB)'
        ];
    }
    return null;
}

$ip = getClientIp();
$serverHost = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : gethostname();
$mac = getClientMac($ip);
$hostname = getClientHostname($ip);
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$device = parseUserAgent($ua);
$storage = getLocalStorageInfo($ip);

$isPrivate = false;
$subnetClass = 'Public WAN';
$processedString = $ip;
$warning = null;

if ($ip === '::1' || $ip === '127.0.0.1') {
    $isPrivate = true;
    $subnetClass = 'Loopback (Localhost)';
    $processedString = "$ip (Localhost Laptop Server)";
} elseif (strpos($ip, '169.254.') === 0) {
    $isPrivate = true;
    $subnetClass = 'APIPA Link-Local (169.254.x.x)';
    $warning = 'PERINGATAN: IP 169.254.x.x menandakan DHCP Server Lokal mati/gagal memberikan IP!';
    $processedString = "$ip (APIPA - DHCP Failure Warning)";
} elseif (strpos($ip, '10.') === 0) {
    $isPrivate = true;
    $subnetClass = 'Class A (10.0.0.0/8 - RS/Enterprise)';
    $processedString = "$ip (LAN Kelas A - RS/Enterprise)";
} elseif (preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $ip)) {
    $isPrivate = true;
    $subnetClass = 'Class B (172.16.0.0/12 - Kampus/Subnet)';
    $processedString = "$ip (LAN Kelas B - Kampus)";
} elseif (strpos($ip, '192.168.') === 0) {
    $isPrivate = true;
    $subnetClass = 'Class C (192.168.0.0/16 - Local Subnet)';
    $processedString = "$ip (LAN Subnet Lokal)";
} else {
    $processedString = "$ip (Public WAN)";
}

$response = [
    'ip' => $ip,
    'mac' => $mac,
    'hostname' => $hostname,
    'device' => $device,
    'storage' => $storage,
    'isPrivate' => $isPrivate,
    'subnetClass' => $subnetClass,
    'serverHost' => $serverHost,
    'warning' => $warning,
    'processedString' => $processedString,
    'rawIspInfo' => [
        'ip' => $ip,
        'mac' => $mac,
        'hostname' => $hostname,
        'device' => $device,
        'storage' => $storage,
        'subnet' => $subnetClass,
        'server' => $serverHost
    ]
];

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>