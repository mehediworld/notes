<?php
// view_note.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$note_id = $_GET['id'];

// Fetch the note with department name
$stmt = $conn->prepare("SELECT n.*, d.name as department_name, e.id as employee_id, e.name as employee_name
                        FROM notes n 
                        LEFT JOIN departments d ON n.department_id = d.id 
                        LEFT JOIN employee e ON n.Assigned_to = e.id
                        WHERE n.id = ? AND n.user_id = ?");
$stmt->execute([$note_id, $user_id]);
$note = $stmt->fetch();

if (!$note) {
    echo "Note not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }
        .note-detail {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
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
        <h2>View Note</h2>
        <div>
            <a href="edit_note.php?id=<?= $note_id ?>" class="btn btn-primary">Edit</a>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
    
    <div class="note-detail">
		<div class="row mb-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <span class="detail-label">Title / Subject:</span>
                    <div><?= htmlspecialchars($note['title']) ?></div>
                </div>
            </div>
		</div>	
        <div class="row mb-3">
            
            <div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Department:</span>
                    <div><?= htmlspecialchars($note['department_name'] ?? 'Not assigned') ?></div>
                </div>
            </div>
			<div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Letter / Memo No:</span>
                    <div><?= htmlspecialchars($note['Letter_Memo_no'] ?? 'Not assigned') ?></div>
                </div>
            </div>
			<div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Assigned To:</span>
                    <div><?= htmlspecialchars($note['employee_name']) ?></div>
                </div>
            </div>
        </div>
		<div class="row mb-3">
            
            <div class="col-md-4">
                <div class="mb-3">
                   <span class="detail-label">ICT Receiving Date:</span>
                    <div><?= htmlspecialchars($note['ICT_Receiving_Date']) ?></div>
                </div>
            </div>
			<div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Status:</span>
                    <div>
                        <span class="badge 
                            <?php 
                                switch($note['status']) {
                                    case 'Open': echo 'bg-primary'; break;
                                    case 'In Progress': echo 'bg-secondary'; break;
                                    default: echo 'bg-success';
                                }
                            ?>">
                            <?= htmlspecialchars($note['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
			<div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Priority:</span>
                    <div>
                        <span class="badge 
                            <?php 
                                switch($note['priority']) {
                                    case 'High': echo 'bg-danger'; break;
                                    case 'Medium': echo 'bg-warning text-dark'; break;
                                    default: echo 'bg-info text-dark';
                                }
                            ?>">
                            <?= htmlspecialchars($note['priority']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            
            
            <div class="col-md-4">
                <div class="mb-3">
                    <span class="detail-label">Created:</span>
                    <div><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></div>
                </div>
            </div>
			
        </div>
		
        
        <div class="mb-3">
            <span class="detail-label">Description:</span>
            <div class="border p-3 bg-white rounded"><?= nl2br(htmlspecialchars($note['content'])) ?></div>
        </div>
		<div class="mb-3">
            <span class="detail-label">Comment:</span>
            <div class="border p-3 bg-white rounded"><?= nl2br(htmlspecialchars($note['Comment'])) ?></div>
        </div>
        
        <?php if (!empty($note['updated_at']) && $note['updated_at'] != $note['created_at']): ?>
        <div class="text-muted small">
            Last updated: <?= date('M j, Y g:i A', strtotime($note['updated_at'])) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>