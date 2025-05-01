<?php
session_start();
include("config_db.php");

$product_id = intval($_POST['id']);
$quantity = max(1, intval($_POST['quantity']));

// Fetch product info
$product_sql = "SELECT id, name, image, price FROM product WHERE id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='text-align:center;'>Product not found.</p>";
    exit;
}

$product = $result->fetch_assoc();
$total = $product['price'] * $quantity;

if (!isset($_SESSION['email'])) {
    // Guest user - store in session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        // Update existing item quantity
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        $_SESSION['cart'][$product_id]['total'] = $_SESSION['cart'][$product_id]['quantity'] * $product['price'];
    } else {
        // Add new item
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'price' => $product['price'],
            'total' => $total
        ];
    }
} else {
    // Logged-in user - store in database
    $email = $_SESSION['email'];

    $check_sql = "SELECT id, quantity FROM cart WHERE user_email = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing cart item
        $row = $check_result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $new_total = $product['price'] * $new_quantity;

        $update = $conn->prepare("UPDATE cart SET quantity = ?, total = ? WHERE id = ?");
        $update->bind_param("idi", $new_quantity, $new_total, $row['id']);
        $update->execute();
    } else {
        // Insert new cart item
        $insert = $conn->prepare("INSERT INTO cart (user_email, product_id, quantity, total) VALUES (?, ?, ?, ?)");
        $insert->bind_param("siid", $email, $product_id, $quantity, $total);
        $insert->execute();
    }
}

// Render cart sidebar
ob_start();
$_POST['skip_sync'] = true;
include 'fetch_cart.php';
echo ob_get_clean();
?>
