<?php

session_start();
include 'config_db.php';

$smail = isset($_SESSION['email']) ? $_SESSION['email'] : null;

$sql = "SELECT p.*, c.categories AS category_name 
        FROM product p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Bingo_Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="StyleSheet.css" rel="stylesheet">
    <style>
        body {
            font-family: "Winky Rough", sans-serif;
            margin: 0;
            padding: 0;
        }

        .product-page {
            display: flex;
            gap: 20px;
        }

        .filter-sidebar {
            width: 250px;
            padding: 15px;
            margin-top: 2px;
            background: #f8f8f8;
        }

        .filter-sidebar h3 {
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .dropdown {
            padding: 0px 6px;
        }

        .dropdown option {
            padding: 0px 6px;
            font-size: 14px;
        }

        .container-card {
            margin-top: 10px;
            padding: 10px;
            flex: 1;
        }

        .container-card h2 {
            text-align: center;
            font-size: 30px;
            margin-bottom: 10px;
            color: black;
            font-weight: 700;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 5px;
            justify-content: center;
        }

        .card-container a {
            color: black;
            text-decoration: none;
        }

        .product-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 250px;
            height: 450px;
            padding: 15px;
            box-shadow: 0 0 10px #eee;
            background-color: white;
            position: relative;
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-card h2 {
            text-align: center;
            font-size: 20px;
            margin-top: 5px;
            color: black;
        }

        .product-card p {
            font-size: 15px;
            margin: 5px 0;
        }

        .price {
            font-weight: bold;
            color: green;
        }

        .btn {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
        }

        .btn a {
            font-size: 15px;
            font-style: italic;
            margin-left: -25px;
            color: #007bff;
            text-decoration: underline;
        }

        .btn a:hover {
            color: green;
            text-decoration: none;
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

        .buy-all-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            margin-top: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .buy-all-btn:hover {
            background-color: #218838;
        }

        .toggle-filters-btn {
            display: none;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 10px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        .toggle-filters-btn {
            display: none;
        }

        @media (max-width: 500px) {
            .product-page {
                flex-direction: column;
            }

            .filter-sidebar {
                display: none;
                flex-wrap: wrap;
                justify-content: space-between;
                position: relative;
                width: 100%;
                background-color: #f8f8f8;
                padding: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                margin-top: -60px;
            }

            .filter-sidebar.show {
                display: flex;
            }

            .filter-sidebar h3 {
                flex: 1 0 100%;
                margin-bottom: 5px;
            }

            .filter-sidebar select,
            .filter-sidebar label {
                flex: 1 0 45%;
                font-size: 14px;
            }

            .toggle-filters-btn {
                display: block;
                background-color: #333;
                color: white;
                border: none;
                padding: 0 2px;
                width: 30px;
                cursor: pointer;
                font-size: 16px;
                border-radius: 5px;
                margin-left: 420px;
                z-index: 100;
            }

            .container-card {
                margin-left: 0;
                padding: 10px;
            }

        }
    </style>
</head>

<body>
    <!-- button for backToTop -->
    <button id="backToTop" title="Go to top">↑</button>

    <?php if (!$smail): ?>
        <!-- Guest Navbar -->
        <section>
            <nav class="navbar">
                <div class="logo">
                    <i class="fa-brands fa-snapchat" style="color: #007bff; font-size: 28px;"></i> B<span
                        style="color: #007bff; font-family: 'Winky Rough', sans-serif; font-size: 28px;">in</span>go
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Search..." />
                    <button><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                <div class="icons">
                    <a href="bingo_home.php"><i class="fa">&#xf015;</i></a>
                    <a href="#" onclick="toggleCart()"><i class="fa-solid fa-cart-shopping"></i></a>
                    <a href="signin.html"><i class="fas fa-user-circle"></i> Login</a>
                </div>
            </nav>
        </section>

    <?php else: ?>
        <!-- Logged-in Navbar -->
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
    <?php endif; ?>

    <!-- Sider bar for filters -->
    <div class="product-page">
        <!-- Toggle Filter Button for Mobile -->
        <button class="toggle-filters-btn">☰ </button>

        <!-- Sidebar Filters -->
        <div class="filter-sidebar">
            <aside>
                <h3>Sort By</h3>
                <form id="filter-form">
                    <select class="dropdown" name="sort">
                        <option value="">-- Select --</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>

                    <h3>Category</h3>
                    <?php
                    $category_query = $conn->query("SELECT * FROM categories");
                    while ($cat = $category_query->fetch_assoc()):
                        ?>
                        <label>
                            <input type="checkbox" name="category[]" value="<?= $cat['id']; ?>">
                            <?= htmlspecialchars($cat['categories']); ?>
                        </label><br>
                    <?php endwhile; ?>
                </form>
            </aside>
        </div>

        <!-- Product Display in Grid cards -->
        <section class="container-card">
            <h2>Products</h2>
            <div id="product-results" class="card-container">
                <!-- Products will be loaded here via AJAX -->
            </div>
        </section>
    </div>

    <!-- Cart -->
    <div id="cartSidebar" class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <span class="close-btn" onclick="toggleCart()">&times;</span>
        </div>
        <div class="cart-items">
            <?php
            if (isset($_SESSION['email'])) {
                // Logged-in user
                $user_email = $_SESSION['email'];
                $sql = "SELECT cart.id AS cart_id, cart.quantity, cart.total, product.id AS product_id, product.name, product.price, product.image 
                    FROM cart 
                    JOIN product ON cart.product_id = product.id 
                    WHERE cart.user_email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user_email);
                $stmt->execute();
                $stmt->bind_result($cart_id, $quantity, $total, $product_id, $name, $price, $image);

                $cart_items = [];
                while ($stmt->fetch()):
                    $item = [
                        'cart_id' => $cart_id,
                        'quantity' => $quantity,
                        'total' => $total,
                        'product_id' => $product_id,
                        'name' => $name,
                        'price' => $price,
                        'image' => $image
                    ];
                    $cart_items[] = $item;
                    ?>
                    <div class="cart-item">
                        <div class="cart-card">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <div class="cart-info">
                                <h4><?php echo $item['name']; ?></h4>
                                <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                                <div class="quantity-control">
                                    <button class="qty-btn" data-action="decrease"
                                        data-cart-id="<?php echo $item['cart_id']; ?>">−</button>
                                    <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                    <button class="qty-btn" data-action="increase"
                                        data-cart-id="<?php echo $item['cart_id']; ?>">+</button>
                                </div>
                                <p>Total: $<?php echo number_format($item['total'], 2); ?></p>
                                <div class="cart-actions">
                                    <form class="delete-form" data-cart-id="<?php echo $item['cart_id']; ?>">
                                        <button type="submit" class="btn-delete delete">Delete</button>
                                    </form>
                                    <form class="buy-form" action="buy_now.php" method="GET" style="display:inline;">
                                        <input type="hidden" name="id"
                                            value="<?php echo htmlspecialchars($item['product_id']); ?>">
                                        <input type="hidden" name="quantity"
                                            value="<?php echo htmlspecialchars($item['quantity']); ?>">
                                        <button type="submit" class="buy">Buy</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile;

                if (empty($cart_items)) {
                    echo "<p style='text-align:center;'>Your cart is empty.</p>";
                }

                // Show Buy All button if more than 2 items
                if (count($cart_items) >= 2): ?>
                    <form action="buy_multiple_item.php" method="GET" style="text-align:center; margin-top: 15px;">
                        <?php foreach ($cart_items as $item): ?>
                            <input type="hidden" name="ids[]" value="<?php echo $item['product_id']; ?>">
                            <input type="hidden" name="quantities[]" value="<?php echo $item['quantity']; ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="buy-all-btn">Buy All</button>
                    </form>
                <?php endif;

            } else {
                // Guest user (use session cart)
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $product_id => $item) {
                        ?>
                        <div class="cart-item">
                            <div class="cart-card">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                <div class="cart-info">
                                    <h4><?php echo $item['name']; ?></h4>
                                    <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                                    <div class="quantity-control">
                                        <button class="qty-btn" data-action="decrease"
                                            data-cart-id="<?php echo $product_id; ?>">−</button>
                                        <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                        <button class="qty-btn" data-action="increase"
                                            data-cart-id="<?php echo $product_id; ?>">+</button>
                                    </div>
                                    <p>Total: $<?php echo number_format($item['total'], 2); ?></p>
                                    <div class="cart-actions">
                                        <form class="delete-form" data-cart-id="<?php echo $product_id; ?>">
                                            <button type="submit" class="btn-delete delete">Delete</button>
                                        </form>
                                        <form class="buy-form" action="buy_now.php" method="GET" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                            <button type="submit" class="buy">Buy</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    // Show Buy All button if more than 2 items
                    if (count($_SESSION['cart']) >= 2): ?>
                        <form action="buy_multiple_item.php" method="GET" style="text-align:center; margin-top: 15px;">
                            <?php foreach ($_SESSION['cart'] as $pid => $item): ?>
                                <input type="hidden" name="ids[]" value="<?php echo $pid; ?>">
                                <input type="hidden" name="quantities[]" value="<?php echo $item['quantity']; ?>">
                            <?php endforeach; ?>
                            <button type="submit" class="buy-all-btn">Buy All</button>
                        </form>
                    <?php endif;
                } else {
                    echo "<p style='text-align:center;'>Your cart is empty.</p>";
                }
            }
            ?>
        </div>
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

    <!-- Scripts for the programs -->
    <script src="allscript.js"> </script>

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

    <script> // Cart buy button click handler
        document.querySelectorAll(".buy").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();

                const form = this.closest('form');
                const productId = form.querySelector('input[name="id"]').value;
                const quantity = form.querySelector('input[name="quantity"]').value || 1;

                const isLoggedIn = <?php echo isset($_SESSION['email']) ? 'true' : 'false'; ?>;

                if (isLoggedIn) {
                    window.location.href = `buy_now.php?id=${productId}&quantity=${quantity}`;
                } else {
                    window.location.href = "signin.html";
                }
            });
        });
    </script>
    <script>  // cart functionality for quantity
        document.addEventListener('DOMContentLoaded', function () {
            const cartContainer = document.querySelector('.cart-items');

            cartContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('qty-btn')) {
                    const button = e.target;
                    const cartId = button.dataset.cartId;
                    const action = button.dataset.action;

                    const cartCard = button.closest('.cart-card');
                    const quantitySpan = cartCard.querySelector('.quantity-value');
                    const totalElement = cartCard.querySelector('.cart-info p:last-of-type'); // Total: ₹xx.xx

                    // Send the data using FormData for consistency
                    const formData = new FormData();
                    formData.append('cart_id', cartId);
                    formData.append('action', action);

                    fetch('update_quantity.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Update quantity and total in the DOM
                                quantitySpan.textContent = data.new_quantity;
                                totalElement.textContent = `Total: ₹${parseFloat(data.new_total).toFixed(2)}`;

                                // Update the hidden input in the Buy Now form (for dynamic submission)
                                const buyNowForm = document.querySelector('.buy-form');
                                buyNowForm.querySelector('input[name="quantity"]').value = data.new_quantity; // Update quantity
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

    <!-- Grid card display -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("filter-form");

            function fetchProducts() {
                const formData = new FormData(form);
                const params = new URLSearchParams(formData).toString();

                fetch("fetch_products.php?" + params)
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById("product-results").innerHTML = data;
                    });
            }

            form.addEventListener("submit", function (e) {
                e.preventDefault();
                fetchProducts();
            });

            form.querySelectorAll('input[type="checkbox"], select').forEach(el => {
                el.addEventListener("change", fetchProducts);
            });

            // Initial fetch
            fetchProducts();
        });
    </script>
    <!-- Filter Sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get the button and sidebar elements
            const toggleButton = document.querySelector('.toggle-filters-btn');
            const filterSidebar = document.querySelector('.filter-sidebar');

            // Check if elements exist in the DOM
            if (toggleButton && filterSidebar) {
                // Add event listener for button click
                toggleButton.addEventListener('click', function () {
                    // Toggle the 'show' class to show or hide the sidebar
                    filterSidebar.classList.toggle('show');
                });
            }
        });
    </script>


</body>

</html>