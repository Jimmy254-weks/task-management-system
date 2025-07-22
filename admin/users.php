<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$users = getAllUsers();

$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'cannot_delete_admin':
            $error_message = 'Cannot delete admin user';
            break;
        case 'cannot_delete_self':
            $error_message = 'You cannot delete your own account';
            break;
        case 'delete_failed':
            $error_message = 'Failed to delete user';
            break;
    }
}

$pageTitle = "Manage Users";

include '../includes/header.php';
?>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<h2 class="mb-4">Manage Users</h2>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title m-0">Users List</h5>
            <a href="register.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New User
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">User deleted successfully</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                                    class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                                    class="btn btn-sm btn-danger delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>