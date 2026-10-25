<?php
/**
 * Local Network Assessment Speedtest - Garbage Endpoint
 * Delivers uncompressible binary chunks for Gigabit Download Measurements
 * Security-hardened & Optimized for Low Server CPU & Max Network Saturation
 */

// Disable Output Compression and Buffering
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('output_handler', '');
while (@ob_end_clean());

// Security & Caching Headers
header('HTTP/1.1 200 OK');
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=speedtest_payload.dat');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Connection: keep-alive');
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

// Generate a high-entropy 1MB block once to prevent CPU bottleneck on Gigabit links
$chunkSize = 1048576; // 1MB
if (function_exists('random_bytes')) {
    $data = random_bytes($chunkSize);
} elseif (function_exists('openssl_random_pseudo_bytes')) {
    $data = openssl_random_pseudo_bytes($chunkSize);
} else {
    $data = str_repeat(md5(microtime(true)), $chunkSize / 32);
}

// Validate chunk count with safe bounds (1 to 256 chunks = 1MB to 256MB per request)
$chunks = isset($_GET['ckSize']) ? intval($_GET['ckSize']) : 4;
if ($chunks < 1) {
    $chunks = 4;
}
if ($chunks > 256) {
    $chunks = 256; // Strict upper bound to prevent DoS / memory exhaustion
}

// Stream data efficiently to the client
for ($i = 0; $i < $chunks; $i++) {
    if (connection_aborted()) {
        break;
    }
    echo $data;
    @flush();
}
?>