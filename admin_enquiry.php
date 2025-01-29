<?php
session_start();
include 'db.php'; 

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle replying to an enquiry and updating status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_enquiry'])) {
    $enquiry_id = $_POST['enquiry_id'];
    $reply = $_POST['reply'];
    $status = $_POST['status'] ?? null;

    if (!$status) {
        $_SESSION['error'] = "Please select a status.";
        header("Location: admin_enquiry.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE Enquiries 
            SET Reply = :reply, Enquiry_status = :status, Updated_at = NOW() 
            WHERE Enquiry_id = :enquiry_id
        ");
        $stmt->bindParam(':reply', $reply);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':enquiry_id', $enquiry_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Enquiry replied and status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to reply to enquiry or update status.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    header("Location: admin_enquiry.php");
    exit();
}

// Handle deleting an enquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enquiry'])) {
    $enquiry_id = $_POST['enquiry_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM Enquiries WHERE Enquiry_id = :enquiry_id");
        $stmt->bindParam(':enquiry_id', $enquiry_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Enquiry deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete enquiry.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    header("Location: admin_enquiry.php");
    exit();
}

// Handle searching for enquiries
$search = $_GET['search'] ?? '';
$query = "
    SELECT e.Enquiry_id, u.Fullname, u.Email, u.Phone_number, e.Message, e.Reply, 
           e.Enquiry_status, e.Created_at 
    FROM Enquiries e
    JOIN User u ON e.User_id = u.User_id
    WHERE u.Fullname LIKE :search OR u.Email LIKE :search
    ORDER BY e.Created_at DESC
";
$stmt = $pdo->prepare($query);
$searchTerm = "%$search%";
$stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
$stmt->execute();
$enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Enquiries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .navbar img {
            height: 60px; 
            margin-right: 10px;
        }
        .navbar {
            background: linear-gradient(90deg, #FFDEE9, #B5FFFC);
        }
        .navbar-nav .nav-link {
            color: #555 !important;
            font-weight: 600;
        }
        .navbar-nav .nav-link:hover {
            color: #000 !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <img src="Image/threedewi.jpg" alt="Beauty Logo">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_customer_list.php">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_appointment.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_enquiry.php">Enquiries</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Manage Enquiries</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>

        <!-- Enquiries Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">Enquiries</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Enquiry ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($enquiries): ?>
                            <?php foreach ($enquiries as $enquiry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($enquiry['Enquiry_id']) ?></td>
                                    <td><?= htmlspecialchars($enquiry['Fullname']) ?></td>
                                    <td><?= htmlspecialchars($enquiry['Email']) ?></td>
                                    <td><?= htmlspecialchars($enquiry['Phone_number']) ?></td>
                                    <td><?= htmlspecialchars($enquiry['Enquiry_status']) ?></td>
                                    <td>
                                        <!-- View Enquiry Message Modal Trigger -->
    <button class="btn btn-secondary btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#viewMessageModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
        <i class="fa-regular fa-comments"></i>
    </button>

    <!-- View Enquiry Message Modal -->
    <div class="modal fade" id="viewMessageModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         tabindex="-1" aria-labelledby="viewMessageModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMessageModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
                        Enquiry Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= nl2br(htmlspecialchars($enquiry['Message'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- View Reply Modal Trigger -->
    <button class="btn btn-secondary btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#viewReplyModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
        <i class="fa-solid fa-street-view"></i>
    </button>

    <!-- View Reply Modal -->
    <div class="modal fade" id="viewReplyModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         tabindex="-1" aria-labelledby="viewReplyModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReplyModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
                        Reply to Enquiry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= $enquiry['Reply'] ? nl2br(htmlspecialchars($enquiry['Reply'])) : 'No reply yet.' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal Trigger -->
    <button class="btn btn-info btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#replyModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
        <i class="fa-solid fa-reply"></i>
    </button>

    <!-- Reply Modal (Already Present) -->
    <div class="modal fade" id="replyModal<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         tabindex="-1" aria-labelledby="replyModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">Reply to Enquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="enquiry_id" 
                               value="<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
                        <div class="mb-3">
                            <label for="reply" class="form-label">Your Reply</label>
                            <textarea class="form-control" name="reply" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Update Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Pending" 
                                    <?= $enquiry['Enquiry_status'] === 'Pending' ? 'selected' : '' ?>>
                                    Pending
                                </option>
                                <option value="Done" 
                                    <?= $enquiry['Enquiry_status'] === 'Done' ? 'selected' : '' ?>>
                                    Done
                                </option>
                            </select>
                        </div>
                        <button type="submit" name="reply_enquiry" class="btn btn-primary w-100">
                            Send Reply & Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form method="POST" style="display:inline;">
        <input type="hidden" name="enquiry_id" value="<?= htmlspecialchars($enquiry['Enquiry_id']) ?>">
        <button type="submit" name="delete_enquiry" class="btn btn-danger btn-sm" 
                onclick="return confirm('Are you sure you want to delete this enquiry?');">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    </form>
</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No enquiries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
