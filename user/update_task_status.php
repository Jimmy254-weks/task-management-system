<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($task_id && $status && in_array($status, ['Pending', 'In Progress', 'Completed'])) {
        if (updateTaskStatus($task_id, $status)) {
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>