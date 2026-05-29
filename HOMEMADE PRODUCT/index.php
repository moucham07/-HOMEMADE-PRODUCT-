<?php
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-3">Discover Authentic Handcrafted Treasures</h1>
        <p class="lead">Support independent artisans and find unique, premium products.</p>
        <a href="products.php" class="btn btn-primary-custom btn-lg mt-3">Explore Products</a>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <h2 class="text-center mb-5 font-weight-bold" style="color: var(--secondary-color);">Shop by Category</h2>
        <div class="row g-4">
            <!-- Category 1 -->
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 product-card text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1599643478524-fb66f70d00f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80') center/cover;">
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <h4 class="card-title fw-bold">Handmade Jewelry</h4>
                    </div>
                    <a href="products.php?category=1" class="stretched-link"></a>
                </div>
            </div>
            <!-- Category 2 -->
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 product-card text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80') center/cover;">
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <h4 class="card-title fw-bold">Home Decor</h4>
                    </div>
                    <a href="products.php?category=2" class="stretched-link"></a>
                </div>
            </div>
            <!-- Category 3 -->
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 product-card text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80') center/cover;">
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <h4 class="card-title fw-bold">Paintings</h4>
                    </div>
                    <a href="products.php?category=3" class="stretched-link"></a>
                </div>
            </div>
            <!-- Category 4 -->
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 product-card text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1603006905003-be475563bc59?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80') center/cover;">
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <h4 class="card-title fw-bold">Candles</h4>
                    </div>
                    <a href="products.php?category=4" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container py-4">
        <h2 class="text-center mb-5 font-weight-bold" style="color: var(--secondary-color);">Featured Handcrafts</h2>
        
        <?php
        // Fetch featured products (mocked logic or empty for now, will pull from DB)
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name, u.name as seller_name FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id WHERE p.is_featured = TRUE LIMIT 4");
        $stmt->execute();
        $featured_products = $stmt->fetchAll();
        ?>
        
        <div class="row g-4">
            <?php if (count($featured_products) > 0): ?>
                <?php foreach($featured_products as $product): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card product-card h-100">
                            <span class="product-badge">Featured</span>
                            <div class="product-image-container">
                                <!-- Fallback image if no product images exist yet -->
                                <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Product">
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <p class="small text-muted mb-2">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Dummy Products for UI demonstration since DB is empty -->
                <?php for($i=1; $i<=4; $i++): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card product-card h-100">
                            <span class="product-badge">Trending</span>
                            <div class="product-image-container">
                                <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Mock Product">
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-1">Pottery</p>
                                <h5 class="product-title">Handcrafted Ceramic Vase <?php echo $i; ?></h5>
                                <p class="small text-muted mb-2">by Artisan Jane</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="product-price">₹45.00</span>
                                    <a href="product-detail.php?id=0" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Artisan Spotlight -->
<section class="py-5" style="background-color: var(--secondary-color); color: var(--white);">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="https://images.unsplash.com/photo-1518384401463-d38761b3fee7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Artisan" class="img-fluid rounded shadow-lg" style="border: 5px solid var(--primary-color);">
            </div>
            <div class="col-md-6">
                <h2 class="font-weight-bold mb-4">Meet the Artisans</h2>
                <p class="lead mb-4">Every product on ArtisanHaven has a story. We connect you directly with independent makers, artists, and craftsmen who pour their heart into every piece.</p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> 100% Authentic Handcrafted Items</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> Direct Support to Small Businesses</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> Sustainable & Ethical Production</li>
                </ul>
                <a href="register.php?role=seller" class="btn btn-primary-custom btn-lg">Join as an Artisan</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
