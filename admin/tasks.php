<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$tasks = getAllTasks();

$pageTitle = "Manage Tasks";
include '../includes/header.php';
?>

<div class="container">
    <h2 class="mb-4">Manage Tasks</h2>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title m-0">Tasks List</h5>
                <a href="add_task.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Add New Task
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Task deleted successfully</div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['assigned_to_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($task['deadline'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                    if ($task['status'] === 'Pending')
                                        echo 'warning';
                                    elseif ($task['status'] === 'In Progress')
                                        echo 'info';
                                    else
                                        echo 'success';
                                    ?>"><?php echo $task['status']; ?></span>
                                </td>
                                <td>
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>"
                                        class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>