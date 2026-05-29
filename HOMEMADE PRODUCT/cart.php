<?php
require_once 'config/config.php';

// Handle Add to Cart logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    if (!isLoggedIn()) {
        redirect('login.php'); // Force login to use cart for simplicity in this prototype
    }
    
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $user_id = $_SESSION['user_id'];
    
    if ($product_id > 0) {
        // Check if already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        if ($existing) {
            $new_quantity = $existing['quantity'] + $quantity;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->execute([$new_quantity, $existing['id']]);
        } else {
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->execute([$user_id, $product_id, $quantity]);
        }
        redirect('cart.php');
    }
}

// Handle Update Cart Quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity']) && isset($_POST['cart_id'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    if ($cart_id > 0 && $quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }
    redirect('cart.php');
}

// Handle Remove from Cart
if (isset($_GET['remove']) && isLoggedIn()) {
    $remove_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$remove_id, $_SESSION['user_id']]);
    redirect('cart.php');
}

// Fetch Cart Items
$cart_items = [];
$subtotal = 0;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.title, p.price, cat.name as category_name, u.name as seller_name FROM cart c JOIN products p ON c.product_id = p.id JOIN categories cat ON p.category_id = cat.id JOIN users u ON p.seller_id = u.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5 mt-4" style="min-height: 60vh;">
    <h2 class="font-weight-bold mb-4" style="color: var(--secondary-color);">Your Shopping Cart</h2>

    <?php if(!isLoggedIn()): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4>Please <a href="login.php">login</a> to view your cart.</h4>
        </div>
    <?php elseif(empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h4>Your cart is empty.</h4>
            <a href="products.php" class="btn btn-primary-custom mt-3">Start Shopping</a>
        </div>
        
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <?php foreach($cart_items as $item): ?>
                            <div class="row align-items-center mb-4">
                                <div class="col-md-2 col-3">
                                    <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" class="img-fluid rounded" alt="Product">
                                </div>
                                <div class="col-md-5 col-5">
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                    <p class="text-muted small mb-0">Seller: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                </div>
                                <div class="col-md-2 col-4">
                                    <form action="cart.php" method="POST" class="d-inline">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="hidden" name="update_quantity" value="1">
                                        <input type="number" name="quantity" class="form-control form-control-sm" value="<?php echo $item['quantity']; ?>" min="1" onchange="this.form.submit()">
                                    </form>
                                </div>
                                <div class="col-md-2 text-end col-6 mt-3 mt-md-0">
                                    <span class="fw-bold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                                <div class="col-md-1 text-end col-6 mt-3 mt-md-0">
                                    <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn btn-link text-danger p-0"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                            <hr class="text-muted">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded position-sticky" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success fw-bold">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Taxes</span>
                            <span class="fw-bold">₹0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold h5">Total</span>
                            <span class="fw-bold h4" style="color: var(--primary-color);">₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Promo code">
                                <button class="btn btn-outline-secondary" type="button">Apply</button>
                            </div>
                        </div>
                        <a href="checkout.php" class="btn btn-primary-custom btn-block w-100 py-3">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
