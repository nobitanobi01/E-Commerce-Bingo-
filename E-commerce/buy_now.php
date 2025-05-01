<?php
session_start();

include("config_db.php");

// Get the product ID and quantity from the URL or POST (ensure valid values)
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quantity = isset($_GET['quantity']) ? max(1, intval($_GET['quantity'])) : 1;

// If product ID is invalid, show an error
if ($product_id === 0 || $quantity === 0) {
    echo "Invalid product ID or quantity.<br>";
    exit;
}

// Fetch product details from the database
$sql = "SELECT * FROM product WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit;
}

$product = $result->fetch_assoc();
$price = $product['price'];
$subtotal = $price * $quantity;

// Dummy values for now
$delivery_charge = 40; // flat delivery charge
$gst_rate = 5;
$cgst_rate = 5;

$gst = ($subtotal * $gst_rate) / 100;
$cgst = ($subtotal * $cgst_rate) / 100;
$total = $subtotal + $gst + $cgst + $delivery_charge;

// Store these values in session for checkout page
$_SESSION['delivery_charge'] = $delivery_charge;
$_SESSION['gst'] = $gst;
$_SESSION['cgst'] = $cgst;
$_SESSION['total'] = $total;

//changes
// New code for transaction ID
$order_id = strtoupper(uniqid('ORD'));
$_SESSION['order_id'] = $order_id;

// Check if user is logged in
if (isset($_SESSION['email'])) {
    // For logged-in users, get the cart from the database
    $user_email = $_SESSION['email'];
    $sql = "SELECT cart.id AS cart_id, cart.quantity, cart.total, product.name, product.price, product.image 
            FROM cart 
            JOIN product ON cart.product_id = product.id 
            WHERE cart.user_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = [];

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }
} else {
    // For guest users, check the session for cart items
    $cart_items = $_SESSION['cart'] ?? [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Buy_Now-<?php echo htmlspecialchars($product['name']); ?></title>
    <link href="stylesheet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 30px;
            max-width: 700px;
            height: auto;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
        }

        .product-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .item img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .details {
            flex: 1;
        }

        .details p {
            margin: 8px 0;
        }

        .coupon-box {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-checkout {
            background: rgb(15, 248, 15);
            color: black;
            width: 100%;
            margin-top: 20px;
            font-size: 16px;
        }

        .btn-checkout:hover {
            background-color: rgb(2, 226, 2);
        }

        .price-breakdown {
            background: #f9f9f9;
            padding: 10px 16px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        .price-breakdown h4 {
            margin-bottom: 15px;
            color: #444;
        }

        .price-line {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            font-size: 15px;
        }

        .price-line.total {
            font-weight: bold;
            font-size: 17px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            margin-top: 10px;
        }

        .back {
            margin-top: 20px;
        }

        .back a {
            color: blue;
        }

        .back a:hover {
            color: red;
        }
    </style>
</head>

<body>

    <button id="backToTop" title="Go to top">↑</button>

    <div class="container">
        <h1>Cart Summary</h1>

        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>

        <div class="item">
            <?php
            $image_path = 'uploads/' . basename($product['image']);
            if (!file_exists($image_path)) {
                echo "<p style='color:red;'>Image not found: $image_path</p>";
            }
            ?>
            <img src="<?php echo $image_path; ?>" alt="Product Image">
            <div class="details">
                <p>Price per item: $<?php echo number_format($price, 2); ?></p>
                <p>Quantity: <?php echo $quantity; ?></p>
            </div>
        </div>

        <form method="post" action="apply_coupon.php">
            <div class="coupon-box">
                <input type="text" name="coupon_code" placeholder="Enter coupon code">
                <button type="submit">Apply Coupon</button>
            </div>
        </form>

        <div class="price-breakdown">
            <h4>Price Details</h4>
            <div class="price-line">
                <span>Subtotal</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="price-line">
                <span>GST (<?php echo $gst_rate; ?>%)</span>
                <span>$<?php echo number_format($gst, 2); ?></span>
            </div>
            <div class="price-line">
                <span>CGST (<?php echo $cgst_rate; ?>%)</span>
                <span>$<?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="price-line">
                <span>Delivery Charges</span>
                <span>$<?php echo number_format($delivery_charge, 2); ?></span>
            </div>
            <div class="price-line total">
                <span>Total</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <form method="post" action="checkout.php">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>"> <!--changes-->
            <button type="submit" class="btn-checkout">Proceed to Checkout</button>
            <div class="back">
                <a href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Go Back</a>
            </div>
        </form>
    </div>

</body>

</html>
