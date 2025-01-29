<?php
session_start();
include 'db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle adding a new customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone_number = trim($_POST['phone_number']);
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);

    if (empty($fullname) || empty($email) || empty($password) || empty($phone_number) || empty($gender) || empty($address)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO User (fullname, email, password, phone_number, gender, address, created_at) 
                               VALUES (:fullname, :email, :password, :phone_number, :gender, :address, NOW())");
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Customer added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add customer.";
        }
    }
}

// Handle updating a customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
    $user_id = $_POST['user_id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);

    // Add validation for missing fields
    if (empty($fullname) || empty($email) || empty($phone_number) || empty($address)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE User 
                                   SET fullname = :fullname, 
                                       email = :email, 
                                       phone_number = :phone_number, 
                                       address = :address 
                                   WHERE user_id = :user_id");
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Customer updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update customer.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch customer list with search functionality
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM User WHERE fullname LIKE :search OR email LIKE :search ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$searchTerm = "%$search%";
$stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Customer List</title>
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
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <img src="Image/threedewi.jpg" alt="Beauty Logo" style="height: 50px; margin-right: 10px;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
        <h2 class="mb-4">Customer List</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>

        <!-- Customer List Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= htmlspecialchars($customer['User_id']) ?></td>
                        <td><?= htmlspecialchars($customer['Fullname']) ?></td>
                        <td><?= htmlspecialchars($customer['Email']) ?></td>
                        <td><?= htmlspecialchars($customer['Phone_number']) ?></td>
                        <td><?= htmlspecialchars($customer['Address']) ?></td>
                        <td>
        <!-- Edit Modal Trigger -->
        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editCustomerModal<?= $customer['User_id'] ?>"><i class="fa-solid fa-user-pen"></i></button>
		<button type="submit" name="delete_customer" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>

        <!-- Edit Modal -->
        <div class="modal fade" id="editCustomerModal<?= $customer['User_id'] ?>" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Edit Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                        <form method="POST">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($customer['User_id']) ?>">
                    <div class="mb-3">
                        <label for="fullname<?= $customer['User_id'] ?>" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname<?= $customer['User_id'] ?>" name="fullname" value="<?= htmlspecialchars($customer['Fullname']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email<?= $customer['User_id'] ?>" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email<?= $customer['User_id'] ?>" name="email" value="<?= htmlspecialchars($customer['Email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number<?= $customer['User_id'] ?>" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number<?= $customer['User_id'] ?>" name="phone_number" value="<?= htmlspecialchars($customer['Phone_number']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address<?= $customer['User_id'] ?>" class="form-label">Address</label>
                        <textarea class="form-control" id="address<?= $customer['User_id'] ?>" name="address" rows="3" required><?= htmlspecialchars($customer['Address']) ?></textarea>
                    </div>
                    <button type="submit" name="update_customer" class="btn btn-primary w-100">Update Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
