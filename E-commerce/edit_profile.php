<?php
session_start();
include("config_db.php");

if (!isset($_SESSION['email'])) {
    echo "Please log in.";
    exit;
}

$user_email = $_SESSION['email'];

// Fetch current details
$stmt = $conn->prepare("SELECT * FROM user_details WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];

    $update = $conn->prepare("UPDATE user_details SET name=?, phone=?, address=?, city=?, state=?, zip=? WHERE email=?");
    $update->bind_param("sssssss", $name, $phone, $address, $city, $state, $zip, $user_email);
    $update->execute();

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile.Bingo</title>
    <link href="stylesheet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            background: linear-gradient(to right, #f1f1f1, #e2e2e2);
        }

        .edit-profile-container {
            max-width: 700px;
            margin: 50px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 18px;
            margin-bottom: 6px;
            color: #555;
        }

        .form-group input {
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .submit-btn {
            margin-top: 30px;
            padding: 14px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #218838;
        }

        /* cart*/
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 350px;
            height: 100%;
            background: #fff;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            overflow-y: auto;
            transition: right 0.3s ease;
            z-index: 1000;
        }

        .cart-sidebar.open {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .close-btn {
            cursor: pointer;
            font-size: 24px;
            color: #888;
        }

        .cart-card {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .cart-card img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
        }

        .cart-info h4 {
            margin: 0;
            font-size: 16px;
        }

        .cart-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-control button {
            background: #ddd;
            border: none;
            padding: 4px 8px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .cart-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .cart-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-form .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        button.buy {
            background-color: #28a745;
            color: white;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Top-Bottom buttom-->
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
                <a href="bingo_home.php"><i  class="fa">&#xf015;</i></a>
                <a href="#" onclick="toggleCart()"><i class="fa-solid fa-cart-shopping"></i></a>
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
                        <p> <?php echo $smail; ?></p>
                    </div>
                </div>

                <div class="actions">
                    <a href="profile.php"> <button class="settings-btn">Profile</button></a>
                    <a href="Bingo.html"><button class="signout-btn">Sign Out</button></a>
                </div>
            </div>
        </nav>
    </section>

    <!-- Cart -->
    <div id="cartSidebar" class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <span class="close-btn" onclick="toggleCart()">&times;</span>
        </div>
        <div class="cart-items">
            <?php

            if (isset($_SESSION['email'])) {
                $user_email = $_SESSION['email'];
                $sql = "SELECT cart.id AS cart_id, cart.quantity, cart.total, product.name, product.price, product.image 
                FROM cart 
                JOIN product ON cart.product_id = product.id 
                WHERE cart.user_email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user_email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        ?>
                        <div class="cart-item">
                            <div class="cart-card">
                                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                                <div class="cart-info">
                                    <h4><?php echo $row['name']; ?></h4>
                                    <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                                    <div class="quantity-control">
                                        <button class="qty-btn" data-action="decrease"
                                            data-cart-id="<?php echo $row['cart_id']; ?>">−</button>
                                        <span class="quantity-value"><?php echo $row['quantity']; ?></span>
                                        <button class="qty-btn" data-action="increase"
                                            data-cart-id="<?php echo $row['cart_id']; ?>">+</button>
                                    </div>
                                    <p>Total: $<?php echo number_format($row['total'], 2); ?></p>
                                    <div class="cart-actions">

                                        <form class="delete-form" data-cart-id="<?php echo $row['cart_id']; ?>"
                                            style="display:inline;">
                                            <button type="submit" class="btn-delete delete">Delete</button>
                                        </form>

                                        <form method="get" action="buy_now.php" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $row['cart_id']; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $row['quantity']; ?>">
                                            <button type="submit" class="buy">Buy</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                    <p style="text-align:center;">Your cart is empty.</p>
                <?php endif;
            } ?>
        </div>
    </div>

    <div class="edit-profile-container">
        <h2>Edit Your Profile</h2>
        <form method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" name="state" value="<?= htmlspecialchars($user['state']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="zip">Zip</label>
                    <input type="text" name="zip" value="<?= htmlspecialchars($user['zip']) ?>" required>
                </div>
            </div>
            <button type="submit" class="submit-btn">Save Changes</button>
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
    <script src="script.js"> </script>
    <script>
        function toggleCart() {
            const cart = document.getElementById("cartSidebar");
            cart.classList.toggle("open");
        }
    </script>
    <script> // cart delete functionality 
        document.addEventListener("DOMContentLoaded", function () {
            // function attachDeleteListeners() {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const cartId = this.getAttribute('data-cart-id');
                    const formElement = this.closest('.cart-item'); // This targets the whole cart item block

                    fetch('delete_cart_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'cart_id=' + encodeURIComponent(cartId)
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data.includes('Failed')) {
                                alert(data);
                            } else {
                                formElement.remove(); // Remove item from DOM

                                // Check if any cart items remain
                                const remainingItems = document.querySelectorAll('.cart-item');
                                if (remainingItems.length === 0) {
                                    const cartItemsContainer = document.querySelector('.cart-items');
                                    cartItemsContainer.innerHTML = '<p style="text-align:center;">Your cart is empty.</p>';
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert("Something went wrong!");
                        });
                });
            });
            attachDeleteListeners();
        });
    </script>

    <script>  /* Cart quantity update*/
        document.addEventListener('DOMContentLoaded', function () {
            const cartContainer = document.querySelector('.cart-items');

            cartContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('qty-btn')) {
                    const button = e.target;
                    const cartId = button.dataset.cartId;
                    const action = button.dataset.action;
                    const quantitySpan = button.parentElement.querySelector('.quantity-value');
                    const cartCard = button.closest('.cart-card');
                    const priceText = cartCard.querySelector('.cart-info p').textContent;
                    const price = parseFloat(priceText.split('Price: $')[1]);

                    fetch('update_quantity.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `cart_id=${cartId}&action=${action}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Update quantity and total in the DOM
                                quantitySpan.textContent = data.new_quantity;
                                totalElement.textContent = `₹${data.new_total.toFixed(2)}`;
                            } else {
                                alert(data.message || 'Error updating cart.');
                            }
                        })
                        .catch(err => {
                            console.error("AJAX error:", err);
                        });
                }
            });
        });
    </script>
</body>

</html>