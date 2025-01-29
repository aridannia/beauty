<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Beauty and Wellness System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
        body {
            background: url('image/logout2.jpg') no-repeat center center fixed; 
            background-size: cover; 
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-box {
            text-align: center;
            max-width: 400px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
        }
        .message-box h1 {
            font-size: 1.5rem;
        }
        .message-box p {
            font-size: 1rem;
        }
    </style>
</head>
<body>
        <script>
        // Redirect to login page after 5 seconds
        setTimeout(() => {
            window.location.href = "login.php";
        }, 5000);
    </script>
</head>
<body>
    <div class="message-box">
        <h1>Three Beauty!</h1>
		<br>
        <p>We appreciate your visit! Your satisfaction is our priority. Thank you for trusting us with your beauty needs. We hope you enjoyed your beauty experience and look forward to seeing you again soon.</p>
        <p>You will be redirected to the login page shortly...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
