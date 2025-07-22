<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/mailer.php'; // includes mailer functionality

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Initialize variables with empty values
$name = '';
$email = '';
$password = '';
$role = 'user'; // Default role
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim all input values
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'user';

    // Validate inputs
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        // Check if email already exists
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            // Validate password strength
            $validation = validatePassword($password);
            if ($validation === true) {
                if (addUser($name, $email, $password, $role)) {
                    $success = 'User added successfully';

                    // Send welcome email using your existing sendEmail function
                    $subject = 'Welcome to Task Management System';
                    $html_message = "
                        <h1>Welcome, $name!</h1>
                        <p>Your account has been created successfully.</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Temporary Password:</strong> $password</p>
                        <p>Please login and change your password immediately.</p>
                        <p><a href=\"" . BASE_URL . "/login.php\">Click here to login</a></p>
                    ";

                    // Use your existing sendEmail function
                    $email_sent = sendEmail($email, $name, $subject, $html_message);

                    if (!$email_sent) {
                        // Email failed to send, but user was still created
                        $success .= ' (Welcome email could not be sent)';
                    }

                    // Clear form
                    $name = $email = $password = '';
                    $role = 'user';
                } else {
                    $error = 'Error adding user';
                }
            } else {
                $error = $validation;
            }
        }
    }
}

$pageTitle = "Add User";

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add New User</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                value="<?php echo htmlspecialchars($password); ?>" required>
                            <small class="text-muted">Minimum 8 characters with at least one number and one special
                                character</small>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>