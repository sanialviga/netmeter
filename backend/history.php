<?php
/**
 * Local Network Assessment Speedtest - Centralized Server History Storage
 * Stores all speedtest records centrally on the tester laptop/server
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

$dataFile = __DIR__ . '/../data/history.json';

// Ensure data directory exists
$dataDir = dirname($dataFile);
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}

// Helper to read history safely
function loadHistory($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = @file_get_contents($file);
    if (!$content) {
        return [];
    }
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

// Helper to save history safely with locking
function saveHistory($file, $records) {
    return @file_put_contents($file, json_encode($records, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
}

// 1. GET: Return all history stored on the server laptop
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $records = loadHistory($dataFile);
    echo json_encode([
        'success' => true,
        'count' => count($records),
        'history' => $records
    ]);
    exit(0);
}

// 2. POST: Actions (save, delete_single, delete_date, delete_all)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $payload = json_decode($rawInput, true);

    if (!$payload || !isset($payload['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action or payload']);
        exit(0);
    }

    $action = $payload['action'];
    $records = loadHistory($dataFile);

    if ($action === 'save') {
        if (!isset($payload['record']) || !is_array($payload['record'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing record object']);
            exit(0);
        }

        $newRecord = $payload['record'];
        if (empty($newRecord['id'])) {
            $newRecord['id'] = 'lan_' . round(microtime(true) * 1000) . '_' . mt_rand(100, 999);
        }

        // Add to beginning of array
        array_unshift($records, $newRecord);

        // Keep maximum 500 records on server to avoid excessive file size
        if (count($records) > 500) {
            array_splice($records, 500);
        }

        saveHistory($dataFile, $records);
        echo json_encode(['success' => true, 'action' => 'save', 'record' => $newRecord]);
        exit(0);
    }

    if ($action === 'delete_single') {
        $targetId = isset($payload['id']) ? $payload['id'] : '';
        if (!$targetId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing id']);
            exit(0);
        }

        $records = array_values(array_filter($records, function($item) use ($targetId) {
            return isset($item['id']) && $item['id'] !== $targetId;
        }));

        saveHistory($dataFile, $records);
        echo json_encode(['success' => true, 'action' => 'delete_single', 'deletedId' => $targetId]);
        exit(0);
    }

    if ($action === 'delete_date') {
        $targetDate = isset($payload['date']) ? trim($payload['date']) : '';
        if (!$targetDate) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing date']);
            exit(0);
        }

        $records = array_values(array_filter($records, function($item) use ($targetDate) {
            return !isset($item['date']) || $item['date'] !== $targetDate;
        }));

        saveHistory($dataFile, $records);
        echo json_encode(['success' => true, 'action' => 'delete_date', 'deletedDate' => $targetDate]);
        exit(0);
    }

    if ($action === 'delete_all') {
        saveHistory($dataFile, []);
        echo json_encode(['success' => true, 'action' => 'delete_all']);
        exit(0);
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)]);
}
?>