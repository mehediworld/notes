<?php
// dashboard.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch all notes
$stmt = $conn->prepare("SELECT n.*, d.name as department_name, e.id as employee_id, e.name as employee_name
                        FROM notes n 
                        LEFT JOIN departments d ON n.department_id = d.id 
                        LEFT JOIN employee e ON n.Assigned_to = e.id");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();

// Get statistics
$total_notes = $conn->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$pending_notes = $conn->query("SELECT COUNT(*) FROM notes WHERE status != 'Solved'")->fetchColumn();
$solved_notes = $conn->query("SELECT COUNT(*) FROM notes WHERE status = 'Solved'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }
        td a {
            margin-bottom: 6px;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.7;
        }
        .total-notes {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        .pending-notes {
            background: linear-gradient(135deg, #f12711 0%, #f5af19 100%);
            color: white;
        }
        .solved-notes {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?= $_SESSION['user']['name'] ?>!</h2>
        <div>
            <a href="add_note.php" class="btn btn-success">Add Note</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card total-notes mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h5 class="card-title">Total Notes</h5>
                            <h2 class="mb-0"><?= $total_notes ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="bi bi-journal-text stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card pending-notes mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h5 class="card-title">Pending Notes</h5>
                            <h2 class="mb-0"><?= $pending_notes ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="bi bi-hourglass-split stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card solved-notes mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h5 class="card-title">Solved Notes</h5>
                            <h2 class="mb-0"><?= $solved_notes ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="bi bi-check-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table id="notesTable" class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Letter / Memo No</th>
                <th>Department</th>
                <th>ICT Receiving Date</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?= htmlspecialchars($note['title']) ?></td>
                    <td><?= htmlspecialchars($note['content']) ?></td>
                    <td><?= htmlspecialchars($note['Letter_Memo_no']) ?></td>
                    <td><?= htmlspecialchars($note['department_name']) ?></td>
                    <td><?= htmlspecialchars($note['ICT_Receiving_Date']) ?></td>
                    <td><?= htmlspecialchars($note['employee_name']) ?></td>
                    <td>
                        <span class="badge bg-<?= $note['priority'] === 'High' ? 'danger' : ($note['priority'] === 'Medium' ? 'info' : 'secondary') ?>">
                            <?= $note['priority'] ?>
                        </span>
                    </td>
                    <td><?= $note['status'] ?></td>
                    <td>
                        <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="view_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<script>
    $(document).ready(function () {
        $('#notesTable').DataTable();
    });
</script>
</body>
</html>