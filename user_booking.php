<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "beauty");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; // Retrieve logged-in user's ID

// Fetch user's full name
$query = "SELECT Fullname FROM User WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$fullname = $user['Fullname'] ?? 'User'; // Default to "User"

// Fetch all appointments for the user
$appointments = [];
$searchDate = $_GET['search_date'] ?? null;
if ($searchDate) {
    $stmt = $conn->prepare("SELECT * FROM appointment WHERE user_id = ? AND appointment_date = ?");
    $stmt->bind_param("is", $user_id, $searchDate);
} else {
    $stmt = $conn->prepare("SELECT * FROM appointment WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Service mapping
$service_mapping = [
    1 => "Facial Treatment",
    2 => "Hair Spa",
    3 => "Massage Therapy",
    4 => "Medicure & Pedicure",
    5 => "Hydrotherapy",
    6 => "Waxing",
];

// Available time slots
$session_1_times = ["10:00", "11:00", "12:00"];
$session_2_times = ["14:00", "15:00", "16:00", "17:00", "18:00"];
$available_times = [];

// Get the booked time slots for the selected date
$booked_times = [];
if ($searchDate) {
    $stmt = $conn->prepare("SELECT appointment_time FROM appointment WHERE appointment_date = ?");
    $stmt->bind_param("s", $searchDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $booked_times[] = $row['appointment_time'];
    }
    $stmt->close();
}

// Filter available times
foreach ($session_1_times as $time) {
    if (!in_array($time, $booked_times)) {
        $available_times[] = $time;
    }
}
foreach ($session_2_times as $time) {
    if (!in_array($time, $booked_times)) {
        $available_times[] = $time;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_appointment'])) {
        $service = $_POST['service'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];

        if (in_array($appointment_time, $booked_times)) {
            echo "<script>alert('Selected time slot is already taken!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointment (user_id, service, appointment_date, appointment_time, admin_status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("isss", $user_id, $service, $appointment_date, $appointment_time);

            if ($stmt->execute()) {
                echo "<script>alert('Appointment booked successfully!');</script>";
            } else {
                echo "<script>alert('Error booking appointment: {$stmt->error}');</script>";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_appointment'])) {
        $appointment_id = $_POST['appointment_id'];

        $stmt = $conn->prepare("DELETE FROM appointment WHERE appointment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $appointment_id, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Appointment deleted successfully!');</script>";
        } else {
            echo "<script>alert('Error deleting appointment: {$stmt->error}');</script>";
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    $service = $_POST['service'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Check if the selected time is already taken
    $stmt = $conn->prepare("SELECT * FROM appointment WHERE appointment_date = ? AND appointment_time = ? AND appointment_id != ?");
    $stmt->bind_param("ssi", $appointment_date, $appointment_time, $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('The selected time is already taken. Please choose another time.');</script>";
    } else {
        // Update the appointment
        $stmt = $conn->prepare("UPDATE appointment SET service = ?, appointment_date = ?, appointment_time = ? WHERE appointment_id = ?");
        $stmt->bind_param("issi", $service, $appointment_date, $appointment_time, $appointment_id);

        if ($stmt->execute()) {
            echo "<script>alert('Appointment updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating appointment: {$stmt->error}');</script>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #FFDEE9, #B5FFFC); 
            color: #555;
            display: flex;
            flex-direction: column;
            padding: 20px;
            border-right: 2px solid #FFF;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar h3 {
            color: #FF69B4; 
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar img {
            width: 150px; 
            height: auto; 
            margin: 0 auto 20px;
            display: block;
        }

        .sidebar a {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #FFD1DC;
            color: #000;
        }

        .sidebar .btn-warning {
            margin-top: 20px;
            background: #FFD1DC;
            border: none;
            color: #555;
            font-weight: bold;
        }

        .sidebar .btn-warning:hover {
            background: #FFC1C1; 
            color: #000;
        }

        /* Content Styling */
        .content {
            flex-grow: 1;
            background: #FFF5F7; 
            padding: 20px;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #FF69B4;
            text-align: center;
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            background: #FFF; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: #FFDEE9; 
            color: #FF69B4;
            font-weight: bold;
            font-size: 1.2rem;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .card-body {
            padding: 15px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="Image/threedewi.jpg" alt="Beauty Logo">
        <a href="user_dashboard.php"><i class="fas fa-home"></i>Home</a>
        <a href="user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="user_booking.php"><i class="fas fa-file-alt"></i>Appointment</a>
        <a href="user_contactUs.php"><i class="fa-solid fa-message"></i>Enquiry</a>
        <div style="margin-top: auto;">
            <p>Welcome, <?= htmlspecialchars($fullname); ?></p>
            <a href="logout.php" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </div>

    <div class="content">
        <h1>Book an Appointment</h1>

        <!-- Add Appointment Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="service" class="form-label">Select Service</label>
                <select class="form-select" id="service" name="service" required>
                    <option value="" disabled selected>Select a service</option>
                    <option value="1">Facial Treatment</option>
                    <option value="2">Hair Spa</option>
                    <option value="3">Massage Therapy</option>
                    <option value="4">Medicure & Pedicure</option>
                    <option value="5">Hydrotherapy</option>
                    <option value="6">Waxing</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Select Date</label>
                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
            </div>
            <div class="mb-3">
                <label for="appointment_time" class="form-label">Select Time</label>
                <select class="form-select" id="appointment_time" name="appointment_time" required>
                    <option value="" disabled selected>Select a time</option>
                    <?php foreach ($available_times as $time): ?>
                        <option value="<?= $time ?>"><?= $time ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_appointment" class="btn btn-primary w-100">Book Appointment</button>
        </form>

        <!-- Appointment List -->
        <h2 class="mt-5">My Appointments</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['appointment_id']) ?></td>
                        <td><?= htmlspecialchars($service_mapping[$appointment['service']] ?? 'Unknown Service') ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                        <td>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                <input type="hidden" name="new_service" value="<?= $appointment['service'] ?>">
                                <input type="hidden" name="new_appointment_time" value="<?= $appointment['appointment_time'] ?>">
                                <button type="button" class="btn btn-sm btn-secondary edit-button" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $appointment['appointment_id'] ?>" data-service="<?= $appointment['service'] ?>" data-date="<?= $appointment['appointment_date'] ?>" data-time="<?= $appointment['appointment_time'] ?>"><i class="fa-regular fa-pen-to-square"></i></button>
                                <button type="submit" name="delete_appointment" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
	
	<!-- Edit Appointment Modal -->
<div class="modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" id="edit_appointment_id" name="appointment_id">
                    <div class="mb-3">
                        <label for="edit_service" class="form-label">Select Service</label>
                        <select class="form-select" id="edit_service" name="service" required>
                            <option value="1">Facial Treatment</option>
                            <option value="2">Hair Spa</option>
                            <option value="3">Massage Therapy</option>
                            <option value="4">Medicure & Pedicure</option>
                            <option value="5">Hydrotherapy</option>
                            <option value="6">Waxing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Select Date</label>
                        <input type="date" class="form-control" id="edit_date" name="appointment_date" required>
                    </div>
					<div class="mb-3">
                        <label for="edit_time" class="form-label">Select Time</label>
                        <select class="form-select" id="edit_time" name="appointment_time" required>
                        <option value="" disabled selected>Select a time</option>
                    <?php foreach ($available_times as $time): ?>
                        <option value="<?= $time ?>"><?= $time ?></option>
                    <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="update_appointment" class="btn btn-primary">Update Appointment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const editButtons = document.querySelectorAll('.edit-button');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const appointmentId = button.getAttribute('data-id');
            const service = button.getAttribute('data-service');
            const date = button.getAttribute('data-date');
            const time = button.getAttribute('data-time');
            
            // Set the values in the modal
            document.getElementById('edit_appointment_id').value = appointmentId;
            document.getElementById('edit_service').value = service;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_time').value = time;
        });
    });
</script>

</body>
</html>
