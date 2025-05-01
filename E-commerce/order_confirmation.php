<?php
include("config_db.php");

$order_id = $_GET['order_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.*, 
        p.name AS product_name, 
        p.price AS product_price, 
        s.name, s.email, s.address, s.city, s.state, s.zip, s.payment_method
    FROM orders o
    JOIN product p ON o.product_id = p.id
    JOIN shipping_details s ON o.id = s.order_id
    WHERE o.id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$data = $result->fetch_assoc();
$subtotal = $data['product_price'] * $data['quantity'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Placed.Bingo</title>
    <meta http-equiv="refresh" content="3;url=bingo_home.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="stylesheet.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f7f7f7;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #28a745;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .section {
            text-align: left;
            margin: 20px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .section h3 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 8px;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            font-size: 16px;
        }

        .info-line span:first-child {
            font-weight: 600;
        }

        .thanks-msg {
            margin-top: 20px;
            font-size: 18px;
            color: black;
        }

        .redirect-msg {
            margin-top: 10px;
            font-size: 14px;
            color: #333;
        }

        @media screen and (max-width: 600px) {
            .info-line {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><i class="fas fa-check-circle"></i> Order Placed</h2>

        <div class="section">
            <h3>Shipping Information</h3>
            <div class="info-line"><span>Name:</span> <span><?= htmlspecialchars($data['name']) ?></span></div>
            <div class="info-line"><span>Email:</span> <span><?= htmlspecialchars($data['email']) ?></span></div>
            <div class="info-line"><span>Address:</span>
                <span><?= htmlspecialchars("{$data['address']}, {$data['city']}, {$data['state']} - {$data['zip']}") ?></span>
            </div>
            <div class="info-line"><span>Payment:</span> <span><?= htmlspecialchars($data['payment_method']) ?></span>
            </div>
        </div>

        <div class="section">
            <h3>Order Summary</h3>
            <div class="info-line"><span>Product:</span> <span><?= htmlspecialchars($data['product_name']) ?></span>
            </div>
            <?php if (!empty($data['order_id'])): ?>
                <div class="info-line"><span>Order ID:</span> <span><?= htmlspecialchars($data['order_id']) ?></span></div>
            <?php endif; ?>
            <div class="info-line"><span>Price:</span> <span>$<?= number_format($data['product_price'], 2) ?></span>
            </div>
            <div class="info-line"><span>Quantity:</span> <span><?= $data['quantity'] ?></span></div>
            <div class="info-line"><span>Subtotal:</span> <span>$<?= number_format($subtotal, 2) ?></span></div>
            <div class="info-line"><span>GST:</span> <span>$<?= number_format($data['gst'], 2) ?></span></div>
            <div class="info-line"><span>CGST:</span> <span>$<?= number_format($data['cgst'], 2) ?></span></div>
            <div class="info-line"><span>Delivery:</span>
                <span>$<?= number_format($data['delivery_charge'], 2) ?></span>
            </div>
            <div class="info-line" style="font-weight: bold; font-size: 18px;">
                <span>Total:</span> <span>$<?= number_format($data['total'], 2) ?></span>
            </div>
        </div>

        <div class="thanks-msg">Thank you for shopping with us!</div>
        <div class="redirect-msg">Redirecting you to home page in 3 seconds...</div>
    </div>
</body>

</html>