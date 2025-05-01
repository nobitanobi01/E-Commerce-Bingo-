<?php
session_start();
header('Content-Type: application/json');
include("config_db.php");

$cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
$action = $_POST['action'] ?? '';

if (!$cart_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

if (isset($_SESSION['email'])) {
    // Logged-in user
    $stmt = $conn->prepare("SELECT cart.quantity, product.price 
                            FROM cart 
                            JOIN product ON cart.product_id = product.id 
                            WHERE cart.id = ? AND cart.user_email = ?");
    $stmt->bind_param("is", $cart_id, $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    $data = $result->fetch_assoc();
    $qty = $data['quantity'];
    $price = $data['price'];

    // Adjust quantity based on action
    if ($action === "increase") {
        $qty += 1;
    } elseif ($action === "decrease") {
        $qty = max(1, $qty - 1); // Prevent quantity from going below 1
    }

    $total = $qty * $price;

    // Update the cart in the database
    $update = $conn->prepare("UPDATE cart SET quantity = ?, total = ? WHERE id = ?");
    $update->bind_param("idi", $qty, $total, $cart_id);
    $update->execute();

    echo json_encode([
        'success' => true,
        'new_quantity' => $qty,
        'new_total' => $total
    ]);
    exit;
} else {
    // Guest user (using session)
    if (!isset($_SESSION['cart'][$cart_id])) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found in session']);
        exit;
    }

    $item = $_SESSION['cart'][$cart_id];
    $qty = $item['quantity'];
    $price = $item['price'];

    // Adjust quantity based on action
    if ($action === "increase") {
        $qty += 1;
    } elseif ($action === "decrease") {
        $qty = max(1, $qty - 1); // Prevent quantity from going below 1
    }

    $total = $price * $qty;

    // Update the cart in session
    $_SESSION['cart'][$cart_id]['quantity'] = $qty;
    $_SESSION['cart'][$cart_id]['total'] = $total;

    echo json_encode([
        'success' => true,
        'new_quantity' => $qty,
        'new_total' => $total
    ]);
    exit;
}
?>
