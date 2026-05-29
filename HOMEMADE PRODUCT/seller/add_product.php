<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'seller') {
    redirect('../login.php');
}

$seller_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch categories for the form
$stmt = $conn->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $materials = trim($_POST['materials'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($category_id) || empty($price)) {
        $error = "Title, Category, and Price are required.";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (seller_id, category_id, title, description, materials, price, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$seller_id, $category_id, $title, $description, $materials, $price, $stock])) {
                $success = "Product added successfully! <a href='products.php' class='alert-link'>Go back to My Products</a>";
            } else {
                $error = "Failed to add product. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">Add New Product</h1>
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

                            <form action="add_product.php" method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="title">Product Title</label>
                                    <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Handmade Ceramic Mug">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold" for="category_id">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="" disabled selected>Select a category</option>
                                            <?php foreach($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="form-label fw-bold" for="price">Price (₹)</label>
                                        <input type="number" step="0.01" id="price" name="price" class="form-control" required min="0">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="form-label fw-bold" for="stock">Initial Stock</label>
                                        <input type="number" id="stock" name="stock" class="form-control" required min="0" value="1">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="materials">Materials Used</label>
                                    <input type="text" id="materials" name="materials" class="form-control" placeholder="e.g. Clay, Glaze, Paint">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="description">Detailed Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="5" required placeholder="Describe your product's unique features, size, and care instructions..."></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">Product Image</label>
                                    <p class="small text-muted mb-2">Image uploading will be integrated soon. A beautiful placeholder image will automatically be used for now.</p>
                                </div>

                                <button type="submit" class="btn btn-primary-custom btn-lg w-100"><i class="fas fa-check-circle me-2"></i> Create Product</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
