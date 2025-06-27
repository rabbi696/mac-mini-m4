<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: #212529;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            padding: 20px 0;
            text-align: center;
        }
        header a {
            color: #62a92b;
            text-decoration: none;
            margin: 0 20px;
            font-size: 18px;
            font-weight: bold;
        }
        header a:hover {
            text-decoration: underline;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            text-align: center;
        }
        h1 {
            color: #62a92b;
            font-size: 36px;
            margin-bottom: 30px;
        }
        .form-container {
            background-color: #333;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 30px;
            width: 50%;
            margin-left: auto;
            margin-right: auto;
        }
        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #444;
            color: #fff;
        }
        .form-container button {
            background-color: #62a92b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #4e8b1f;
        }
        footer {
            background-color: #333;
            color: #ffffff;
            padding: 20px 0;
            text-align: center;
        }
        footer a {
            color: #62a92b;
            text-decoration: none;
            margin: 0 10px;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <a href="https://mac.golamrabbi.dev">Home</a>
    <a href="logout.php">Logout</a>
</header>

<div class="container">
    <h1>Admin Dashboard</h1>

    <!-- Add New Software Form -->
    <div class="form-container">
        <h2>Add New Software</h2>
        <form action="process_add_software.php" method="POST">
            <label for="software-name">Software Name</label>
            <input type="text" id="software-name" name="software-name" required>

            <label for="software-version">Version</label>
            <input type="text" id="software-version" name="software-version">

            <label for="download-link">Download Link</label>
            <input type="url" id="download-link" name="download-link" required>

            <button type="submit">Add Software</button>
        </form>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2025 Golam Rabbi. All Rights Reserved.</p>
    <a href="https://mac.golamrabbi.dev/terms-of-service.html">Terms of Service</a>
    <a href="https://mac.golamrabbi.dev/privacy-policy.html">Privacy Policy</a>
    <a href="https://mac.golamrabbi.dev/dmca.html">DMCA</a>
    <a href="https://mac.golamrabbi.dev/contact-us.html">Contact Us</a>
</footer>

</body>
</html>
