<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header('Location: tasks.php');
    exit();
}

if (deleteTask($task_id)) {
    header('Location: tasks.php?success=1');
} else {
    header('Location: tasks.php?error=delete_failed');
}
exit();
?>