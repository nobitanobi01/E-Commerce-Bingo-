<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = ''; // your DB password
$database = 'ecommerce'; // your DB name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get category name from POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category'])) {
    $name = trim($_POST['category']);

    if (!empty($name)) {
        // Check if the category already exists (case-insensitive)
        $checkStmt = $conn->prepare("SELECT id FROM categories WHERE LOWER(categories) = LOWER(?)");
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            header("location:add_categories.php?error=Category already exists.");
        } else {
            // Insert the new category
            $insertStmt = $conn->prepare("INSERT INTO categories (categories) VALUES (?)");
            $insertStmt->bind_param("s", $name);

            if ($insertStmt->execute()) {
             //   echo "Category added successfully!";
            } else {
                echo "Error: " . $insertStmt->error;
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    } else {
        header("location:add_categories.php?error=Category name cannot be empty.");
    }
} else {
    echo "";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add_Category</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=National+Park:wght@200..800&display=swap');
    body {
      font-family: 'National Park', sans-serif;
      background-color: #f2f2f2;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 400px;
      position: relative;
    }

    .form-container {
      background-color: white;
      padding: 20px 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 10px;
      font-weight: bold;
    }
    p{
      font-style:italic;
      font-size:12px;
      color:red;
    }
    input{
        padding: 5px;
        margin-bottom: 10px;
    }

    button {
      width: 40%;
      padding: 10px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #218838;
    }
    a{
      position: absolute;
      top: 2%;
      right: 2%;
      text-decoration: none;
      font-size: 18px;
    }
    a:hover{
      color:Black;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Categories</h2>

    <form  method="POST" action="add_categories.php">
      <label>Enter Category:</label>
      <p>i.e. Electronic,wearable,toys,fitness,clothing...</p>
      <input type="text" name="category" placeholder="Enter the category"/>
      
      <button type="submit">Add</button>
    </form>
  </div>
      <a href="add_product.php">Add Product</a>
  <script>
        const urlParams = new URLSearchParams(window.location.search);
        const errorMessage = urlParams.get('error');

        if (errorMessage) {
        
            // Show alert for specific critical errors
            if (errorMessage.includes("Category name cannot be empty.") || errorMessage.includes("Category already exists.") ) {
                alert(decodeURIComponent(errorMessage));
            }
            // Clean the URL after displaying the error
            if (window.history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
            }            
        }
    </script>
</body>
</html>

