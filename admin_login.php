<?php
// Set session settings BEFORE starting session
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();

require 'db.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate admin username and password using the database
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Inside your login success logic:
if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_logged_in'] = true; // This is the "Key"
    $_SESSION['admin_user'] = $admin['username'];
    header("Location: admin_dashboard.php");
    exit();
} else {
        // Display clear error messages for invalid login
        $error_message = "اسم المستخدم أو كلمة المرور غير صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول المشرف</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #1b5e20; color: white; padding: 10px; width: 100%; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; font-size: 14px; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2 style="background-color: #1b5e20; color: white; padding: 10px; border-radius: 4px; margin-top: 0;">تسجيل دخول المشرف</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">دخول</button>
        </form>
    </div>

</body>
</html>