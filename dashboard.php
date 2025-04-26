<?php


session_start();
require 'db.php';
include 'navbar.php';
// ... rest of your dashboard logic

session_start();

require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2>Welcome, <?php echo $user['name']; ?> (<?php echo $role; ?>)</h2>

<?php if ($role === 'admin'): ?>
    <h3>Admin Dashboard</h3>
    <div class="card">
        <?php
        $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalStores = $conn->query("SELECT COUNT(*) FROM stores")->fetchColumn();
        $totalRatings = $conn->query("SELECT COUNT(*) FROM ratings")->fetchColumn();
        ?>
        <p>Total Users: <?php echo $totalUsers; ?></p>
        <p>Total Stores: <?php echo $totalStores; ?></p>
        <p>Total Ratings: <?php echo $totalRatings; ?></p>
    </div>

    <a href="users.php">Manage Users</a><br>
    <a href="stores.php">Manage Stores</a><br>

<?php elseif ($role === 'owner'): ?>
    <h3>Store Owner Dashboard</h3>
    <?php
    $store = $conn->prepare("SELECT * FROM stores WHERE owner_id = ?");
    $store->execute([$user['id']]);
    $store = $store->fetch();

    if ($store):
        $ratings = $conn->prepare("SELECT rating FROM ratings WHERE store_id = ?");
        $ratings->execute([$store['id']]);
        $ratingsData = $ratings->fetchAll(PDO::FETCH_COLUMN);
        $avgRating = $ratingsData ? round(array_sum($ratingsData) / count($ratingsData), 2) : 'N/A';
        ?>
        <p>Store Name: <?php echo $store['name']; ?></p>
        <p>Average Rating: <?php echo $avgRating; ?></p>
        <p>Ratings:</p>
        <ul>
            <?php
            foreach ($ratingsData as $r) {
                echo "<li>$r ★</li>";
            }
            ?>
        </ul>
    <?php else: ?>
        <p>No store found for this owner.</p>
    <?php endif; ?>

<?php else: ?>
    <h3>User Dashboard</h3>
    <a href="stores.php">Browse & Rate Stores</a><br>
<?php endif; ?>

<br><br>
<a href="logout.php">Logout</a>

</body>
</html>
