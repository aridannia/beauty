<?php include('DB.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
        body {
            background: url('image/login3.jpg') no-repeat center center fixed; 
            background-size: cover; /* Ensures the image covers the entire background */
            color: white;
            font-family: Arial, sans-serif;
            display: flex; /* Use Flexbox to center content */
            justify-content: center; /* Horizontally center */
            align-items: center; /* Vertically center */
            height: 100vh; /* Set height to fill the viewport */
            margin: 0; /* Remove default margin */
        }
        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Adds a semi-transparent black background */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Adds a shadow for a lifted effect */
            max-width: 400px; /* Limits the width of the form */
            width: 100%; /* Ensures the container is responsive */
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        h2 {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>
        <form action="LOGIN.PHP" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="login">Login</button>
			<a href="register.php">Don't have an account? Register</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM User WHERE Email = :email AND Password = :password");
    $stmt->execute(['email' => $email, 'password' => $password]);
    $user = $stmt->fetch();

    if ($user) {
        // Start session for logged-in user
        session_start();
        $_SESSION['user_id'] = $user['User_id'];
        echo "<script>alert('Login successful!'); window.location = 'user_dashboard.php';</script>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Admin WHERE Email = :email AND Password = :password");
        $stmt->execute(['email' => $email, 'password' => $password]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Start session for logged-in admin
            session_start();
            $_SESSION['admin_id'] = $admin['admin_id'];
            echo "<script>alert('Admin Login successful!'); window.location = 'admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Invalid credentials.');</script>";
        }
    }
}
?>
