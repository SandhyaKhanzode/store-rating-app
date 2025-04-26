<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['user']['id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    // Fetch existing password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (password_verify($current, $user['password'])) {
        // Validate new password format
        if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,16}$/', $new)) {
            $message = "Password must be 8–16 characters with 1 uppercase and 1 special character.";
        } else {
            // Update password
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $userId]);
            $message = "Password updated successfully.";
        }
    } else {
        $message = "Incorrect current password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        input { padding: 8px; margin: 8px; width: 300px; }
        button { padding: 10px 20px; }
        .msg { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>Change Password</h2>

<form method="POST">
    <input type="password" name="current_password" placeholder="Current Password" required><br>
    <input type="password" name="new_password" placeholder="New Password" required><br>
    <button type="submit">Update Password</button>
</form>

<p class="msg"><?= $message ?></p>

<a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
