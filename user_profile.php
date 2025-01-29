<?php
// USER_PROFILE.PHP

session_start();
include 'db.php';

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

// Default to "User" if the full name is not found
$fullname = $user['Fullname'] ?? 'User';

// Fetch user data
$query = "SELECT * FROM User WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle case where user is not found
if (!$user) {
    echo "<script>alert('User not found. Please contact support.'); window.location.href = 'login.php';</script>";
    exit();
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
        <h1 class="text-center">My Profile</h1>
        <div class="row mt-5">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>User Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['Fullname'] ?? 'N/A') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['Email'] ?? 'N/A') ?></p>
                        <p><strong>Phone Number:</strong> <?= htmlspecialchars($user['Phone_number'] ?? 'N/A') ?></p>
                        <p><strong>Gender:</strong> <?= htmlspecialchars($user['Gender'] ?? 'N/A') ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($user['Address'] ?? 'N/A') ?></p>
                        <a href="edit_userprofile.php" class="btn btn-secondary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
