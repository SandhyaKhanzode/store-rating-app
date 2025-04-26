<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "Access denied.";
    exit;
}

// Filter logic
$where = "1=1";
$params = [];

if (!empty($_GET['name'])) {
    $where .= " AND name LIKE ?";
    $params[] = "%" . $_GET['name'] . "%";
}
if (!empty($_GET['email'])) {
    $where .= " AND email LIKE ?";
    $params[] = "%" . $_GET['email'] . "%";
}
if (!empty($_GET['address'])) {
    $where .= " AND address LIKE ?";
    $params[] = "%" . $_GET['address'] . "%";
}
if (!empty($_GET['role'])) {
    $where .= " AND role = ?";
    $params[] = $_GET['role'];
}

$stmt = $conn->prepare("SELECT * FROM users WHERE $where ORDER BY name ASC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        input, select { padding: 5px; margin: 5px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
    </style>
</head>
<body>

<h2>Manage Users</h2>

<!-- Filter Form -->
<form method="GET">
    <input type="text" name="name" placeholder="Name" value="<?= $_GET['name'] ?? '' ?>">
    <input type="text" name="email" placeholder="Email" value="<?= $_GET['email'] ?? '' ?>">
    <input type="text" name="address" placeholder="Address" value="<?= $_GET['address'] ?? '' ?>">
    <select name="role">
        <option value="">All Roles</option>
        <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="owner" <?= ($_GET['role'] ?? '') === 'owner' ? 'selected' : '' ?>>Store Owner</option>
        <option value="user" <?= ($_GET['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
    </select>
    <button type="submit">Filter</button>
</form>

<!-- Users Table -->
<table>
    <thead>
        <tr>
            <th>Name</th><th>Email</th><th>Address</th><th>Role</th><th>Rating (if Owner)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['address']) ?></td>
                <td><?= $u['role'] ?></td>
                <td>
                    <?php
                    if ($u['role'] === 'owner') {
                        $stmt = $conn->prepare("SELECT s.id FROM stores s WHERE s.owner_id = ?");
                        $stmt->execute([$u['id']]);
                        $storeId = $stmt->fetchColumn();
                        if ($storeId) {
                            $ratingStmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE store_id = ?");
                            $ratingStmt->execute([$storeId]);
                            echo round($ratingStmt->fetchColumn(), 2) . " ★";
                        } else {
                            echo "No Store";
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add User Form -->
<h3>Add New User</h3>
<form method="POST" action="register.php">
    <input type="text" name="name" placeholder="Full Name" required minlength="20" maxlength="60">
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="address" placeholder="Address" required maxlength="400">
    <input type="password" name="password" placeholder="Password" required pattern="(?=.*[A-Z])(?=.*[\W]).{8,16}">
    <select name="role" required>
        <option value="user">Normal User</option>
        <option value="owner">Store Owner</option>
        <option value="admin">System Admin</option>
    </select>
    <button type="submit">Add User</button>
</form>

<br><a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
