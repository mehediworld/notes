<?php
// add_note.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
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
    $employee_stmt = $conn->query("SELECT id, name, email FROM employee ORDER BY name ASC");
    $employees = $employee_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching employees: " . $e->getMessage());
    $employees = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $department_id = $_POST['department_id'];
    $letter_memo_no = $_POST['letter_memo_no'];
    $title = $_POST['title'];
    $ict_receiving_date = $_POST['ict_receiving_date'];
    $content = $_POST['content'];
    $assigned_to = $_POST['assigned_to'];
    $comment = $_POST['comment'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];

    // Get the assigned employee's email
    $employee_email = '';
    $employee_name = '';
    foreach ($employees as $emp) {
        if ($emp['id'] == $assigned_to) {
            $employee_email = $emp['email'];
            $employee_name = $emp['name'];
            break;
        }
    }

    // Insert the note
    $stmt = $conn->prepare("INSERT INTO notes (user_id, department_id, Letter_Memo_no, title, ICT_Receiving_Date, content, Assigned_to, Comment, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $department_id, $letter_memo_no, $title, $ict_receiving_date, $content, $assigned_to, $comment, $priority, $status]);

    // Send email notification if employee has an email
    if (!empty($employee_email)) {
        // Get department name
        $dept_name = '';
        foreach ($departments as $dept) {
            if ($dept['id'] == $department_id) {
                $dept_name = $dept['name'];
                break;
            }
        }

        $subject = "New Note Assigned: " . htmlspecialchars($title);
        $message = "
        <html>
        <head>
            <title>New Note Assignment</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Note Assigned</h2>
                </div>
                <div class='content'>
                    <p>Hello $employee_name,</p>
                    <p>You have been assigned a new note with the following details:</p>
                    
                    <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                    <p><strong>Department:</strong> " . htmlspecialchars($dept_name) . "</p>
                    <p><strong>Letter/Memo No:</strong> " . htmlspecialchars($letter_memo_no) . "</p>
                    <p><strong>Priority:</strong> " . htmlspecialchars($priority) . "</p>
                    <p><strong>Status:</strong> " . htmlspecialchars($status) . "</p>
                    <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($content)) . "</p>
                    <p><strong>Comment:</strong><br>" . nl2br(htmlspecialchars($comment)) . "</p>
                    
                    <p>Please log in to the system to view and take action on this note.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // To send HTML mail, the Content-type header must be set
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        
        // Additional headers
        $headers .= "From: Note Tracker System <noreply@aibl.com.bd>\r\n";
        
        // Send email
        mail($employee_email, $subject, $message, $headers);
    }

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">Add Note</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" name="department_id" id="department_id" required>
                                <option value="">Select Department</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept['id']); ?>">
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
                            <input type="text" class="form-control" name="letter_memo_no" required>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title / Subject</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="ict_receiving_date" class="form-label">ICT Receiving Date</label>
                            <input type="date" class="form-control" name="ict_receiving_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Description</label>
                            <textarea class="form-control" name="content"></textarea>
                        </div>
                       
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigned To</label>
                            <select class="form-select" name="assigned_to" id="assigned_to" required>
                                <option value="">Select Employee</option>
                                <?php if (!empty($employees)): ?>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                            <?php echo htmlspecialchars($emp['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No employees found</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea class="form-control" name="comment" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Solved">Solved</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>