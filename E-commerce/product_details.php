<?php
session_start();

$smail = isset($_SESSION['email']) ? $_SESSION['email'] : null;

include("config_db.php");

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

// Fetch product details and category name from database
$sql = "SELECT product.*, categories AS category_name 
        FROM product
        JOIN categories ON product.category_id = categories.id
        WHERE product.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $product_category = $product['category_name']; // Category name from the 'categories' table
} else {
    echo "Product not found.";
    exit;
}


// to redirect here after sign in 
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> -Bingo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="StyleSheet.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;

        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            width: 300px;
            height: auto;
            background-color: #fff;
            padding: 20px;
            transition: right 0.3s ease;
            z-index: 11;
            flex-direction: column;
            justify-content: space-between;
            display: none;
            right: 0;
            top: 8%;
        }

        .container {
            width: 100%;
            padding: 40px;
            height: 65vh;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
        }

        .col-half {
            flex: 1;
            min-width: 300px;
        }

        .product-image {
            width: 300px;
            height: 400px;
            margin-top: 10px;
            margin-left: 150px;
            object-fit: contain;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .text-muted {
            color: #888;
            margin-bottom: 15px;
        }

        .text-success {
            margin: 15px 0;
        }

        form {
            margin-top: 20px;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-group label {
            margin-right: 10px;
        }

        .form-group input[type="number"] {
            width: 70px;
            padding: 5px;
            text-align: center;
            margin: 0 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            gap: 40px;
        }

        .counter {
            padding: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: green;
        }

        .btn-buy-now {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-buy-now:hover {
            background-color: green;
        }


        .toast {
            position: fixed;
            right: 0;
            top: 0;
            background-color: rgb(22, 217, 67);
            color: black;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
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

        .btn-delete.delete {
            background-color: red ;
            color: white;

        }
        .delete:hover{
            background-color: red;
        }

        button.buy {
            background-color: rgb(23, 199, 64);
            color: white;
        }

        .back {
            margin-top: 20px;
            margin-left: 20px;
        }

        .back a {
            color: blue;
        }

        .back a:hover {
            color: red;
        }

        .buy-all-btn {
            background-color: rgb(23, 199, 64);
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
    </style>
    <script>
        function changeQuantity(amount) {
            const input = document.getElementById("quantity");
            let current = parseInt(input.value);
            if (!isNaN(current)) {
                const newValue = current + amount;
                if (newValue >= 1) {
                    input.value = newValue;
                }
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            sidebar.style.display = sidebar.style.display === "block" ? "none" : "block";
        }
    </script>
</head>

<body>

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


    <div class="back">
        <a href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Go Back</a>
    </div>

    <!-- grid to display products-->
    <section>
        <div class="container">
            <div class="row">
                <!-- Left: Image -->
                <div class="col-half">
                    <?php
                    $imagePath = $product['image'];
                    if (strpos($imagePath, 'uploads/') === false) {
                        $imagePath = 'uploads/' . $imagePath;
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" class="product-image"
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <!-- Right: Details -->
                <div class="col-half">
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <h5 class="text-muted"><?php echo htmlspecialchars($product_category); ?></h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p class="text-success">Price:- $<?php echo number_format($product['price'], 2); ?></p>
                    <form id="addToCartForm">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <button class="counter" type="button" onclick="changeQuantity(-1)">−</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1">
                            <button class="counter" type="button" onclick="changeQuantity(1)">+</button>
                        </div>
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                        <a href="#" id="buyNowBtn" class="btn-buy-now">Buy Now</a>
                    </form>
                    <div id="toast" class="toast">✔️ Item added to cart successfully!</div>
                </div>

            </div>
        </div>
    </section>

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
                } else {
                    echo "<p style='text-align:center;'>Your cart is empty.</p>";
                }
            }
            ?>
        </div>
    </div>

    <script src="allscript.js"> </script>

    <!-- Cart add and delete function -->
    <script>
        function attachDeleteListeners() {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const cartId = this.getAttribute('data-cart-id');
                    const formElement = this.closest('.cart-item');

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
                                formElement.remove();

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
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('addToCartForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        const cartItemsContainer = document.querySelector('.cart-items');
                        const emptyMsg = cartItemsContainer.querySelector('p');
                        if (emptyMsg) emptyMsg.remove();

                        fetch('fetch_cart.php')
                            .then(res => res.text())
                            .then(html => {
                                cartItemsContainer.innerHTML = html;
                                attachDeleteListeners(); // now safe to call
                            });

                        const toast = document.getElementById('toast');
                        toast.style.display = 'block';
                        setTimeout(() => {
                            toast.style.display = 'none';
                        }, 1500);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            attachDeleteListeners(); // also safe now
        });
    </script>

    <script>
        // Buy Now button click handler
        document.getElementById("buyNowBtn").addEventListener("click", function (e) {
            e.preventDefault(); // Prevent default anchor behavior

            const isLoggedIn = <?php echo isset($_SESSION['email']) ? 'true' : 'false'; ?>;
            const productId = <?php echo json_encode($product['id']); ?>;
            const quantity = document.getElementById("quantity").value || 1;

            if (isLoggedIn) {
                // If user is logged in, go to Buy Now page with product info
                window.location.href = `buy_now.php?id=${productId}&quantity=${quantity}`;
            } else {
                // If not logged in, redirect to signin page
                window.location.href = "signin.html";
            }
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


    <script> //cart slider bar visbility
        function toggleCart() {
            const sidebar = document.getElementById("cartSidebar");
            sidebar.classList.toggle("open");
        }
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
    <!-- JavaScript for dynamic Buy All toggle -->
    <script>
        function updateBuyAllButton() {
            const itemCount = document.querySelectorAll('.cart-item').length;
            const buyAllContainer = document.getElementById('buyAllContainer');
            if (buyAllContainer) {
                buyAllContainer.style.display = itemCount >= 2 ? 'block' : 'none';
            }
        }

        // Run on initial load
        document.addEventListener("DOMContentLoaded", updateBuyAllButton);
    </script>

</body>

</html>