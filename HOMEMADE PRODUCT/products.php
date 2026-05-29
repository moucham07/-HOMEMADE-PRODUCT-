<?php
require_once 'config/config.php';
require_once 'includes/header.php';

// Fetch categories for filter
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Fetch products based on category if set
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
if ($category_id) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, u.name as seller_name FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id WHERE p.category_id = ?");
    $stmt->execute([$category_id]);
} else {
    $stmt = $conn->query("SELECT p.*, c.name as category_name, u.name as seller_name FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id");
}
$products = $stmt->fetchAll();
?>

<div class="container py-5 mt-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Filters</h5>
                    
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="products.php" class="text-decoration-none text-muted <?php echo !$category_id ? 'fw-bold text-primary' : ''; ?>">All Products</a>
                        </li>
                        <?php foreach($categories as $cat): ?>
                        <li class="mb-2">
                            <a href="products.php?category=<?php echo $cat['id']; ?>" class="text-decoration-none text-muted <?php echo $category_id == $cat['id'] ? 'fw-bold text-primary' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <!-- Dummy Categories for UI -->
                        <?php if(empty($categories)): ?>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Handmade Jewelry</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Home Decor</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Paintings</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Pottery</a></li>
                        <?php endif; ?>
                    </ul>

                    <h6 class="fw-bold mt-4 mb-3">Price Range</h6>
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control form-control-sm me-2" placeholder="Min">
                        <span>-</span>
                        <input type="number" class="form-control form-control-sm ms-2" placeholder="Max">
                    </div>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-3">Apply Filter</button>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">All Handcrafts</h4>
                <select class="form-select w-auto">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>

            <div class="row g-4">
                <?php if(count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card product-card h-100">
                                <div class="product-image-container">
                                    <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Product">
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <p class="small text-muted mb-2">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary-custom btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Dummy Products for UI -->
                    <?php for($i=1; $i<=6; $i++): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card product-card h-100 border-0 shadow-sm">
                                <div class="product-image-container">
                                    <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Mock Product">
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-1">Art & Canvas</p>
                                    <h5 class="product-title">Abstract Painting <?php echo $i; ?></h5>
                                    <p class="small text-muted mb-2">by Studio Artisan</p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="product-price">₹120.00</span>
                                        <a href="product-detail.php?id=0" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fas fa-shopping-cart"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <nav class="mt-5" aria-label="Page navigation">
              <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                  <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </nav>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
