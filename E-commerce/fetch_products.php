<?php
include 'config_db.php'; // adjust path as needed

$sql = "SELECT p.*, c.categories AS category_name
        FROM product p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1";

// Filter by category_id
if (!empty($_GET['category'])) {
    $categories = array_map('intval', $_GET['category']); // integers for IDs
    $sql .= " AND p.category_id IN (" . implode(",", $categories) . ")";
}

// Sorting
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }
}

$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <a href="product_details.php?id=<?= $row['id']; ?>&category=<?= $row['category_id']; ?>">
        <div class="product-card">
            <img src="<?= $row['image']; ?>" alt="<?= $row['name']; ?>">
            <h2><?= $row['name']; ?></h2>
            <p>Category: <?= $row['category_name']; ?></p>
            <p><?= $row['description']; ?></p>
            <p class="price">Price: $<?= number_format($row['price'], 2); ?></p>
            <p>Added on: <?= date('d M Y', strtotime($row['created_at'])); ?></p>
            <div class="btn"><a href="product_details.php?id=<?= $row['id']; ?>&category=<?= $row['category_id']; ?>">Shop Now..</a></div>
        </div>
    </a>
<?php
    endwhile;
else:
    echo "<p>No products found.</p>";
endif;
?>
