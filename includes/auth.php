<?php
require_once 'db.php';
require_once 'functions.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function loginUser($email, $password)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Check if password needs to be changed (first login)
        if ($user['password_changed'] == 0) {
            $_SESSION['needs_password_change'] = true;
            header('Location: user/change_password.php');
            exit();
        }

        return true;
    }

    return false;
}

function logoutUser()
{
    session_unset();
    session_destroy();
}