<?php
// Database connection
include("config_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $status = $_POST['status'];

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE orders SET status = ?, status_updated_at = NOW() WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("sii", $status, $order_id, $product_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product details updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating product details.']);
    }

    $stmt->close();
    $conn->close();
}
?>
