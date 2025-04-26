<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'];

// For Admin: Add a store
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $owner_id = $_POST['owner_id'];

    $stmt = $conn->prepare("INSERT INTO stores (name, email, address, owner_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $address, $owner_id]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stores</title>
    <style>
        input, select { margin: 5px; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    </style>
</head>
<body>

<h2>Stores</h2>

<?php if ($role === 'admin'): ?>
    <h3>Add Store</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Store Name" required>
        <input type="email" name="email" placeholder="Store Email" required>
        <input type="text" name="address" placeholder="Address" required>
        <select name="owner_id" required>
            <option value="">Select Owner</option>
            <?php
            $owners = $conn->query("SELECT id, name FROM users WHERE role = 'owner'")->fetchAll();
            foreach ($owners as $o) {
                echo "<option value='{$o['id']}'>{$o['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Add Store</button>
    </form>
<?php endif; ?>

<h3>Store Listings</h3>

<table>
    <tr>
        <th>Name</th><th>Address</th><th>Average Rating</th>
        <?php if ($role === 'user') echo "<th>Your Rating</th><th>Action</th>"; ?>
    </tr>
    <?php
    $stores = $conn->query("SELECT * FROM stores")->fetchAll();
    foreach ($stores as $store):
        $sid = $store['id'];

        // Get average rating
        $avgStmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE store_id = ?");
        $avgStmt->execute([$sid]);
        $avg = round($avgStmt->fetchColumn(), 2) ?: "No ratings";

        echo "<tr>";
        echo "<td>{$store['name']}</td>";
        echo "<td>{$store['address']}</td>";
        echo "<td>$avg ★</td>";

        if ($role === 'user') {
            $ratingStmt = $conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND store_id = ?");
            $ratingStmt->execute([$user['id'], $sid]);
            $userRating = $ratingStmt->fetchColumn();

            echo "<td>" . ($userRating ? $userRating . " ★" : "Not rated") . "</td>";
            echo "<td>
                    <form method='POST' action='rate.php'>
                        <input type='hidden' name='store_id' value='$sid'>
                        <select name='rating'>
                            <option value=''>-- Rate --</option>";
            for ($i = 1; $i <= 5; $i++) {
                echo "<option value='$i'>$i ★</option>";
            }
            echo "</select>
                        <button type='submit'>Submit</button>
                    </form>
                  </td>";
        }

        echo "</tr>";
    endforeach;
    ?>
</table>

<br><a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
