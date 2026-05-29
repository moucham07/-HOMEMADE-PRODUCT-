<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Calculate subtotal from DB
$subtotal = 0;
$stmt = $conn->prepare("SELECT c.quantity, p.id as product_id, p.title, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Fallback to mock data subtotal if cart is empty but user still accesses page
if (empty($cart_items) && $subtotal == 0) {
    $subtotal = 120.00; // Mock subtotal
}

$error = '';

// Process Order Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['zip']);
    $payment_method = $_POST['payment_method'] ?? 'credit_card';
    $user_id = $_SESSION['user_id'];
    
    if (!empty($cart_items)) {
        try {
            $conn->beginTransaction();
            
            // Calculate final total amount (including discount or taxes if any)
            $total_amount = $subtotal;
            if ($payment_method == 'paypal' || $payment_method == 'credit_card') {
                // Apply a small mock discount FIRST10 (-12.00) just like the UI displays:
                $total_amount = max(0, $subtotal - 12.00);
            }
            
            // Insert into orders table
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, 'completed', 'pending')");
            $stmt_order->execute([$user_id, $total_amount, $address, $payment_method]);
            $order_id = $conn->lastInsertId();
            
            // Move cart items to order_items
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) SELECT ?, c.product_id, p.seller_id, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
            $stmt_item->execute([$order_id, $user_id]);
            
            // Update product stock (decrement stock)
            foreach ($cart_items as $item) {
                $stmt_stock = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt_stock->execute([$item['product_id']]);
                $prod = $stmt_stock->fetch();
                if ($prod) {
                    $new_stock = max(0, $prod['stock'] - $item['quantity']);
                    $stmt_update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                    $stmt_update_stock->execute([$new_stock, $item['product_id']]);
                }
            }
            
            // Clear cart
            $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt_clear->execute([$user_id]);
            
            $conn->commit();
            
            $_SESSION['order_success'] = true;
            redirect('checkout.php'); // PRG pattern
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Failed to place order: " . $e->getMessage();
        }
    } else {
        $error = "Your cart is empty.";
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5 mt-4">
    <?php if(isset($_SESSION['order_success'])): ?>
        <div class="row justify-content-center">
            <div class="col-md-8 text-center py-5">
                <i class="fas fa-check-circle text-success mb-4" style="font-size: 5rem;"></i>
                <h1 class="font-weight-bold mb-3">Order Placed Successfully!</h1>
                <p class="lead text-muted mb-5">Thank you for supporting independent artisans. Your order #<?php echo rand(10000, 99999); ?> has been received and is being processed.</p>
                <a href="user/orders.php" class="btn btn-outline-secondary btn-lg me-2">View Order</a>
                <a href="index.php" class="btn btn-primary-custom btn-lg">Continue Shopping</a>
            </div>
        </div>
        <?php unset($_SESSION['order_success']); // Clear success flag ?>
    <?php else: ?>
    
    <h2 class="font-weight-bold mb-4" style="color: var(--secondary-color);">Checkout</h2>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <form action="checkout.php" method="POST">
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <h4 class="mb-4 fw-bold border-bottom pb-2">Shipping Details</h4>
                        <div class="row mb-4">
                            <div class="col">
                              <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="firstName" class="form-control" required />
                                <label class="form-label" for="firstName">First name</label>
                              </div>
                            </div>
                            <div class="col">
                              <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="lastName" class="form-control" required />
                                <label class="form-label" for="lastName">Last name</label>
                              </div>
                            </div>
                        </div>

                        <div class="form-outline mb-4" data-mdb-input-init>
                            <input type="text" id="address" name="address" class="form-control" required />
                            <label class="form-label" for="address">Address</label>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-4 mb-md-0">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="city" name="city" class="form-control" required />
                                    <label class="form-label" for="city">City</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="zip" name="zip" class="form-control" required />
                                    <label class="form-label" for="zip">Zip / Postal Code</label>
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-4 mt-5 fw-bold border-bottom pb-2">Payment Method</h4>
                        
                        <div class="my-3">
                            <div class="form-check mb-2">
                                <input id="credit" name="payment_method" type="radio" class="form-check-input" value="credit_card" checked required>
                                <label class="form-check-label" for="credit"><i class="far fa-credit-card me-2"></i> Credit Card (Stripe Mockup)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input id="paypal" name="payment_method" type="radio" class="form-check-input" value="paypal" required>
                                <label class="form-check-label" for="paypal"><i class="fab fa-paypal me-2 text-primary"></i> PayPal</label>
                            </div>
                        </div>

                        <div class="row gy-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="cc-name" class="form-control" placeholder="" required />
                                    <label class="form-label" for="cc-name">Name on card</label>
                                </div>
                                <small class="text-muted">Full name as displayed on card</small>
                            </div>

                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="cc-number" class="form-control" placeholder="" required />
                                    <label class="form-label" for="cc-number">Credit card number</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="cc-expiration" class="form-control" placeholder="MM/YY" required />
                                    <label class="form-label" for="cc-expiration">Expiration</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="cc-cvv" class="form-control" placeholder="" required />
                                    <label class="form-label" for="cc-cvv">CVV</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="btn btn-primary-custom btn-lg btn-block w-100" type="submit" name="place_order">Place Order</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded position-sticky" style="top: 100px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex justify-content-between align-items-center">
                        Your Order
                        <span class="badge bg-primary rounded-pill"><?php echo count($cart_items) > 0 ? count($cart_items) : 1; ?></span>
                    </h5>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <?php if(!empty($cart_items)): ?>
                            <?php foreach($cart_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-sm px-0">
                                    <div>
                                        <h6 class="my-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span class="text-muted">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Mock Item -->
                            <li class="list-group-item d-flex justify-content-between lh-sm px-0">
                                <div>
                                    <h6 class="my-0">Abstract Sunset Painting</h6>
                                    <small class="text-muted">Qty: 1</small>
                                </div>
                                <span class="text-muted">₹120.00</span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="list-group-item d-flex justify-content-between bg-light px-0 mt-3">
                            <div class="text-success fw-bold">
                                <h6 class="my-0">Promo code</h6>
                                <small>FIRST10</small>
                            </div>
                            <span class="text-success">−₹12.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Total (INR)</span>
                            <strong>₹<?php echo number_format(max(0, $subtotal - 12.00), 2); ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
