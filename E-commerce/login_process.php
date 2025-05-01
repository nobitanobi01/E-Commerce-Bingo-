<?php
session_start();
include 'config_db.php';

// Assuming you have user_id after login
$_SESSION['user_id'] = $user_id;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Check if item already exists in DB
        $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
        }
    }

    // Clear session cart after merging
    unset($_SESSION['cart']);
}
?>
