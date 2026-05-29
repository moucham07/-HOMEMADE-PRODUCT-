<?php
require_once 'includes/admin_header.php';

// ENFORCE Access restricted exclusively to Super Admin
if ($admin_role !== 'super_admin') {
    ?>
    <div class="access-denied-container d-flex flex-column align-items-center justify-content-center" style="min-height: 65vh;">
        <div class="card border-0 shadow-premium p-5 text-center bg-white" style="border-radius: 20px; max-width: 500px;">
            <div class="mb-4">
                <i class="fa-solid fa-shield-halved fa-5x text-danger"></i>
            </div>
            <h4 class="fw-bold text-dark">Super Admin Clearance Required</h4>
            <p class="text-muted">Your account (Editor privilege level) does not have authorization to modify payment gateway API keys, tax structures, or website branding configurations.</p>
            <a href="index.php" class="btn btn-primary btn-premium rounded-pill px-4 mt-3"><i class="fa-solid fa-circle-arrow-left me-2"></i> Return to Dashboard</a>
        </div>
    </div>
    <?php
    require_once 'includes/admin_footer.php';
    exit();
}

// Handle Settings Update
if (isset($_POST['save_settings'])) {
    $stmt_upd = $conn->prepare("UPDATE settings SET value = ? WHERE `key` = ?");

    // Save site logo
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['site_logo']['tmp_name'];
        $filename = 'logo_' . time() . '_' . basename($_FILES['site_logo']['name']);
        $target_dir = __DIR__ . '/../../assets/images/';
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $stmt_upd->execute([$filename, 'site_logo']);
        }
    }

    // Save all other settings fields
    $fields = [
        'site_name', 'site_footer', 'currency', 'tax_gst', 'tax_vat',
        'payment_gateway', 'paypal_client_id', 'paypal_secret',
        'stripe_key', 'stripe_secret', 'razorpay_key', 'razorpay_secret',
        'email_order_confirmation', 'email_failed_payment'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $stmt_upd->execute([$_POST[$field], $field]);
        }
    }
    
    redirect('settings.php?msg=Configurations updated successfully');
}
?>

<!-- Alerts -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Dashboard Section Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i> Site Settings & Configurations</h4>
        <small class="text-muted">Modify payment gateways, tax structures, brand details, and automated notification setups.</small>
    </div>
</div>

<form action="settings.php" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        
        <!-- Branding and Visual Settings Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-premium p-4 h-100" style="border-radius: 20px;">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fa-solid fa-paint-brush text-primary me-2"></i> Website Branding</h5>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="site_name">Website Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('site_name', 'ArtisanHaven')); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="site_footer">Copyright & Footer Description</label>
                    <textarea id="site_footer" name="site_footer" class="form-control rounded-4 border px-3 py-2 text-dark" rows="3" required><?php echo htmlspecialchars(getSetting('site_footer')); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="site_logo">Change Website Logo</label>
                    <input type="file" id="site_logo" name="site_logo" class="form-control rounded-pill border px-3 py-2 text-dark">
                    <?php 
                    $logo = getSetting('site_logo');
                    if ($logo): 
                    ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Active Logo Preview:</small>
                            <img src="/HOMEMADE PRODUCT/assets/images/<?php echo htmlspecialchars($logo); ?>" class="img-thumbnail rounded-4 p-2 bg-light" style="max-height: 50px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Taxes & Currencies Settings Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-premium p-4 h-100" style="border-radius: 20px;">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fa-solid fa-coins text-warning me-2"></i> Currency & Taxation</h5>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="currency">Store Default Currency</label>
                    <select id="currency" name="currency" class="form-select rounded-pill border px-3 py-2 text-dark" required>
                        <option value="INR" <?php echo (getSetting('currency') === 'INR') ? 'selected' : ''; ?>>INR (₹) Indian Rupee</option>
                        <option value="USD" <?php echo (getSetting('currency') === 'USD') ? 'selected' : ''; ?>>USD ($) US Dollar</option>
                        <option value="EUR" <?php echo (getSetting('currency') === 'EUR') ? 'selected' : ''; ?>>EUR (€) Euro</option>
                        <option value="GBP" <?php echo (getSetting('currency') === 'GBP') ? 'selected' : ''; ?>>GBP (£) Great British Pound</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold text-dark" for="tax_gst">GST Percentage (%)</label>
                        <input type="number" step="0.01" id="tax_gst" name="tax_gst" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('tax_gst', '18.00')); ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold text-dark" for="tax_vat">VAT Percentage (%)</label>
                        <input type="number" step="0.01" id="tax_vat" name="tax_vat" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('tax_vat', '5.00')); ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Gateway Credentials Card -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fa-solid fa-credit-card text-success me-2"></i> Payment Gateway Configurations</h5>
                
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="payment_gateway">Active Checkout Gateway</label>
                    <select id="payment_gateway" name="payment_gateway" class="form-select rounded-pill border px-3 py-2 text-dark" required>
                        <option value="paypal" <?php echo (getSetting('payment_gateway') === 'paypal') ? 'selected' : ''; ?>>PayPal Standard Integration</option>
                        <option value="stripe" <?php echo (getSetting('payment_gateway') === 'stripe') ? 'selected' : ''; ?>>Stripe Elements Checkout</option>
                        <option value="razorpay" <?php echo (getSetting('payment_gateway') === 'razorpay') ? 'selected' : ''; ?>>Razorpay Direct Payment</option>
                    </select>
                </div>

                <div class="row g-4">
                    <!-- Paypal Keys -->
                    <div class="col-lg-4">
                        <div class="p-3 border rounded-4 bg-light">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa-brands fa-paypal me-2"></i> PayPal Settings</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1" for="paypal_client_id">Client ID</label>
                                <input type="password" id="paypal_client_id" name="paypal_client_id" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('paypal_client_id')); ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted mb-1" for="paypal_secret">Client Secret</label>
                                <input type="password" id="paypal_secret" name="paypal_secret" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('paypal_secret')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Keys -->
                    <div class="col-lg-4">
                        <div class="p-3 border rounded-4 bg-light">
                            <h6 class="fw-bold text-info mb-3"><i class="fa-brands fa-stripe me-2"></i> Stripe Settings</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1" for="stripe_key">Publishable Key</label>
                                <input type="password" id="stripe_key" name="stripe_key" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('stripe_key')); ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted mb-1" for="stripe_secret">Secret Key</label>
                                <input type="password" id="stripe_secret" name="stripe_secret" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('stripe_secret')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Razorpay Keys -->
                    <div class="col-lg-4">
                        <div class="p-3 border rounded-4 bg-light">
                            <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-money-bill-transfer me-2"></i> Razorpay Settings</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1" for="razorpay_key">Key ID</label>
                                <input type="password" id="razorpay_key" name="razorpay_key" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('razorpay_key')); ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted mb-1" for="razorpay_secret">Key Secret</label>
                                <input type="password" id="razorpay_secret" name="razorpay_secret" class="form-control border rounded-pill px-3 py-2 text-dark" value="<?php echo htmlspecialchars(getSetting('razorpay_secret')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Automated Notification Templates Settings -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fa-solid fa-envelope-open-text text-info me-2"></i> Notification Templates</h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" for="email_order_confirmation">Order Confirmation Email Template</label>
                        <textarea id="email_order_confirmation" name="email_order_confirmation" class="form-control rounded-4 border px-3 py-2 text-dark font-monospace" rows="5" style="font-size: 0.85rem;" required><?php echo htmlspecialchars(getSetting('email_order_confirmation')); ?></textarea>
                        <small class="text-muted mt-2 d-block">Supported tokens: `{customer_name}`, `{order_id}`, `{total_amount}`</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" for="email_failed_payment">Failed Payment Notification Template</label>
                        <textarea id="email_failed_payment" name="email_failed_payment" class="form-control rounded-4 border px-3 py-2 text-dark font-monospace" rows="5" style="font-size: 0.85rem;" required><?php echo htmlspecialchars(getSetting('email_failed_payment')); ?></textarea>
                        <small class="text-muted mt-2 d-block">Supported tokens: `{customer_name}`, `{order_id}`</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-12 text-end mb-4">
            <button type="submit" name="save_settings" class="btn btn-primary btn-premium rounded-pill px-5 py-3 fw-bold shadow-lg"><i class="fa-solid fa-floppy-disk me-2"></i> Save System Settings</button>
        </div>
    </div>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
