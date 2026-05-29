<?php
require_once 'config/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, u.name as seller_name FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="container py-5 mt-4">
    <?php if ($product || $product_id === 0): ?>
        <div class="row gx-5">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded" alt="Product Image" style="width: 100%; height: 500px; object-fit: cover;">
                    </div>
                </div>
                <div class="d-flex mt-3 gap-2">
                    <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; cursor:pointer;">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" class="img-thumbnail opacity-50" style="width: 80px; height: 80px; object-fit: cover; cursor:pointer;">
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none">Shop</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $product ? htmlspecialchars($product['category_name']) : 'Art & Canvas'; ?></li>
                  </ol>
                </nav>

                <h1 class="fw-bold mb-2"><?php echo $product ? htmlspecialchars($product['title']) : 'Abstract Sunset Painting'; ?></h1>
                <p class="text-muted mb-4">Handcrafted by <span class="fw-bold text-dark"><?php echo $product ? htmlspecialchars($product['seller_name']) : 'Studio Artisan'; ?></span></p>
                
                <div class="mb-4">
                    <span class="h2 fw-bold text-primary" style="color: var(--primary-color) !important;">₹<?php echo $product ? number_format($product['price'], 2) : '120.00'; ?></span>
                    <span class="ms-2 badge bg-success">In Stock</span>
                </div>

                <p class="lead mb-4" style="font-size: 1.1rem;">
                    <?php echo $product ? nl2br(htmlspecialchars($product['description'])) : 'This beautiful abstract painting is created using premium acrylic colors on a hand-stretched canvas. Perfect for modern home decor, bringing warmth and color to any room.'; ?>
                </p>

                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Materials: <?php echo $product ? htmlspecialchars($product['materials']) : 'Acrylic, Canvas, Wood Frame'; ?></li>
                    <li class="mb-2"><i class="fas fa-shipping-fast text-secondary me-2"></i> Ships worldwide within 3-5 business days</li>
                    <li class="mb-2"><i class="fas fa-undo text-secondary me-2"></i> 14-day return policy</li>
                </ul>

                <form action="cart.php" method="POST" class="mb-5">
                    <input type="hidden" name="product_id" value="<?php echo $product ? $product['id'] : 0; ?>">
                    <div class="row align-items-center gx-3">
                        <div class="col-auto">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product ? $product['stock'] : 10; ?>" style="width: 80px;">
                        </div>
                        <div class="col-auto mt-4">
                            <button type="submit" class="btn btn-primary-custom btn-lg"><i class="fas fa-cart-plus me-2"></i> Add to Cart</button>
                        </div>
                        <div class="col-auto mt-4">
                            <button type="button" class="btn btn-outline-danger btn-lg px-3"><i class="fas fa-heart"></i></button>
                        </div>
                    </div>
                </form>

                <!-- Reviews Tab -->
                <div class="mt-5">
                    <h4 class="fw-bold border-bottom pb-2">Customer Reviews</h4>
                    <div class="d-flex mb-3 mt-4">
                        <div class="text-warning me-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span>4.5 out of 5 (12 reviews)</span>
                    </div>
                    <div class="card border-0 bg-light p-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Jane Doe</span>
                            <span class="text-muted small">Oct 12, 2025</span>
                        </div>
                        <p class="mb-0 mt-2">Absolutely love this piece! The colors are so vibrant in person and it fits perfectly in my living room. Fast shipping too.</p>
                    </div>
                </div>

            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">Product not found.</div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
