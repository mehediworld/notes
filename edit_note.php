<?php
// edit_note.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Fetch departments for the dropdown
try {
    $departments_stmt = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
    $departments = $departments_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching departments: " . $e->getMessage());
    $departments = [];
}

// Fetch employees for the dropdown
try {
    $employees_stmt = $conn->query("SELECT id, name FROM employee ORDER BY name ASC");
    $employees = $employees_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching departments: " . $e->getMessage());
    $employees = [];
}

$user_id = $_SESSION['user']['id'];
$note_id = $_GET['id'];

// Fetch the note
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$note_id, $user_id]);
$note = $stmt->fetch();

if (!$note) {
    echo "Note not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $department_id = $_POST['department_id'];

    $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, priority = ?, status = ?, department_id = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $content, $priority, $status, $department_id, $note_id, $user_id]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Note Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_note.php">Add Note</a></li>
                    <li class="nav-item"><a class="nav-link" href="change_password.php">Change Password</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <h2>Edit Note</h2>
    <form method="post">
        <div class="mb-3">
            <label for="department_id" class="form-label">Department</label>
            <select class="form-select" name="department_id" id="department_id" required>
                <option value="">Select Department</option>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>"
                            <?php echo ($note['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No departments found</option>
                <?php endif; ?>
            </select>
        </div>
		<div class="mb-3">
			<label for="letter_memo_no" class="form-label">Letter/Memo No</label>
			<input type="text" class="form-control" name="letter_memo_no" value="<?= htmlspecialchars($note['Letter_Memo_no']) ?>" required>
		</div>
        <div class="mb-3">
            <label for="title" class="form-label">Title / Subject</label>
            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($note['title']) ?>" required>
        </div>
		<div class="mb-3">
			<label for="ict_receiving_date" class="form-label">ICT Receiving Date</label>
			<input type="date" class="form-control" name="ict_receiving_date" value="<?= htmlspecialchars($note['ICT_Receiving_Date']) ?>" required>
		</div>
        <div class="mb-3">
            <label for="content" class="form-label">Description</label>
            <textarea class="form-control" name="content" rows="5" required><?= htmlspecialchars($note['content']) ?></textarea>
        </div>
		<div class="mb-3">

			<select class="form-select" name="assigned_to" id="assigned_to" required>
                <option value="">Select Employee</option>
                <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo htmlspecialchars($emp['id']); ?>"
                            <?php echo ($note['Assigned_to'] == $emp['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($emp['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No Employees found</option>
                <?php endif; ?>
            </select>
		</div>
		<div class="mb-3">
			<label for="comment" class="form-label">Comment</label>
			<textarea class="form-control" name="comment" required><?= htmlspecialchars($note['content']) ?></textarea>
		</div>
        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-select" name="priority">
                <option value="Low" <?= $note['priority'] == 'Low' ? 'selected' : '' ?>>Low</option>
                <option value="Medium" <?= $note['priority'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="High" <?= $note['priority'] == 'High' ? 'selected' : '' ?>>High</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="Open" <?= $note['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="In Progress" <?= $note['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Solved" <?= $note['status'] == 'Solved' ? 'selected' : '' ?>>Solved</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php //include('footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>