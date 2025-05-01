<?php
session_start();
include("config_db.php");
$smail = isset($_SESSION['email']) ? $_SESSION['email'] : null;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to view your profile.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user email and details
$stmt = $conn->prepare("SELECT u.email, d.* FROM user u JOIN user_details d ON u.email = d.email WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

// Fetch order history (single product orders)
$order_stmt = $conn->prepare("
    SELECT o.*, p.name AS product_name, p.image AS product_image, p.price AS product_price, o.status AS order_status, o.status_updated_at
    FROM orders o
    JOIN product p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");

$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Bingo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="StyleSheet.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #222;
            margin-bottom: 30px;
            font-size: 24px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        /* Profile Section */
        .info {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.7;
            color: #444;
        }

        .info strong {
            display: inline-block;
            width: 110px;
            color: #333;
        }

        .edit-profile {
            text-align: center;
        }

        .edit-btn {
            color: green;
        }

        .back {
            margin-top: 40px;
        }

        .back a {
            color: green;
            text-decoration: none;
        }

        .back a:hover {
            color: blue;
        }

        /* Table for Order History */
        .order-table {
            width: 100%;
            margin-top: -20px;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        td img {
            border-radius: 6px;
            object-fit: cover;
            height: 70px;
            width: 70px;
        }

        .footer {
            position: relative;
            bottom: 0;
           
        }


        .order-table tr:hover {
            background-color: #f9f9f9;
        }


        @media (max-width: 600px) {
            .info strong {
                width: auto;
            }

            .order-table th,
            .order-table td {
                padding: 8px;
            }
        }
    </style>

</head>

<body>
    <button id="backToTop" title="Go to top">↑</button>

    <section>
        <nav class="navbar">
            <div class="logo">
                <i class="fa-brands fa-snapchat" style="color: #007bff; font-size: 28px;"></i> B<span
                    style="color: #007bff; font-family: 'Winky Rough', sans-serif;  font-size:28px">in</span>go
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search..." />
                <button><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <div class="icons">
                <a href="bingo_home.php"><i class="fa">&#xf015;</i></a>
                <a onclick="toggleSidebar()"><i class="fas fa-user-circle"></i> User</a>
            </div>
            <div class="sidebar" id="sidebar">
                <div>
                    <div class="sidebar-header">
                        <h2>Account Details</h2>
                    </div>

                    <div class="profile">
                        <h3><i class="fa fa-user-secret"></i></h3>
                        <p>Username</p>
                        <p><?php echo htmlspecialchars($smail); ?></p>
                    </div>
                </div>

                <div class="actions">
                    <a href="profile.php"><button class="settings-btn">Profile</button></a>
                    <a href="logout.php"><button class="signout-btn">Sign Out</button></a>
                </div>
            </div>
        </nav>
    </section>

    <div class="container">
        <h3>User Details </h3>
        <?php if ($user_details): ?>
            <div class="info">
                <strong>Name:</strong> <?= htmlspecialchars($user_details['name']) ?><br>
                <strong>Email:</strong> <?= htmlspecialchars($user_details['email']) ?><br>
                <strong>Phone:</strong> <?= htmlspecialchars($user_details['phone']) ?><br>
                <strong>Address:</strong>
                <?= htmlspecialchars("{$user_details['address']}, {$user_details['city']}, {$user_details['state']} - {$user_details['zip']}") ?>
            </div>
            <a href="edit_profile.php" class="edit-btn"><i class="fas fa-edit"></i> Edit Profile</a>

        <?php else: ?>
            <p>Order Somethings to see your details</p>
        <?php endif; ?>

        <h3>Order History</h3>

        <?php if ($order_result->num_rows > 0 || !empty($grouped_orders)): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>OrderDate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($order_result->num_rows > 0): ?>
                        <?php while ($order = $order_result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?= htmlspecialchars($order['product_image']) ?>" alt="Product Image"></td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td>$<?= number_format($order['product_price'], 2) ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td>$<?= number_format($order['total'], 2) ?></td>
                                <td><?= $order['order_date'] ?></td>
                                <td><?= htmlspecialchars($order['order_status']) ?><br><?= htmlspecialchars($order['status_updated_at']) ?>

                                </td>
                            </tr>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>


                </tbody>
            </table>
        <?php else: ?>
            <p>No Order Placed Yet.<br> ORDER SOMETHING!!!! </p>
        <?php endif; ?>

        <div class="back"><a href="bingo_home.php">&larr; Go Back</a></div>
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


    <script src="allscript.js"></script>

</body>

</html>