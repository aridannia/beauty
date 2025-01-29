<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection (PDO for consistency)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=beauty", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id']; // Retrieve logged-in user's ID

// Fetch user's full name
$query = "SELECT Fullname FROM User WHERE User_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$fullname = $user['Fullname'] ?? 'User';

// Handle form submission for new enquiries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Validate input
    if (empty($message)) {
        $_SESSION['error'] = "Message cannot be empty.";
    } else {
        // Insert enquiry into the Enquiries table
        $query = "
            INSERT INTO Enquiries (User_id, Service_id, Message, Enquiry_status, Reply, Created_at)
            VALUES (:user_id, NULL, :message, 'Pending', '', NOW())
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $_SESSION['message'] = "Your enquiry has been submitted successfully.";
            } else {
                $_SESSION['error'] = "Failed to submit your enquiry. Please try again.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }

    // Redirect to avoid form resubmission
    header("Location: user_contactUs.php");
    exit();
}

// Handle deleting an enquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enquiry'])) {
    $enquiry_id = $_POST['enquiry_id'];

    // Delete the enquiry from the Enquiries table
    $query = "DELETE FROM Enquiries WHERE Enquiry_id = :enquiry_id AND User_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':enquiry_id', $enquiry_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Enquiry deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete enquiry.";
    }

    // Redirect to refresh the page
    header("Location: user_contactUs.php");
    exit();
}

// Fetch user's enquiries
$query = "SELECT * FROM Enquiries WHERE User_id = :user_id ORDER BY Created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
        <a href="user_dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="user_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="user_booking.php"><i class="fas fa-file-alt"></i> Appointment</a>
        <a href="user_contactUs.php"><i class="fa-solid fa-message"></i> Enquiry</a>
        <div style="margin-top: auto;">
            <p>Welcome, <?= htmlspecialchars($fullname); ?></p>
            <a href="logout.php" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="content">
        <h1>Contact Us</h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="section">
            <h4>Send Us a Message</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div> 
        
		<br>
        <div class="section">
            <h4>Your Enquiries</h4>
            <table class="table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Your Message</th>
                    <th>Status</th>
                    <th>Reply</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($enquiries as $enquiry): ?>
    <tr>
        <td><?= htmlspecialchars($enquiry['created_at']) ?></td>
        <td><?= htmlspecialchars($enquiry['message']) ?></td>
        <td><?= htmlspecialchars($enquiry['enquiry_status']) ?></td>
        <td>
            <!-- View Reply Button -->
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#replyModal<?= $enquiry['enquiry_id'] ?>">
                <i class="fa-regular fa-eye"></i>
            </button>

            <!-- Modal -->
            <div class="modal fade" id="replyModal<?= $enquiry['enquiry_id'] ?>" tabindex="-1" aria-labelledby="replyModalLabel<?= $enquiry['Enquiry_id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="replyModalLabel<?= $enquiry['enquiry_id'] ?>">Admin Reply</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?= htmlspecialchars($enquiry['reply'] ?: 'No reply yet') ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td>
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="enquiry_id" value="<?= $enquiry['enquiry_id'] ?>">
                <button type="submit" name="delete_enquiry" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
            </table>
        </div>
    </div>
</body>

</html>
