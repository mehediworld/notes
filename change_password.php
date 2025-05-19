<?php
// change_password.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password'])) {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $userId]);
        $message = "Password updated successfully.";
    } else {
        $message = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		footer {
			position: relative;
			bottom: 0;
			width: 100%;
		}
	</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Note Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="add_note.php">Add Note</a></li>
                <li class="nav-item"><a class="nav-link active" href="change_password.php">Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Change Password</div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info"> <?= $message ?> </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <button type="submit" class="btn btn-success">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php //include('footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
