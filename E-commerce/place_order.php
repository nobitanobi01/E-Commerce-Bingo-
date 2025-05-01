<?php
session_start();
include("config_db.php");

// Ensure correct timezone
date_default_timezone_set('Asia/Kolkata'); // Set the timezone to your region

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $state = $_POST["state"];
    $city = $_POST["city"];
    $zip = $_POST["zip"];
    $payment_method = $_POST["payment_method"] ?? 'cod';
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;

    // Get product price from DB
    $stmt = $conn->prepare("SELECT price FROM product WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "Product not found.";
        exit;
    }

    $product = $result->fetch_assoc();
    $price = $product['price'];
    $subtotal = $price * $quantity;

    $delivery_charge = $_SESSION["delivery_charge"] ?? 40;
    $gst = $_SESSION["gst"] ?? 0;
    $cgst = $_SESSION["cgst"] ?? 0;
    $total = $subtotal + $delivery_charge + $gst + $cgst;
    $order_date = date("Y-m-d H:i:s"); // Current date and time
    $user_id = $_SESSION["user_id"] ?? 1;

    $order_id = $_POST['order_id'] ?? $_SESSION['order_id'] ?? ''; // safer fallback

    // Insert into orders table, without affecting status_updated_at
    $stmtOrder = $conn->prepare("INSERT INTO orders (product_id, quantity, delivery_charge, gst, cgst, total, order_date, user_id, order_id, status_updated_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)"); // Explicitly set status_updated_at to NULL
    $stmtOrder->bind_param("iiddddsis", $product_id, $quantity, $delivery_charge, $gst, $cgst, $total, $order_date, $user_id, $order_id);
    $stmtOrder->execute();
    $order_id = $stmtOrder->insert_id; // Get the inserted order ID

    // Insert shipping details
    $stmtShipping = $conn->prepare("INSERT INTO shipping_details (order_id, name, email, phone, address, state, city, zip, payment_method) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtShipping->bind_param("issssssss", $order_id, $name, $email, $phone, $address, $state, $city, $zip, $payment_method);
    $stmtShipping->execute();

    // Check if user exists in user_details
    $stmtCheck = $conn->prepare("SELECT id FROM user_details WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // User exists, update info
        $stmtUpdate = $conn->prepare("UPDATE user_details SET name = ?, phone = ?, address = ?, state = ?, city = ?, zip = ? WHERE email = ?");
        $stmtUpdate->bind_param("sssssss", $name, $phone, $address, $state, $city, $zip, $email);
        $stmtUpdate->execute();
    } else {
        // User doesn't exist, insert new
        $stmtInsert = $conn->prepare("INSERT INTO user_details (name, email, phone, address, state, city, zip) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("sssssss", $name, $email, $phone, $address, $state, $city, $zip);
        $stmtInsert->execute();
    }

    header("Location: order_confirmation.php?order_id=" . $order_id);
    exit;
}
?>
