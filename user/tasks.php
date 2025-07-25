<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$tasks = getTasksByUserId($user_id);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];

    if (in_array($new_status, ['Pending', 'In Progress', 'Completed'])) {
        updateTaskStatus($task_id, $new_status);
        header('Location: tasks.php?updated=1');
        exit();
    }
}

$pageTitle = "My Tasks";

include '../includes/header.php';
?>

<div class="container">
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Task status updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Tasks</h2>
        <span class="badge bg-primary"><?php echo count($tasks); ?> tasks</span>
    </div>

    <div class="row">
        <?php foreach ($tasks as $task): ?>
            <div class="col-md-6 mb-4">
                <div class="card task-card status-<?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                            <span class="badge bg-<?php
                            echo $task['status'] === 'Pending' ? 'warning' :
                                ($task['status'] === 'In Progress' ? 'info' : 'success');
                            ?>">
                                <?php echo $task['status']; ?>
                            </span>
                        </div>

                        <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>

                        <div class="task-meta mb-3">
                            <div><i class="fas fa-user-tag me-2"></i> Assigned by:
                                <?php echo htmlspecialchars($task['assigned_by_name']); ?>
                            </div>
                            <div><i class="fas fa-calendar-alt me-2"></i> Deadline:
                                <?php echo date('M j, Y', strtotime($task['deadline'])); ?>
                            </div>
                            <div><i class="fas fa-clock me-2"></i> Created:
                                <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                            </div>
                        </div>

                        <form method="POST" class="status-form">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>
                                        Pending</option>
                                    <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($tasks)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    You don't have any tasks assigned yet.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>