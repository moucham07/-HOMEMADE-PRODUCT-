<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'seller') {
    redirect('../login.php');
}

$seller_id = $_SESSION['user_id'];
$success = '';
$error = '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch categories
$stmt = $conn->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $materials = trim($_POST['materials'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($category_id) || empty($price)) {
        $error = "Title, Category, and Price are required.";
    } else {
        // Ensure product belongs to seller
        $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
        $check_stmt->execute([$product_id, $seller_id]);
        
        if ($check_stmt->fetch()) {
            try {
                $update_stmt = $conn->prepare("
                    UPDATE products 
                    SET category_id = ?, title = ?, description = ?, materials = ?, price = ?, stock = ? 
                    WHERE id = ?
                ");
                if ($update_stmt->execute([$category_id, $title, $description, $materials, $price, $stock, $product_id])) {
                    $success = "Product updated successfully! <a href='products.php' class='alert-link'>Go back to My Products</a>";
                } else {
                    $error = "Failed to update product. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Unauthorized to edit this product.";
        }
    }
}

// Fetch Product Data to pre-fill the form
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch();
    if (!$product) {
        redirect('products.php'); // Invalid product or not owned by seller
    }
} else {
    redirect('products.php');
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">Edit Product</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Products
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="title">Product Title</label>
                                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($product['title'] ?? ''); ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold" for="category_id">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="" disabled>Select a category</option>
                                            <?php foreach($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="form-label fw-bold" for="price">Price (₹)</label>
                                        <input type="number" step="0.01" id="price" name="price" class="form-control" required min="0" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="form-label fw-bold" for="stock">Current Stock</label>
                                        <input type="number" id="stock" name="stock" class="form-control" required min="0" value="<?php echo htmlspecialchars($product['stock'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="materials">Materials Used</label>
                                    <input type="text" id="materials" name="materials" class="form-control" value="<?php echo htmlspecialchars($product['materials'] ?? ''); ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="description">Detailed Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary-custom btn-lg w-100"><i class="fas fa-save me-2"></i> Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
