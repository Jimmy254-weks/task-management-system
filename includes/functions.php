<?php
require_once 'db.php';
require_once 'mailer.php';

/**
 * Get all users from the database
 */
function getAllUsers()
{
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, role, email_notifications FROM users ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get a single user by ID
 */
function getUserById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, role, password_changed, email_notifications FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get complete user data including password by ID
 */
function getCompleteUserById($id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by email
 */
function getUserByEmail($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}



/**
 * Add a new user to the database
 */
function addUser($name, $email, $password, $role = 'user')
{
    global $pdo;

    if (empty($name) || empty($email) || empty($password)) {
        return false;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashed_password, $role]);
}

/**
 * Update user information
 */
function updateUser($id, $name, $email, $password = null, $role = null)
{
    global $pdo;

    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        if ($role) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $hashed_password, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $hashed_password, $id]);
        }
    } else {
        if ($role) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $id]);
        }
    }
}

/**
 * Delete a user from the database
 */
function deleteUser($id)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user && $user['role'] === 'admin') {
        return false;
    }

    $pdo->prepare("DELETE FROM tasks WHERE assigned_to = ?")->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Get all tasks from the database
 */
function getAllTasks()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT t.*, 
               u1.name as assigned_to_name, 
               u2.name as assigned_by_name 
        FROM tasks t 
        JOIN users u1 ON t.assigned_to = u1.id 
        JOIN users u2 ON t.assigned_by = u2.id 
        ORDER BY t.deadline
    ");
    return $stmt->fetchAll();
}

/**
 * Get tasks assigned to a specific user
 */
function getTasksByUserId($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, u.name as assigned_by_name 
        FROM tasks t 
        JOIN users u ON t.assigned_by = u.id 
        WHERE t.assigned_to = ? 
        ORDER BY t.deadline
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get a single task by ID
 */
function getTaskById($task_id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, 
               u1.name as assigned_to_name, 
               u2.name as assigned_by_name 
        FROM tasks t 
        JOIN users u1 ON t.assigned_to = u1.id 
        JOIN users u2 ON t.assigned_by = u2.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$task_id]);
    return $stmt->fetch();
}

/**
 * Add a new task to the database
 */
function addTask($title, $description, $assigned_to, $assigned_by, $deadline)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, deadline) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$title, $description, $assigned_to, $assigned_by, $deadline]);

        if ($result) {
            $user_stmt = $pdo->prepare("SELECT email, name, email_notifications FROM users WHERE id = ?");
            $user_stmt->execute([$assigned_to]);
            $user = $user_stmt->fetch();

            $assigner_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $assigner_stmt->execute([$assigned_by]);
            $assigner = $assigner_stmt->fetch();

            // Check if user exists and email notifications are enabled (default to true if not set)
            if ($user && ($user['email_notifications'] ?? true)) {
                $formatted_deadline = date('F j, Y', strtotime($deadline));
                sendTaskAssignmentEmail(
                    $user['email'],
                    $user['name'],
                    $title,
                    $description,
                    $formatted_deadline,
                    $assigner['name'] // Pass the assigner's name
                );
            }

            $pdo->commit();
            return true;
        }

        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding task: " . $e->getMessage());
        return false;
    }
}

/**
 * Update task information
 */
function updateTask($task_id, $title, $description, $assigned_to, $deadline, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, assigned_to = ?, deadline = ?, status = ? 
        WHERE id = ?
    ");
    return $stmt->execute([$title, $description, $assigned_to, $deadline, $status, $task_id]);
}

/**
 * Update only the task status
 */
function updateTaskStatus($task_id, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $task_id]);
}

/**
 * Delete a task from the database
 */
function deleteTask($task_id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    return $stmt->execute([$task_id]);
}

/**
 * Check if user needs to change password (first login)
 */
function needsPasswordChange($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT password_changed FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user && $user['password_changed'] == 0;
}

/**
 * Validate password meets requirements
 */
function validatePassword($password)
{
    if (strlen($password) < 7) {
        return "Password must be at least 7 characters long";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }
    if (!preg_match('/[a-zA-Z]/', $password)) {
        return "Password must contain at least one letter";
    }
    return true;
}

/**
 * Update password and mark as changed
 */
function updatePassword($user_id, $password)
{
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, password_changed = 1 WHERE id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}

/**
 * Send task assignment email
 */
function sendTaskAssignmentEmail($to_email, $to_name, $task_title, $task_description, $deadline, $assigned_by)
{
    $subject = "New Task Assigned: $task_title";

    $html_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #3498db; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .task-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #eee; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; text-align: center; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #3498db; 
                   color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
            .btn:hover { background-color: #2980b9; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Task Assignment</h2>
            </div>
            <div class='content'>
                <p>Hello $to_name,</p>
                <p>You have been assigned a new task by $assigned_by:</p>
                
                <div class='task-details'>
                    <h3 style='margin-top: 0;'>$task_title</h3>
                    <p><strong>Description:</strong><br>$task_description</p>
                    <p><strong>Deadline:</strong> $deadline</p>
                </div>
                
                <p>Please log in to the system to view and update this task.</p>
                <a href='" . BASE_URL . "' class='btn'>Go to Task System</a>
            </div>
            <div class='footer'>
                <p>This is an automated notification. Please do not reply to this email.</p>
                <p>&copy; " . date('Y') . " Task Management System</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to_email, $to_name, $subject, $html_body);
}
/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($to_email, $to_name, $temp_password)
{
    $subject = "Welcome to Task Management System";

    $html_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #3498db; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to Task Management System</h2>
            </div>
            <div class='content'>
                <p>Hello $to_name,</p>
                <p>Your account has been created successfully. Here are your login details:</p>
                <p><strong>Email:</strong> $to_email</p>
                <p><strong>Temporary Password:</strong> $temp_password</p>
                <p>Please login and change your password immediately.</p>
                <a href='" . BASE_URL . "/login.php' style='display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Login Now</a>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to_email, $to_name, $subject, $html_body);
}

//for notification count
function getUnreadNotificationCount($user_id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

function getNotificationsByUserId($user_id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}


/**
 * Send password change confirmation email
 */
function sendPasswordChangeEmail($to_email, $to_name)
{
    $subject = "Your Password Has Been Changed";

    $html_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #3498db; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Password Changed Successfully</h2>
            </div>
            <div class='content'>
                <p>Hello $to_name,</p>
                <p>This is a confirmation that your password for your account has been successfully changed.</p>
                <p>If you didn't make this change, please contact your system administrator immediately.</p>
                <a href='" . BASE_URL . "' style='display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Login to System</a>
            </div>
            <div class='footer'>
                <p>This is an automated notification. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to_email, $to_name, $subject, $html_body);
}

/**
 * Count all tasks in the system
 */
function countAllTasks()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM tasks");
    return $stmt->fetchColumn();
}

/**
 * Count tasks for a specific user
 */
function countUserTasks($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Count tasks by status for a specific user
 */
function countTasksByStatus($user_id, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = ?");
    $stmt->execute([$user_id, $status]);
    return $stmt->fetchColumn();
}

/**
 * Get recent tasks (limit by count)
 */
function getRecentTasks($limit = 5)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, 
               u1.name as assigned_to_name, 
               u2.name as assigned_by_name 
        FROM tasks t 
        JOIN users u1 ON t.assigned_to = u1.id 
        JOIN users u2 ON t.assigned_by = u2.id 
        ORDER BY t.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Check if a user exists
 */
function userExists($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Get tasks that are due soon (within 3 days)
 */
function getDueSoonTasks($user_id = null)
{
    global $pdo;
    $three_days_later = date('Y-m-d', strtotime('+3 days'));

    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT * FROM tasks 
            WHERE assigned_to = ? 
            AND deadline <= ? 
            AND status != 'Completed'
            ORDER BY deadline
        ");
        $stmt->execute([$user_id, $three_days_later]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM tasks 
            WHERE deadline <= ? 
            AND status != 'Completed'
            ORDER BY deadline
        ");
        $stmt->execute([$three_days_later]);
    }

    return $stmt->fetchAll();
}