<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Dashboard";

include 'includes/header.php';

if (isAdmin()) {
    // Admin dashboard
    $totalUsers = count(getAllUsers());
    $totalTasks = count(getAllTasks());
    $recentTasks = array_slice(getAllTasks(), 0, 5);
} else {
    // User dashboard
    $userTasks = getTasksByUserId($_SESSION['user_id']);
    $pendingTasks = array_filter($userTasks, function ($task) {
        return $task['status'] === 'Pending';
    });
    $inProgressTasks = array_filter($userTasks, function ($task) {
        return $task['status'] === 'In Progress';
    });
    $completedTasks = array_filter($userTasks, function ($task) {
        return $task['status'] === 'Completed';
    });
    $recentTasks = array_slice($userTasks, 0, 5);
}
?>

<?php if (isAdmin()): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="display-4"><?php echo $totalUsers; ?></p>
                    <a href="admin/users.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tasks</h5>
                    <p class="display-4"><?php echo $totalTasks; ?></p>
                    <a href="admin/tasks.php" class="btn btn-primary">Manage Tasks</a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pending Tasks</h5>
                    <p class="display-4"><?php echo count($pendingTasks); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">In Progress</h5>
                    <p class="display-4"><?php echo count($inProgressTasks); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Completed</h5>
                    <p class="display-4"><?php echo count($completedTasks); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<h3 class="mb-3">Recent Tasks</h3>
<div class="row">
    <?php foreach ($recentTasks as $task): ?>
        <div class="col-md-6">
            <div class="card task-card status-<?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    <p class="card-text"><small class="text-muted">Deadline:
                            <?php echo date('M j, Y', strtotime($task['deadline'])); ?></small></p>
                    <p class="card-text">
                        <span class="badge bg-<?php
                        if ($task['status'] === 'Pending')
                            echo 'warning';
                        elseif ($task['status'] === 'In Progress')
                            echo 'info';
                        else
                            echo 'success';
                        ?>"><?php echo $task['status']; ?></span>
                    </p>
                    <?php if (isAdmin()): ?>
                        <p class="card-text">Assigned to: <?php echo htmlspecialchars($task['assigned_to_name']); ?></p>
                    <?php else: ?>
                        <p class="card-text">Assigned by: <?php echo htmlspecialchars($task['assigned_by_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>