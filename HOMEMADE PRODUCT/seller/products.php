<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'seller') {
    redirect('../login.php');
}

$seller_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Product Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Ensure the product belongs to the seller
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
    $check_stmt->execute([$delete_id, $seller_id]);
    
    if ($check_stmt->fetch()) {
        try {
            $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            if ($del_stmt->execute([$delete_id])) {
                $success = "Product deleted successfully.";
            } else {
                $error = "Failed to delete product.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Unauthorized action or product not found.";
    }
}

// Fetch Seller's Products
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.seller_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">My Products</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_product.php" class="btn btn-primary-custom">
                        <i class="fas fa-plus me-2"></i> Add New Product
                    </a>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?php if (count($products) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $p): ?>
                                        <tr>
                                            <td>
                                                <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=50&h=50&q=80" class="rounded" alt="Product Placeholder">
                                            </td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($p['title']); ?></td>
                                            <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                                            <td>₹<?php echo number_format($p['price'], 2); ?></td>
                                            <td>
                                                <?php if ($p['stock'] > 5): ?>
                                                    <span class="badge bg-success">In Stock (<?php echo $p['stock']; ?>)</span>
                                                <?php elseif ($p['stock'] > 0): ?>
                                                    <span class="badge bg-warning text-dark">Low Stock (<?php echo $p['stock']; ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="products.php?delete_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <h4>You haven't added any products yet.</h4>
                            <p class="text-muted">Start building your store inventory by adding your first handcrafted product.</p>
                            <a href="add_product.php" class="btn btn-primary-custom mt-3"><i class="fas fa-plus me-2"></i>Add First Product</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
