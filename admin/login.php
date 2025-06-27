<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to fetch the admin user from the database
    $sql = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password using bcrypt
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: index.php");  // Redirect to the admin dashboard
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

ini_set('display_errors', 1);
error_reporting(E_ALL);

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            background-color: #212529;
            font-family: Arial, sans-serif;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: #62a92b;
            font-size: 32px;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #444;
            background-color: #555;
            color: #fff;
            font-size: 16px;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #62a92b;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #62a92b;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #4e8b1f;
        }
        .error-message {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>Admin Login</h1>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>

        <?php
        // Display error message if login fails
        if (isset($error)) {
            echo "<p class='error-message'>$error</p>";
        }
        ?>
    </form>
</div>

</body>
</html>
