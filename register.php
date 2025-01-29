<?php include('DB.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
	body {
            background: url('image/login3.jpg') no-repeat center center fixed; 
            background-size: cover; 
            color: white;
            font-family: Arial, sans-serif;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .container {
            background-color: rgba(0, 0, 0, 0.7); 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            max-width: 400px; 
            width: 100%; 
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
        <h2>Register</h2>
        <form action="REGISTER.php" method="POST">
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="mb-3">
                <label for="is_admin" class="form-label">Register as Admin</label>
                <input type="checkbox" id="is_admin" name="is_admin"> Yes
            </div>
            <div class="mb-3" id="admin-code" style="display:none;">
                <label for="admin_code" class="form-label">Admin Private Code</label>
                <input type="text" class="form-control" id="admin_code" name="admin_code">
            </div>
            <button type="submit" class="btn btn-primary" name="register">Register</button>
			<a href="login.php">Already have an account? Login</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('is_admin').addEventListener('change', function() {
            document.getElementById('admin-code').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>

<?php
if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone_number = $_POST['phone_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $admin_code = $_POST['admin_code'] ?? '';

    if ($is_admin && $admin_code !== 'BeautyWellness2025') {
        echo "<script>alert('Invalid admin code.');</script>";
    } else {
        $created_at = date("Y-m-d H:i:s");
        if ($is_admin) {
            $query = "INSERT INTO Admin (Fullname, Email, Password, Phone_number, Created_at) 
                      VALUES ('$fullname', '$email', '$password', '$phone_number', '$created_at')";
        } else {
            $query = "INSERT INTO User (Fullname, Email, Password, Phone_number, Gender, Address, Created_at) 
                      VALUES ('$fullname', '$email', '$password', '$phone_number', '$gender', '$address', '$created_at')";
        }
        $pdo->exec($query);
        echo "<script>alert('Registration successful!');</script>";
    }
}
?>
