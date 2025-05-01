<?php
session_start();
include("config_db.php");

$order_id = $_POST['order_id'] ?? $_SESSION['order_id'] ?? null;  
$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

$sql = "SELECT * FROM product WHERE id = ?";
$stmt = $conn->prepare($sql);
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

$delivery_charge = $_SESSION['delivery_charge'] ?? 40;
$gst = $_SESSION['gst'] ?? 0;
$cgst = $_SESSION['cgst'] ?? 0;
$total = $_SESSION['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout -<?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="stylesheet.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            height: auto;
            margin: 30px auto;
            padding: 10px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 0;
            font-size: 28px;
        }

        .main-content {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .left-section {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .right-section {
            flex: 1;
        }

        .section {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .section h3 {
            margin-bottom: 12px;
            font-size: 20px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"] {
            width: 100%;
            padding: 5px;
            margin: 2px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="radio"] {
            margin-top: 6px;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .payment-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .payment-label {
            min-width: 14px;
            font-weight: bold;
            margin-top: 0px;
        }

        .payment-fields {
            display: none;
            flex-direction: row;
            gap: 10px;
            flex-wrap: wrap;
            flex: 1;
        }


        .payment-fields.active {
            display: flex;
        }

        .payment-fields input {
            margin: 0 0;
            flex: 1 1 10%;
            width: 50px;
            padding: 3px;
            height: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }


        .proceed-btn {
            margin-top: 0px;
            padding: 8px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: fit-content;
        }

        .proceed-btn:hover {
            background: #0056b3;
        }

        .order-summary,
        .price-details {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 16px;
            background-color: #fafafa;
            margin-bottom: 10px;
        }

        .price-line {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
        }

        .total {
            font-weight: bold;
            border-top: 1px solid #ccc;
            margin-top: 10px;
            padding-top: 10px;
        }

        button[type="submit"] {
            padding: 10px;
            background: rgb(15, 248, 15);
            color: black;
            border: none;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background: rgb(2, 226, 2);
        }


        /* existing styles above ... */

        @media screen and (max-width: 500px) {
            .main-content {
                flex-direction: column;
            }

            .left-section,
            .right-section {
                flex: 1 1 100%;
            }
        }
    </style>
</head>

<body>
    <button id="backToTop" title="Go to top">↑</button>

    <nav class="navbar">
        <div class="logo">
            <i class="fa-brands fa-snapchat" style="color: #007bff; font-size: 28px;"></i>
            B<span style="color: #007bff; font-family: 'Winky Rough', sans-serif; font-size:28px">in</span>go
        </div>

        <div class="icons">
            <a href="bingo_home.php"><i class="fa">&#xf015;</i></a>
            </div>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Account Details</h2>
            </div>
            <div class="profile">
                <h3><i class="fa fa-user-secret"></i></h3>
                <p>Username</p>
                <p><?php echo $smail; ?></p>
            </div>
            <div class="actions">
                <a href="profile.php"> <button class="settings-btn">Profile</button></a>
                <a href="logout.php"><button class="signout-btn">Sign Out</button></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Complete Your Purchase</h2>
        <form method="post" action="place_order.php">
            <div class="main-content">
                <!-- LEFT SECTION -->
                <div class="left-section">
                    <!-- Shipping Details -->
                    <div class="section">
                        <h3>1. Shipping Details</h3>
                        <input type="text" name="name" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email Address" required>
                        <input type="text" name="phone" placeholder="Phone Number" required>
                        <input type="text" name="address" placeholder="Street Address" required>
                        <input type="text" name="city" placeholder="City" required>
                        <input type="text" name="state" placeholder="State" required>
                        <input type="text" name="zip" placeholder="ZIP Code" required>
                    </div>

                    <!-- Payment Method -->
                    <div class="section">
                        <h3>2. Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-option">
                                <input type="radio" name="payment_method" value="card"
                                    onclick="showPaymentFields('card')">
                                <div class="payment-label">Credit/Debit Card</div>
                                <div id="card-fields" class="payment-fields">
                                    <input type="text" name="card_number" placeholder="Card Number">
                                    <input type="text" name="card_expiry" placeholder="Expiry Date (MM/YY)">
                                    <input type="text" name="card_cvv" placeholder="CVV">
                                    <button type="button" class="proceed-btn">Proceed</button>
                                </div>
                            </div>

                            <div class="payment-option">
                                <input type="radio" name="payment_method" value="cod"
                                    onclick="showPaymentFields('cod')">
                                <div class="payment-label">Cash on Delivery</div>
                            </div>

                            <div class="payment-option">
                                <input type="radio" name="payment_method" value="upi"
                                    onclick="showPaymentFields('upi')">
                                <div class="payment-label">UPI</div>
                                <div id="upi-fields" class="payment-fields">
                                    <input type="text" name="upi_id" placeholder="Enter UPI ID">
                                    <button type="button" class="proceed-btn">Proceed</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SECTION -->
                <div class="right-section">
                    <div class="section summary">
                        <h3>3. Order Summary</h3>
                        <div class="order-summary">
                            <p><strong>Product:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
                            <p><strong>Price:</strong> $<?php echo number_format($price, 2); ?></p>
                            <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
                        </div>

                        <div class="price-details">
                            <div class="price-line">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="price-line">
                                <span>GST (5%)</span>
                                <span>$<?php echo number_format($gst, 2); ?></span>
                            </div>
                            <div class="price-line">
                                <span>CGST (5%)</span>
                                <span>$<?php echo number_format($cgst, 2); ?></span>
                            </div>
                            <div class="price-line">
                                <span>Delivery</span>
                                <span>$<?php echo number_format($delivery_charge, 2); ?></span>
                            </div>
                            <div class="price-line total">
                                <span>Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Inputs -->
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total; ?>">

            <!-- Submit Button -->
            <button type="submit">Place Order</button>
        </form>
    </div>
    
    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-links" style="margin-bottom: 5px;">
            <a href="bingo_home.php">Home</a>
            <a href="bingo_product.php">Products</a>
            <a href="#">cart</a>
            <a href="bingo_product.php">New Releases</a>
        </div>
        <hr style="width: 400px; margin-left: 550px;">

        <div class="footer-social" style="margin-top: 10px; margin-bottom: 5px;">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook-f"></i></a>
        </div>

        <hr style="width: 200px; margin-left: 650px;">

        <div class="footer-legal">
            <p>Conditions of Use & Sale | Privacy Notice | Interest-Based Ads</p>
            <p>© 1996-2025, Bingo.com, Inc. or its affiliates</p>
        </div>
    </footer>

    <script src="allscript.js"> </script>
    <script>
        function showPaymentFields(method) {
            document.getElementById("card-fields").style.display = "none";
            document.getElementById("upi-fields").style.display = "none";

            if (method === "card") {
                document.getElementById("card-fields").style.display = "flex";
            } else if (method === "upi") {
                document.getElementById("upi-fields").style.display = "flex";
            }
        }
    </script>

</body>

</html>