<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Get user data first
$user = getUserById($user_id);

// Prevent deleting admin users
if ($user && $user['role'] === 'admin') {
    header('Location: users.php?error=cannot_delete_admin');
    exit();
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    header('Location: users.php?error=cannot_delete_self');
    exit();
}

if (deleteUser($user_id)) {
    header('Location: users.php?success=1');
} else {
    header('Location: users.php?error=delete_failed');
}
exit();
?>