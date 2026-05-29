<?php
require_once __DIR__ . '/config/config.php';

if (!isLoggedIn()) {
    die("Error: You must be logged in to download digital files. <a href='login.php'>Login here</a>");
}

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($product_id <= 0) {
    die("Error: Invalid Product ID.");
}

// Fetch product details
$stmt_p = $conn->prepare("SELECT title, is_digital, digital_file_url FROM products WHERE id = ?");
$stmt_p->execute([$product_id]);
$product = $stmt_p->fetch();

if (!$product) {
    die("Error: Product not found.");
}

if (!$product['is_digital'] || empty($product['digital_file_url'])) {
    die("Error: Selected product is not a digital download.");
}

$user_id = $_SESSION['user_id'];
$user_role = getUserRole();

// Security Gate check:
// The user is allowed to download ONLY if they are an Admin (Super Admin/Editor) OR if they have a completed order purchase for this product!
$has_access = false;

if ($user_role === 'admin') {
    $has_access = true;
} else {
    $stmt_access = $conn->prepare("SELECT COUNT(*) FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'completed'");
    $stmt_access->execute([$user_id, $product_id]);
    if ($stmt_access->fetchColumn() > 0) {
        $has_access = true;
    }
}

if (!$has_access) {
    die("Access Denied: You have not purchased this digital product or your payment status is pending. Please complete your transaction to access download assets.");
}

// File Stream Downloader
$filepath = __DIR__ . '/uploads/digital_products/' . $product['digital_file_url'];

if (!file_exists($filepath)) {
    die("Error: The secure digital file is temporarily unavailable on our storage servers. Please contact administrator support.");
}

// Stream headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($product['digital_file_url']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
flush(); // Flush system output buffer
readfile($filepath);
exit;
