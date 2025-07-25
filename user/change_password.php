<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$forcePasswordChange = isset($_SESSION['needs_password_change']) && $_SESSION['needs_password_change'];
$user = getCompleteUserById($_SESSION['user_id']);

// Set page title before including header
$pageTitle = $forcePasswordChange ? 'First Login - Change Password' : 'Change Password';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate inputs
    if (empty($current_password)) {
        $error = 'Current password is required';
    } elseif (empty($password)) {
        $error = 'New password is required';
    } elseif (empty($confirm_password)) {
        $error = 'Please confirm your new password';
    } else {
        // For forced password change, verify current password matches
        if ($forcePasswordChange) {
            if (!password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect';
            }
        } else {
            // For regular password change, verify current password matches
            if (!password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect';
            }
        }

        if (empty($error)) {
            if ($password === $current_password) {
                $error = 'New password must be different from current password';
            } elseif ($password !== $confirm_password) {
                $error = 'New passwords do not match';
            } else {
                $validation = validatePassword($password);
                if ($validation === true) {
                    if (updatePassword($_SESSION['user_id'], $password)) {
                        $success = 'Password changed successfully!';

                        // For forced password change, redirect to dashboard
                        if ($forcePasswordChange) {
                            unset($_SESSION['needs_password_change']);
                            // Send confirmation email
                            sendPasswordChangeEmail($_SESSION['user_email'], $_SESSION['user_name']);
                            header('Refresh: 2; URL=../index.php');
                        } else {
                            // Send confirmation email for regular password changes
                            sendPasswordChangeEmail($_SESSION['user_email'], $_SESSION['user_name']);
                        }
                    } else {
                        $error = 'Error updating password';
                    }
                } else {
                    $error = $validation;
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title text-center">
                    <?php echo $pageTitle; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($forcePasswordChange): ?>
                    <div class="alert alert-warning">
                        This is your first login. You must change your password before proceeding.
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <?php echo $forcePasswordChange ? 'Temporary Password' : 'Current Password'; ?>
                        </label>
                        <input type="password" class="form-control" id="current_password" name="current_password"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>

                    <?php if (!$forcePasswordChange): ?>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>