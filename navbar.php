<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['user'])) return;

$role = $_SESSION['user']['role'];
?>

<style>
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #333;
        padding: 10px 20px;
        flex-wrap: wrap;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        margin: 5px 10px;
        padding: 8px 12px;
        border-radius: 4px;
        transition: 0.3s;
    }

    .navbar a:hover {
        background-color: #555;
    }

    @media (max-width: 600px) {
        .navbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="navbar">
    <div>
        <a href="dashboard.php">Dashboard</a>
        <?php if ($role === 'admin'): ?>
            <a href="users.php">Users</a>
            <a href="stores.php">Stores</a>
        <?php elseif ($role === 'user'): ?>
            <a href="stores.php">Rate Stores</a>
        <?php elseif ($role === 'owner'): ?>
            <a href="store_ratings.php">My Ratings</a>
        <?php endif; ?>
    </div>
    <div>
        <a href="change_password.php">Change Password</a>
        <a href="logout.php">Logout</a>
    </div>

    
 
</div>
