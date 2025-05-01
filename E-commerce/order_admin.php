<?php
session_start();
include("config_db.php");

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

if (!$user_details) {
    echo "User not found.";
    exit;
}

// Fetch order history (single product orders)
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.*, p.id AS product_id, p.name AS product_name, p.image AS product_image, p.price AS product_price
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
    <title>Admin Panel - Order History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="stylesheet.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
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

        td button {
            padding: 2px;
        }

        td button:hover {
            background-color: greenyellow;
            border-radius: 5px;

        }

        .order-table tr:hover {
            background-color: #f9f9f9;
        }

        @media (max-width: 600px) {

            .order-table th,
            .order-table td {
                padding: 8px;
            }
        }
    </style>

</head>

<body>
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
        <?php endif; ?>

        <h3>Admin Panel Orders</h3>

        <?php if ($order_result->num_rows > 0 || !empty($grouped_orders)): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Order Date</th>
                        <th colspan="3">Status</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- Single Orders -->
                    <?php if ($order_result->num_rows > 0): ?>
                        <?php while ($order = $order_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><img src="<?= htmlspecialchars($order['product_image']) ?>" alt="Product Image"></td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td>$<?= number_format($order['product_price'], 2) ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td>$<?= number_format($order['total'], 2) ?></td>
                                <td><?= $order['order_date'] ?></td>
                                <td>
                                    <input type="radio"
                                        id="dispatched_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>"
                                        name="status_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>"
                                        value="dispatched">
                                    <label
                                        for="dispatched_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>">Dispatched</label>
                                </td>
                                <td>
                                    <input type="radio"
                                        id="delivered_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>"
                                        name="status_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>"
                                        value="delivered">
                                    <label
                                        for="delivered_<?= htmlspecialchars($order['order_id']) ?>_<?= htmlspecialchars($order['product_id']) ?>">Delivered</label>
                                </td>
                                <td>
                                    <button type="button"
                                        onclick="updateStatus('<?= $order['order_id'] ?>', '<?= $order['product_id'] ?>')">Update</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Grouped Orders -->
                    <?php if (!empty($grouped_orders)): ?>
                        <?php foreach ($grouped_orders as $oid => $order): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['transaction_id']) ?></td>
                                    <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image"></td>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                    <td><?= $order['order_date'] ?></td>
                                    <td>
                                        <input type="radio" id="dispatched_<?= $oid ?>_<?= $item['product_id'] ?>"
                                            name="status_<?= $oid ?>_<?= $item['product_id'] ?>" value="dispatched"
                                            <?= $item['status'] == 'dispatched' ? 'checked' : '' ?>>
                                        <label for="dispatched_<?= $oid ?>_<?= $item['product_id'] ?>">Dispatched</label>
                                    </td>
                                    <td>
                                        <input type="radio" id="delivered_<?= $oid ?>_<?= $item['product_id'] ?>"
                                            name="status_<?= $oid ?>_<?= $item['product_id'] ?>" value="delivered"
                                            <?= $item['status'] == 'delivered' ? 'checked' : '' ?>>
                                        <label for="delivered_<?= $oid ?>_<?= $item['product_id'] ?>">Delivered</label>
                                    </td>
                                    <td>
                                        <button type="button"
                                            onclick="updateStatus('<?= $oid ?>', '<?= $item['product_id'] ?>')">Update</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript: Update Order Status
        function updateStatus(orderId, productId) {
            const status = document.querySelector(`input[name="status_${orderId}_${productId}"]:checked`).value;

            var formData = new FormData();
            formData.append("order_id", orderId);
            formData.append("product_id", productId);
            formData.append("status", status);

            fetch('update_order_status.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert(data.message);
                        console.log('Order status updated:', data.message);
                        // Optionally refresh the page or update UI to reflect the changes
                        // location.reload(); // Uncomment to reload the page
                    } else {
                        // Show error message
                        alert(data.message);
                        console.error('Error updating status:', data.message);
                    }
                })
                .catch(error => console.error('Error updating status:', error));
        }
    </script>

</body>

</html>