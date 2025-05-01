<?php
session_start();
include('config_db.php'); // Ensure this sets up $conn as your MySQLi connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: signin.html?error=Invalid email format");
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: signin.html?error=Password must be at least 6 characters");
        exit;
    }

    // Password complexity check
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars) {
        header("Location: signin.html?error=Password must include uppercase, lowercase, number, and special character");
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Determine redirect page
    $redirectPage = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'bingo_home.php';

    if ($user) {
        // User exists, check password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            unset($_SESSION['redirect_after_login']);

            // 🔄 Merge session cart to DB
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $product_id => $item) {
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $total = $quantity * $price;

                    $check_sql = "SELECT id, quantity FROM cart WHERE user_email = ? AND product_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("si", $email, $product_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        $row = $check_result->fetch_assoc();
                        $new_quantity = $row['quantity'] + $quantity;
                        $new_total = $price * $new_quantity;

                        $update = $conn->prepare("UPDATE cart SET quantity = ?, total = ? WHERE id = ?");
                        $update->bind_param("idi", $new_quantity, $new_total, $row['id']);
                        $update->execute();
                    } else {
                        $insert = $conn->prepare("INSERT INTO cart (user_email, product_id, quantity, total) VALUES (?, ?, ?, ?)");
                        $insert->bind_param("siid", $email, $product_id, $quantity, $total);
                        $insert->execute();
                    }
                }
                unset($_SESSION['cart']);
            }

            header("Location: $redirectPage");
            exit;
        } else {
            header("Location: signin.html?error=Credentials do not match");
            exit;
        }
    } else {
        // Register new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert_stmt = $conn->prepare("INSERT INTO user (email, password) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $email, $hashedPassword);
        $insert_stmt->execute();

        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $conn->insert_id;
        unset($_SESSION['redirect_after_login']);

        // 🔄 Merge session cart to DB (for new users too)
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $quantity = $item['quantity'];
                $price = $item['price'];
                $total = $quantity * $price;

                $insert = $conn->prepare("INSERT INTO cart (user_email, product_id, quantity, total) VALUES (?, ?, ?, ?)");
                $insert->bind_param("siid", $email, $product_id, $quantity, $total);
                $insert->execute();
            }
            unset($_SESSION['cart']);
        }

        header("Location: $redirectPage");
        exit;
    }
} else {
    header("Location: signin.html?error=Invalid request");
    exit;
}
?>
