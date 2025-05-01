<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ecommerce';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $category = (int)$_POST['category'];
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    // Handle image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = "uploads/" . time() . "_" . $imageName;

        // Create uploads folder if it doesn't exist
        if (!file_exists("uploads")) {
            mkdir("uploads", 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO product (name, category_id, description, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $name, $category, $description, $price, $imagePath);

            if ($stmt->execute()) {
                $message = "✅ Product added successfully!";
            } else {
                $message = "❌ Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "❌ Failed to upload image.";
        }
    } else {
        $message = "❌ Please upload a valid image.";
    }
}

// Fetch categories for dropdown
$categoryOptions = '';
$result = $conn->query("SELECT id, categories FROM categories");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categoryOptions .= "<option value='{$row['id']}'>{$row['categories']}</option>";
    }
} else {
    $categoryOptions = "<option disabled>No categories found</option>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add_Product</title>
    <style>
     @import url('https://fonts.googleapis.com/css2?family=National+Park:wght@200..800&display=swap');
    body {
        font-family: 'National Park', sans-serif;
        background-color: #f4f6f8;
        padding: 10px;
        display: flex;
        justify-content: center;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        background-color: #fff;
        padding: 20px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 25%;
    }

    label {
        font-weight: bold;
        display: block;
        color: #444;
    }

    input[type="text"],
    input[type="number"],
    select,
    input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        border-color: #007BFF;
        outline: none;
    }
    input[placeholder]{
        font-family: 'National Park', sans-serif;
        font-style:italic;
    }

    button {
        background-color: #007BFF;
        color: #fff;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #0056b3;
    }

    p strong {
        display: block;
        margin-bottom: 20px;
        color: green;
        text-align: left;
    }

    p strong:has-text('❌') {
        color: red;
    }
</style>

</head>
<body>



<?php if ($message): ?>
    <p><strong><?php echo $message; ?></strong></p>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
<h2>Add Product</h2>
    <label for="name">Product Name:</label><br>
    <input type="text" name="name" id="name" placeholder="Enter product name,company" required><br><br>

    <label for="category">Category:</label><br>
    <select name="category" id="category" required>
        <option value="">-- Select Category --</option>
        <?php echo $categoryOptions; ?>
    </select><br><br>

    <label for="description">Description:</label><br>
    <input type="text" name="description" id="description" placeholder="Enter product description" required><br><br>

    <label for="price">Price ($):</label><br>
    <input type="number" name="price" id="price" step="0.01"placeholder="Enter product price" required><br><br>

    <label for="image">Upload Image:</label><br>
    <input type="file" name="image" id="image" accept="image/*" required><br><br>

    <button type="submit">Add Product</button>
</form>
<script></script>
</body>
</html>
