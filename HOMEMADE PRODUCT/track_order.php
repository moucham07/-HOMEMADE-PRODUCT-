<?php
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<div class="container py-5 mt-4" style="min-height: 65vh;">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <i class="fas fa-shipping-fast fa-4x text-primary mb-4"></i>
            <h1 class="fw-bold mb-3">Track Your Order</h1>
            <p class="text-muted mb-4">Enter your Order ID and email address below to check the current status of your shipment.</p>
            
            <div class="card border-0 shadow-sm text-start">
                <div class="card-body p-4">
                    <form action="#" method="GET">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="order_id">Order ID</label>
                            <input type="text" id="order_id" name="order_id" class="form-control form-control-lg" placeholder="e.g. #ORD-12345" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold" for="email">Billing Email</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                        </div>
                        <button type="button" class="btn btn-primary-custom btn-lg w-100" onclick="alert('Order tracking integration coming soon!')">
                            <i class="fas fa-search me-2"></i> Track Order
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
