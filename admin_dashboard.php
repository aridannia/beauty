<?php
session_start();
include 'db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch total statistics
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM User")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM Appointment")->fetchColumn();
$totalPendingEnquiries = $pdo->query("SELECT COUNT(*) FROM Enquiries WHERE enquiry_status = 'Pending'")->fetchColumn();

// Fetch recent appointments
$recentAppointments = $pdo->query("
    SELECT a.appointment_id, u.fullname AS user_name, s.service_name, a.appointment_date, a.appointment_time, a.admin_status
    FROM Appointment a
    JOIN User u ON a.user_id = u.user_id
    JOIN Services s ON a.service = s.service_id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all services
$services = $pdo->query("SELECT * FROM Services")->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending enquiries
$pendingEnquiries = $pdo->query("
    SELECT e.enquiry_id, u.fullname AS customer_name, s.service_name, e.message, e.enquiry_status
    FROM Enquiries e
    JOIN User u ON e.user_id = u.user_id
    JOIN Services s ON e.service_id = s.service_id
    WHERE e.enquiry_status = 'Pending'
    ORDER BY e.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Insert predefined services if they don't already exist
$predefinedServices = [
    ['Facial Treatment', 'Rejuvenate your skin with our specialized facial treatment.', 90, 50],
    ['Hair Treatment', 'Restore shine and softness to your hair.', 180, 40],
    ['Massage Therapy', 'Relieve stress and rejuvenate your body with our massage therapy.', 90, 70],
    ['Medicure and Pedicure', 'Nail care services including trimming, shaping, and polishing.', 60, 40],
    ['Hydrotherapy', 'A therapeutic treatment involving water for pain relief and treatment.', 90, 40],
    ['Waxing', 'Hair removal from the root using warm wax.', 60, 40],
];

foreach ($predefinedServices as $service) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Services WHERE service_name = :service_name");
    $stmt->bindParam(':service_name', $service[0]);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO Services (service_name, description, duration, price, created_at)
            VALUES (:service_name, :description, :duration, :price, NOW())");
        $stmt->bindParam(':service_name', $service[0]);
        $stmt->bindParam(':description', $service[1]);
        $stmt->bindParam(':duration', $service[2]);
        $stmt->bindParam(':price', $service[3]);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }

        .navbar {
            background: linear-gradient(90deg, #FFDEE9, #B5FFFC);
        }

        .navbar img {
            height: 50px;
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            color: #555 !important;
            font-weight: 600;
        }

        .navbar-nav .nav-link:hover {
            color: #000 !important;
        }

        .dashboard-header {
            text-align: center;
            margin: 30px 0;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }

        .card h5 {
            font-weight: bold;
        }

        .dashboard-cards {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-add-service {
            background-color: #FFA07A;
            border: none;
        }

        .btn-add-service:hover {
            background-color: #FF7F50;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <img src="Image/threedewi.jpg" alt="Beauty Logo">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_customer_list.php">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_appointment.php">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_enquiry.php">Enquiries</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Centered Statistics -->
        <div class="dashboard-cards">
            <div class="card text-white bg-primary mb-3" style="width: 18rem;">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Customers</h5>
                    <p class="card-text"><?= htmlspecialchars($totalCustomers) ?></p>
                </div>
            </div>
            <div class="card text-white bg-success mb-3" style="width: 18rem;">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Appointments</h5>
                    <p class="card-text"><?= htmlspecialchars($totalAppointments) ?></p>
                </div>
            </div>
			<div class="card text-white bg-warning mb-3" style="width: 18rem;">
                <div class="card-body text-center">
            <h5 class="card-title">Pending Enquiries</h5>
            <p class="card-text"><?= htmlspecialchars($totalPendingEnquiries) ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h4>Recent Appointments</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>User</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAppointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars($appointment['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($appointment['user_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                                <td><?= htmlspecialchars($appointment['admin_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
		
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
