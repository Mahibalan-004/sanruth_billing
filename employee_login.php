<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $query = "SELECT id, name, email, password FROM employees WHERE email = '$email_safe' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $employee = mysqli_fetch_assoc($result);
            if (password_verify($password, $employee['password']) || $password === $employee['password']) {
                // Use user_id for shared auth checks while keeping a separate employee display name.
                $_SESSION['user_id'] = $employee['id'];
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_email'] = $employee['email'];
                $_SESSION['role'] = 'EMPLOYEE';

                header('Location: login/dashboard.php');
                exit();
            }
        }

        $error = 'Invalid employee email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login | Sanruth Billing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f6fb;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            padding: 28px;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .12);
        }

        .login-card h1 {
            font-size: 24px;
            margin-bottom: 18px;
            color: #111827;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            background: #16a34a;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .error-box {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .login-note {
            margin-top: 14px;
            color: #64748b;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1>Employee Login</h1>
        <?php if ($error): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="employee_login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
        <p class="login-note">Existing admin users should use the regular admin login page.</p>
    </div>
</body>

</html>