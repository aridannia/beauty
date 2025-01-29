<?php
session_start();
include 'db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch appointments (default or based on search)
$appointments = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_appointments'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $stmt = $pdo->prepare("
        SELECT a.appointment_id, u.fullname AS user_name, s.service_name, 
               a.appointment_date, a.appointment_time, a.customer_status, 
               a.admin_status, a.invoices_amount, a.invoices_status
        FROM Appointment a
        JOIN User u ON a.user_id = u.user_id
        JOIN Services s ON a.service = s.service_id
        WHERE a.appointment_date BETWEEN :start_date AND :end_date
        ORDER BY a.appointment_date DESC
    ");
    $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $appointments = $pdo->query("
        SELECT a.appointment_id, u.fullname AS user_name, s.service_name, 
               a.appointment_date, a.appointment_time, a.customer_status, 
               a.admin_status, a.invoices_amount, a.invoices_status
        FROM Appointment a
        JOIN User u ON a.user_id = u.user_id
        JOIN Services s ON a.service = s.service_id
        ORDER BY a.appointment_date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Handle adding new appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $userId = $_POST['user_id'];
    $serviceId = $_POST['service_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];

    $stmt = $pdo->prepare("
        INSERT INTO Appointment (user_id, service, appointment_date, appointment_time, customer_status, admin_status, created_at) 
        VALUES (:user_id, :service_id, :appointment_date, :appointment_time, 'Pending', 'Pending', NOW())
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
    $stmt->bindParam(':appointment_date', $appointmentDate, PDO::PARAM_STR);
    $stmt->bindParam(':appointment_time', $appointmentTime, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add appointment.";
    }
}

// Handle admin status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $admin_status = $_POST['admin_status'];

    $stmt = $pdo->prepare("
        UPDATE Appointment 
        SET admin_status = :admin_status 
        WHERE appointment_id = :appointment_id
    ");
    $stmt->bindParam(':admin_status', $admin_status);
    $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment status.";
    }
}

// Handle deleting appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment'])) {
    $appointmentId = $_POST['appointment_id'];

    $stmt = $pdo->prepare("DELETE FROM Appointment WHERE appointment_id = :appointment_id");
    $stmt->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete appointment.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
	    .navbar img {
            height: 60px; /* Adjust logo height */
            margin-right: 10px;
        }

        .navbar {
            background: linear-gradient(90deg, #FFDEE9, #B5FFFC);
        }

        .navbar-brand img {
            height: 50px;
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
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <img src="Image/threedewi.jpg" alt="Logo" class="navbar-brand">
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
        <h2>Manage Appointments</h2>

        <!-- Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Search -->
        <form method="POST" class="row mb-4">
            <div class="col-md-5">
                <input type="date" class="form-control" name="start_date" placeholder="Start Date" required>
            </div>
            <div class="col-md-5">
                <input type="date" class="form-control" name="end_date" placeholder="End Date" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="search_appointments" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>

        <!-- Appointment Table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= $appointment['appointment_id'] ?></td>
                        <td><?= $appointment['user_name'] ?></td>
                        <td><?= $appointment['service_name'] ?></td>
                        <td><?= $appointment['appointment_date'] ?></td>
                        <td><?= $appointment['appointment_time'] ?></td>
                        <td><?= $appointment['admin_status'] ?: 'Pending' ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                <select name="admin_status" class="form-select-sm">
								    <option value="pending" <?= $appointment['admin_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $appointment['admin_status'] === 'confirmed' ? 'selected' : '' ?>>Confirm</option>
									<option value="cancelled" <?= $appointment['admin_status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                                    <option value="completed" <?= $appointment['admin_status'] === 'completed' ? 'selected' : '' ?>>Complete</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                <button type="submit" name="delete_appointment" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
