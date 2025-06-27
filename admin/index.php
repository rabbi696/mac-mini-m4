    <?php
    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // Fetch all software from the database using a prepared statement
    $sql = "SELECT * FROM software ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Index: Prepare failed: " . $conn->error);
        die("An error occurred. Please try again later.");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Software Downloads for Mac Mini M4</title>
        <style>
            /* Styles omitted for brevity - consider moving to external CSS */
            body {
                background-color: #212529;
                color: #ffffff;
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
            }

            /* Header Styles */
            header {
                background-color: #333;
                padding: 20px 0;
                text-align: center;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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

            /* Container Styles */
            .container {
                width: 80%;
                margin: 50px auto;
                text-align: center;
            }
            h1 {
                color: #62a92b;
                font-size: 36px;
                margin-bottom: 30px;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            .software-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 30px;
            }
            .software-item {
                background-color: #333;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .software-item:hover {
                transform: translateY(-10px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            }
            .software-item p {
                font-size: 18px;
                margin: 10px 0;
            }
            .download-btn {
                background-color: #62a92b;
                color: #ffffff;
                font-size: 16px;
                padding: 12px 25px;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s, transform 0.3s;
            }
            .download-btn:hover {
                background-color: #4e8b1f;
                transform: scale(1.05);
            }
            .software-item .version {
                color: #bbbbbb;
                font-style: italic;
            }
            .software-item .name {
                font-weight: bold;
                color: #62a92b;
            }
            .space-between {
                margin-bottom: 15px;
            }

            /* Footer */
            footer {
                background-color: #333;
                color: #ffffff;
                padding: 20px 0;
                text-align: center;
                position: relative;
                bottom: 0;
                width: 100%;
            }
            footer a {
                color: #62a92b;
                text-decoration: none;
                margin: 0 10px;
            }
            footer a:hover {
                text-decoration: underline;
            }
            /* Adding space before footer */
            .footer-space {
                margin-bottom: 50px; /* Adjust this value to increase or decrease the space before footer */
            }
        </style>
    </head>
    <body>

    <!-- Header -->
    <header>
        <nav>
            <a href="https://mac.golamrabbi.dev">Home</a>
            <a href="https://mac.golamrabbi.dev/dmca.html">DMCA</a>
            <a href="https://mac.golamrabbi.dev/contact-us.html">Contact Us</a>
        </nav>
    </header>

    <div class="container">
        <!-- Software List -->
        <h1>Software for "Mac Mini M4"</h1>
        <div class="software-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='software-item'>";
                    echo "<p class='name'>" . htmlspecialchars($row['name']) . "</p>";
                    echo "<p class='version version-spacing'>Version: " . htmlspecialchars($row['version']) . "</p>";
                    echo "<br>";
                    echo "<a href='" . htmlspecialchars($row['download_link']) . "' class='download-btn' download target='_blank'>Download Now</a>";
                    echo "<br>";
                    echo "</div>";
                }
            } else {
                echo "<p>No software available</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer Menu -->
    <footer>
        <p>&copy; 2025 Golam Rabbi. All Rights Reserved.</p>
        <a href="https://mac.golamrabbi.dev/terms-of-service.html">Terms of Service</a>
        <a href="https://mac.golamrabbi.dev/privacy-policy.html">Privacy Policy</a>
        <a href="https://mac.golamrabbi.dev/dmca.html">DMCA</a>
        <a href="https://mac.golamrabbi.dev/contact-us.html">Contact Us</a>
    </footer>

    </body>
    </html>

    <?php
    $stmt->close();
    $conn->close();
    ?>
    