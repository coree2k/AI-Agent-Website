<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $agentId = isset($_GET['agentId']) ? $_GET['agentId'] : '';
    
    if ($agentId === 'all') {
        // 모든 파일 불러오기
        $dataDir = '../data/';
        $files = glob($dataDir . '*.txt');
        $allData = [];
        
        foreach ($files as $file) {
            $filename = basename($file, '.txt');
            $content = file_get_contents($file);
            $allData[$filename] = $content;
        }
        
        echo json_encode($allData);
    } else if (!empty($agentId)) {
        // 특정 파일 불러오기
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agentId);
        $filepath = '../data/' . $filename . '.txt';
        
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
            echo json_encode(['content' => $content]);
        } else {
            echo json_encode(['content' => null]);
        }
    } else {
        echo json_encode(['error' => 'agentId is required']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>