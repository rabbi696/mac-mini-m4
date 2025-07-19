    <?php
    session_start();

    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Collect username and password from the form
        $username = trim($_POST['username']);
        $password = $_POST['password']; // Password is not trimmed as it might contain leading/trailing spaces

        // Query to fetch the admin user from the database
        $sql = "SELECT * FROM admin_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Login: Prepare failed: " . $conn->error);
            die("An error occurred. Please try again later.");
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify password using bcrypt
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                session_regenerate_id(true); // Prevent session fixation
                header("Location: dashboard.php");  // Redirect to the admin dashboard
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

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                background: linear-gradient(135deg, #0f1419 0%, #1a1f2e 25%, #212529 50%, #2d3436 75%, #1e293b 100%);
                background-size: 400% 400%;
                animation: gradientShift 15s ease infinite;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #ffffff;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                overflow: hidden;
            }
            
            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            .background-animation {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                z-index: 1;
            }
            
            .floating-shapes {
                position: absolute;
                width: 100%;
                height: 100%;
            }
            
            .shape {
                position: absolute;
                background: rgba(98, 169, 43, 0.1);
                border-radius: 50%;
                animation: float 6s ease-in-out infinite;
            }
            
            .shape:nth-child(1) {
                width: 80px;
                height: 80px;
                left: 10%;
                top: 20%;
                animation-delay: 0s;
            }
            
            .shape:nth-child(2) {
                width: 120px;
                height: 120px;
                right: 10%;
                bottom: 20%;
                animation-delay: 2s;
            }
            
            .shape:nth-child(3) {
                width: 60px;
                height: 60px;
                left: 70%;
                top: 10%;
                animation-delay: 4s;
            }
            
            .shape:nth-child(4) {
                width: 100px;
                height: 100px;
                left: 20%;
                bottom: 10%;
                animation-delay: 1s;
            }
            
            @keyframes float {
                0%, 100% {
                    transform: translateY(0px) rotate(0deg);
                }
                50% {
                    transform: translateY(-20px) rotate(180deg);
                }
            }
            
            .login-container {
                background: rgba(51, 51, 51, 0.95);
                backdrop-filter: blur(20px);
                padding: 50px 40px;
                border-radius: 20px;
                box-shadow: 
                    0 20px 40px rgba(0, 0, 0, 0.4),
                    0 0 0 1px rgba(255, 255, 255, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                width: 100%;
                max-width: 420px;
                text-align: center;
                position: relative;
                z-index: 10;
                transform: translateY(0);
                animation: slideIn 0.8s ease-out;
                border: 1px solid rgba(98, 169, 43, 0.2);
            }
            
            @keyframes slideIn {
                from {
                    transform: translateY(50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .login-header {
                margin-bottom: 40px;
            }
            
            .login-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #62a92b, #4e8b1f);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                box-shadow: 0 10px 30px rgba(98, 169, 43, 0.3);
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 10px 30px rgba(98, 169, 43, 0.3);
                }
                50% {
                    box-shadow: 0 10px 40px rgba(98, 169, 43, 0.5);
                }
                100% {
                    box-shadow: 0 10px 30px rgba(98, 169, 43, 0.3);
                }
            }
            
            .login-icon i {
                font-size: 35px;
                color: white;
            }
            
            h1 {
                color: #ffffff;
                font-size: 32px;
                font-weight: 600;
                margin-bottom: 8px;
                letter-spacing: -0.5px;
            }
            
            .subtitle {
                color: #94a3b8;
                font-size: 16px;
                margin-bottom: 30px;
                font-weight: 400;
            }
            
            .input-group {
                position: relative;
                margin-bottom: 25px;
                text-align: left;
            }
            
            .input-group label {
                display: block;
                color: #94a3b8;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 8px;
                transition: color 0.3s ease;
            }
            
            .input-wrapper {
                position: relative;
            }
            
            .input-icon {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #64748b;
                font-size: 18px;
                transition: color 0.3s ease;
                z-index: 2;
            }
            
            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 18px 50px;
                border-radius: 12px;
                border: 2px solid rgba(148, 163, 184, 0.2);
                background: rgba(30, 41, 59, 0.8);
                color: #e2e8f0;
                font-size: 16px;
                font-weight: 400;
                transition: all 0.3s ease;
                outline: none;
            }
            
            input[type="text"]:focus, input[type="password"]:focus {
                border-color: #62a92b;
                background: rgba(30, 41, 59, 0.95);
                box-shadow: 0 0 0 4px rgba(98, 169, 43, 0.1);
            }
            
            input[type="text"]:focus + .input-icon,
            input[type="password"]:focus + .input-icon {
                color: #62a92b;
            }
            
            input[type="text"]:focus ~ label,
            input[type="password"]:focus ~ label {
                color: #62a92b;
            }
            
            .login-button {
                width: 100%;
                padding: 18px;
                background: linear-gradient(135deg, #62a92b 0%, #4e8b1f 100%);
                color: white;
                font-size: 16px;
                font-weight: 600;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                margin-top: 10px;
            }
            
            .login-button::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.5s;
            }
            
            .login-button:hover::before {
                left: 100%;
            }
            
            .login-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 35px rgba(98, 169, 43, 0.4);
            }
            
            .login-button:active {
                transform: translateY(0);
            }
            
            .error-message {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #fca5a5;
                padding: 15px;
                border-radius: 12px;
                margin-top: 20px;
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: shake 0.5s ease-in-out;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            .footer-text {
                margin-top: 30px;
                color: #64748b;
                font-size: 12px;
                text-align: center;
            }
            
            /* Loading animation */
            .loading {
                position: relative;
            }
            
            .loading::after {
                content: '';
                position: absolute;
                width: 20px;
                height: 20px;
                margin: auto;
                border: 2px solid transparent;
                border-top-color: #ffffff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
            
            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
            
            /* Responsive design */
            @media (max-width: 480px) {
                .login-container {
                    margin: 20px;
                    padding: 40px 30px;
                    max-width: none;
                }
                
                h1 {
                    font-size: 28px;
                }
                
                .login-icon {
                    width: 70px;
                    height: 70px;
                }
                
                .login-icon i {
                    font-size: 30px;
                }
            }
        </style>
    </head>
    <body>
        <!-- Background Animation -->
        <div class="background-animation">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
        </div>

        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Admin Portal</h1>
                <p class="subtitle">Welcome back! Please sign in to continue.</p>
            </div>
            
            <form method="POST" id="loginForm">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" id="username" placeholder="Enter your username" required autocomplete="username" />
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="current-password" />
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="login-button">
                    <span class="button-text">Sign In</span>
                </button>

                <?php
                // Display error message if login fails
                if (isset($error)) {
                    echo "<div class='error-message'>";
                    echo "<i class='fas fa-exclamation-triangle'></i>";
                    echo htmlspecialchars($error);
                    echo "</div>";
                }
                ?>
            </form>
            
            <div class="footer-text">
                <i class="fas fa-shield-alt"></i> Secure Admin Access
            </div>
        </div>

        <script>
            // Add smooth loading animation
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const button = document.querySelector('.login-button');
                const buttonText = document.querySelector('.button-text');
                
                button.classList.add('loading');
                buttonText.textContent = 'Signing In...';
                button.disabled = true;
            });
            
            // Add focus effects to inputs
            document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentNode.parentNode.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentNode.parentNode.classList.remove('focused');
                    }
                });
            });
        </script>
    </body>
    </html>
    