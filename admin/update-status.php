<?php
/**
 * SOBHA REALTY LUXURY - ASYNC LEAD STATUS UPDATE API
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../api/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$allowedStatuses = ['New', 'Contacted', 'Qualified', 'Converted', 'Spam'];

if (!$id || !in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$db = getDbConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $db->prepare("UPDATE leads SET status = :status WHERE id = :id");
$success = $stmt->execute([':status' => $status, ':id' => $id]);

echo json_encode(['success' => $success]);
exit;
