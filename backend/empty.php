<?php
/**
 * Local Network Assessment Speedtest - Empty Endpoint
 * Used for Ping, Jitter, and Upload Speed Measurements
 * Security-hardened & Optimized for High Throughput LANs
 */

// Disable caching completely
header("HTTP/1.1 200 OK");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: keep-alive");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");

// Intranet CORS handling
if (isset($_GET["cors"]) || isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Encoding, Content-Type, Authorization, X-Requested-With, Origin, Accept");
    header("Access-Control-Max-Age: 86400");
}

// Return immediately for OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Drain POST input stream efficiently without memory bloat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputStream = @fopen('php://input', 'rb');
    if ($inputStream) {
        // Read & discard in 1MB chunks to measure incoming network throughput
        while (!feof($inputStream)) {
            @fread($inputStream, 1048576);
        }
        @fclose($inputStream);
    }
}

// Output empty response
http_response_code(200);
?>