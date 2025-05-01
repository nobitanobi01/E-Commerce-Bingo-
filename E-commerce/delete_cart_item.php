<?php
session_start();
include("config_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);

    // 🟢 If user is logged in: delete from database
    if (isset($_SESSION['email'])) {
        $user_email = $_SESSION['email'];

        $sql = "DELETE FROM cart WHERE id = ? AND user_email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("is", $cart_id, $user_email);
            if ($stmt->execute()) {
                echo "Item deleted successfully.";
            } else {
                echo "Failed to delete item. SQL Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Failed to prepare statement.";
        }
    }

    // 🔴 If user is NOT logged in: delete from session
    else {
        if (isset($_SESSION['cart'][$cart_id])) {
            unset($_SESSION['cart'][$cart_id]);
            echo "Item deleted from session cart.";
        } else {
            echo "Item not found in session cart.";
        }
    }
} else {
    echo "Invalid request.";
}
?>
