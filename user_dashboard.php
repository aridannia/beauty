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

// Default to "User" if the full name is not found
$fullname = $user['Fullname'] ?? 'User';

// Fetch all services
$servicesQuery = "SELECT * FROM Services";
$servicesResult = $conn->query($servicesQuery);
$services = $servicesResult->fetch_all(MYSQLI_ASSOC);

// Business-specific information
$aboutus = "Welcome to <strong>Three Dewi Beauty</strong>, your premier destination for rejuvenation and self-care. At Three Dewi Beauty, we believe that beauty and wellness go hand in hand. Our expert team offers a wide range of personalized treatments designed to enhance your natural beauty while promoting relaxation and well-being.<br><br>
From facial treatments and massage therapy to nail care and hydrotherapy, we provide a tranquil space where you can unwind and rediscover your best self.<br><br>
<strong>Services Offered:</strong><ul>";
foreach ($services as $service) {
    $aboutus .= "<li><strong>" . htmlspecialchars($service['service_name']) . ":</strong> " . htmlspecialchars($service['description']) . " (Duration: " . htmlspecialchars($service['duration']) . " minutes) - RM" . number_format($service['price'], 2) . "</li>";
}
$aboutus .= "</ul>";
$rules = "<ul>
<li><strong>Plan Ahead:</strong> Book your treatment at least 24 hours in advance.</li>
<li><strong>Arrive on Time:</strong> Arrive 10 minutes before your appointment.</li>
<li><strong>Cancellation Policy:</strong> Cancel at least 12 hours prior to avoid charges.</li>
<li><strong>Hygiene Protocol:</strong> Maintain proper hygiene before treatments.</li>
<li><strong>Respect Others:</strong> Enjoy a quiet and serene environment during your visit.</li>
</ul>";
$contactInfo = "For more information or assistance, please contact us:<br>
<strong>Email:</strong> support@dwidewibeauty.com<br>
<strong>Phone:</strong> +603 4567 8900<br>
<strong>Operating Hours:</strong> Mon-Sat, 9 AM - 7 PM";

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
            width: 400px;
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
            width: 150px; /* logo width */
            height: auto; /* Maintain aspect ratio */
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
        <h1>Welcome to Three Dewi Beauty</h1>

        <!-- About Us -->
        <div class="card">
            <div class="card-header">About Us</div>
            <div class="card-body">
                <?= $aboutus; ?>
            </div>
        </div>

        <!-- Booking Guidelines -->
        <div class="card">
            <div class="card-header">How to Book Your Treatment</div>
            <div class="card-body">
                <?= $rules; ?>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">Contact Information</div>
            <div class="card-body">
                <?= $contactInfo; ?>
            </div>
        </div>
    </div>
</body>
</html>
