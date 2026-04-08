<?php
session_start();
require_once("config/db.php");

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: login/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // CHECK IF TABLE EXISTS
    $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'credential'");
    if (mysqli_num_rows($tableCheck) == 0) {
        $error = "System Error: Authentication table missing. <a href='setup_users.php' style='color:inherit; font-weight:bold;'>Click here to fix it.</a>";
    } else {
        $query = "SELECT * FROM credential WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                header("Location: login/dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password. <br><small>Tip: If this is a new setup, run <a href='setup_users.php'>setup_users.php</a></small>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sanruth Billing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e7e34;
            --primary-hover: #155d25;
            --bg-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            background-attachment: fixed;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(30, 126, 52, 0.2);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(-10%, -10%); }
            to { transform: translate(10%, 10%); }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--glass-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-container {
            width: 70px;
            height: 70px;
            background: var(--primary-color);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 15px -3px rgba(30, 126, 52, 0.3);
            color: white;
            font-size: 32px;
            font-weight: bold;
        }

        .brand-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .brand-accent {
            color: var(--primary-color);
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-field {
            width: 100%;
            padding: 12px 16px;
            padding-left: 44px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 126, 52, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px;
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #fecaca;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .show-pass-container {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .show-pass-container label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="blob"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-container">⚙️</div>
                <h1 class="brand-name"><span class="brand-accent">Sanruth</span> Billing</h1>
                <p class="subtitle">Please enter your credentials</p>
            </div>

            <div class="error-message">
                <?php echo $error; ?>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" name="username" class="input-field" placeholder="Username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
                    </div>
                    <div class="show-pass-container">
                        <input type="checkbox" id="showPass" onclick="togglePass()">
                        <label for="showPass">Show Password</label>
                    </div>
                </div>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <script>
            function togglePass() {
                var x = document.getElementById("password");
                x.type = x.type === "password" ? "text" : "password";
            }
            </script>

            <div class="footer-text">
                &copy; <?php echo date('Y'); ?> Sanruth Billing System
            </div>
        </div>
    </div>
</body>
</html>
