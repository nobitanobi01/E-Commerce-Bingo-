<?php
session_start();
include("config_db.php");

ob_start();

if (!isset($_SESSION['email'])) {
    // Guest user – display cart from session
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $item) {
            ?>
            <div class="cart-item">
                <div class="cart-card">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                    <div class="cart-info">
                        <h4><?php echo $item['name']; ?></h4>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn" data-action="decrease" data-cart-id="<?php echo $product_id; ?>">−</button>
                            <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                            <button type="button" class="qty-btn" data-action="increase" data-cart-id="<?php echo $product_id; ?>">+</button>
                        </div>
                        <p>Total: $<?php echo number_format($item['total'], 2); ?></p>
                        <div class="cart-actions">
                            <form class="delete-form" data-cart-id="<?php echo $product_id; ?>" style="display:inline;">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                            <form method="post" action="buy_item.php" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                <button class="buy">Buy</button>
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

} else {
    // Logged-in user – fetch cart from database
    $user_email = $_SESSION['email'];

    // ✅ Sync session cart to database (if exists)
    if (!isset($_POST['skip_sync']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $cart_item) {
            $sql_check = "SELECT * FROM cart WHERE user_email = ? AND product_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("si", $user_email, $product_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $existing_item = $result_check->fetch_assoc();
                $new_qty = $existing_item['quantity'] + $cart_item['quantity'];
                $new_total = $new_qty * $cart_item['price'];

                $sql_update = "UPDATE cart SET quantity = ?, total = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("idi", $new_qty, $new_total, $existing_item['id']);
                $stmt_update->execute();
            } else {
                $sql_insert = "INSERT INTO cart (user_email, product_id, quantity, total) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("siid", $user_email, $product_id, $cart_item['quantity'], $cart_item['total']);
                $stmt_insert->execute();
            }
        }

        // ✅ Clear session cart and redirect to refresh cart from DB
        unset($_SESSION['cart']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // ✅ Now fetch the cart from the DB
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
                            <button type="button" class="qty-btn" data-action="decrease" data-cart-id="<?php echo $row['cart_id']; ?>">−</button>
                            <span class="quantity-value"><?php echo $row['quantity']; ?></span>
                            <button type="button" class="qty-btn" data-action="increase" data-cart-id="<?php echo $row['cart_id']; ?>">+</button>
                        </div>
                        <p>Total: $<?php echo number_format($row['total'], 2); ?></p>
                        <div class="cart-actions">
                            <form class="delete-form" data-cart-id="<?php echo $row['cart_id']; ?>" style="display:inline;">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                            <form method="post" action="buy_item.php" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                                <button class="buy">Buy</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
    else:
        echo "<p style='text-align:center;'>Your cart is empty.</p>";
    endif;
}

echo ob_get_clean();
?>
