<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'user';

    $stmt = $conn->prepare("INSERT INTO users (name, email, address, password, role) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $address, $password, $role])) {
        echo "User registered successfully";
    } else {
        echo "Registration failed";
    }
}
?>
