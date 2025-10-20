<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Admin password (SHA-256 of 'admin@')
$ADMIN_PASSWORD_HASH = '13a95c75b44f95ead23f47f0bf10667e57b44ec5150180c8a39a39361cf56169';

function sha256_hex($s) {
    return hash('sha256', $s);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!is_array($data)) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }

    $agentId = isset($data['agentId']) ? $data['agentId'] : null;
    $content = isset($data['content']) ? $data['content'] : null;
    $password_hash = isset($data['password_hash']) ? $data['password_hash'] : '';
    $password_plain = isset($data['password']) ? $data['password'] : '';

    if (!$agentId || $content === null) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
    }

    // Backward compatibility: if plaintext provided, hash it
    if ($password_hash === '' && $password_plain !== '') {
        $password_hash = sha256_hex($password_plain);
    }

    if (strtolower($password_hash) !== strtolower($ADMIN_PASSWORD_HASH)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agentId);
    $dir = realpath(__DIR__ . '/../data');
    if ($dir === false) {
        $dir = __DIR__ . '/../data';
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $fullpath = $dir . '/' . $filename . '.txt';

    if (file_put_contents($fullpath, $content) !== false) {
        echo json_encode(['success' => true, 'message' => 'Saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
