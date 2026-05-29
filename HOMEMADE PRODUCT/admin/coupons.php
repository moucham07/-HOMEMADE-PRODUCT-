<?php
require_once 'includes/admin_header.php';

// Handle Add Coupon
if (isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = (float)$_POST['value'];
    $expiry_date = $_POST['expiry_date'];
    $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;

    $stmt = $conn->prepare("INSERT INTO coupons (code, type, value, expiry_date, usage_limit) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$code, $type, $value, $expiry_date, $usage_limit]);
    redirect('coupons.php?msg=Coupon code created successfully');
}

// Handle Status Toggle
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $status = ($_GET['toggle_status'] === 'active') ? 'active' : 'inactive';
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("UPDATE coupons SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    redirect('coupons.php?msg=Coupon status updated successfully');
}

// Handle Delete Coupon
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('coupons.php?msg=Coupon deleted successfully');
}

// Fetch all coupons
$coupons_list = $conn->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
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
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-percent text-primary me-2"></i> Coupons & Discounts</h4>
        <small class="text-muted">Generate flat/percentage discount codes, establish expiry gates, and track coupon usages.</small>
    </div>
</div>

<div class="row">
    <!-- Add Coupon Form Card -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fa-solid fa-square-plus text-success me-2"></i> Generate Coupon</h5>
            
            <form action="coupons.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="code">Coupon Code</label>
                    <input type="text" id="code" name="code" class="form-control rounded-pill border px-3 py-2 text-dark font-monospace" required placeholder="e.g. SAVE20" style="text-transform: uppercase;">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="type">Discount Type</label>
                    <select id="type" name="type" class="form-select rounded-pill border px-3 py-2 text-dark" required>
                        <option value="percentage">Percentage Discount (%)</option>
                        <option value="flat">Flat Cash Discount (<?php echo getCurrencySymbol(); ?>)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="value">Discount Value</label>
                    <input type="number" step="0.01" id="value" name="value" class="form-control rounded-pill border px-3 py-2 text-dark" required placeholder="e.g. 10 or 150">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control rounded-pill border px-3 py-2 text-dark" required value="<?php echo date('Y-12-31'); ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="usage_limit">Total Usage Limit</label>
                    <input type="number" id="usage_limit" name="usage_limit" class="form-control rounded-pill border px-3 py-2 text-dark" placeholder="e.g. 100 (Leave blank for infinite)">
                </div>

                <button type="submit" name="add_coupon" class="btn btn-primary btn-premium w-100 rounded-pill py-2"><i class="fa-solid fa-circle-check me-2"></i> Save Coupon Code</button>
            </form>
        </div>
    </div>

    <!-- Coupons List Grid Table -->
    <div class="col-lg-8 mb-4">
        <div class="premium-table-card">
            <div class="premium-table-header">
                <h6 class="fw-bold mb-0 text-dark">Active Coupon Listings (<?php echo count($coupons_list); ?> total)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Coupon Code</th>
                            <th>Value / Rate</th>
                            <th>Expiry Date</th>
                            <th>Usage Status</th>
                            <th>Coupon Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($coupons_list) > 0): ?>
                            <?php foreach($coupons_list as $cp): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark font-monospace text-uppercase" style="font-size: 1.1rem;"><?php echo htmlspecialchars($cp['code']); ?></span></td>
                                    <td>
                                        <?php if ($cp['type'] === 'percentage'): ?>
                                            <span class="fw-bold text-primary"><i class="fa-solid fa-percent me-1"></i> <?php echo number_format($cp['value'], 0); ?>% Off</span>
                                        <?php else: ?>
                                            <span class="fw-bold text-success"><?php echo getCurrencySymbol() . number_format($cp['value'], 2); ?> Off</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $exp_time = strtotime($cp['expiry_date']);
                                        $is_expired = $exp_time < time();
                                        ?>
                                        <span class="badge rounded-pill <?php echo ($is_expired) ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-muted'; ?> fw-bold">
                                            <?php echo date('M d, Y', $exp_time); ?>
                                            <?php echo ($is_expired) ? ' (Expired)' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark font-monospace"><?php echo $cp['used_count']; ?></span>
                                        <span class="text-muted small">/ <?php echo !empty($cp['usage_limit']) ? $cp['usage_limit'] : '∞'; ?> used</span>
                                    </td>
                                    <td>
                                        <?php if ($cp['status'] === 'inactive'): ?>
                                            <span class="badge badge-premium bg-light text-muted border"><i class="fa-solid fa-eye-slash me-1"></i> Inactive</span>
                                        <?php else: ?>
                                            <span class="badge badge-premium bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <?php if ($cp['status'] === 'active'): ?>
                                                <a href="coupons.php?toggle_status=inactive&id=<?php echo $cp['id']; ?>" class="btn btn-outline-warning btn-floating btn-sm shadow-0" title="Deactivate Coupon"><i class="fa-solid fa-eye-slash"></i></a>
                                            <?php else: ?>
                                                <a href="coupons.php?toggle_status=active&id=<?php echo $cp['id']; ?>" class="btn btn-outline-success btn-floating btn-sm shadow-0" title="Activate Coupon"><i class="fa-solid fa-circle-check"></i></a>
                                            <?php endif; ?>
                                            <a href="coupons.php?delete=<?php echo $cp['id']; ?>" class="btn btn-outline-danger btn-floating btn-sm shadow-0" title="Delete Coupon" onclick="return confirm('Confirm deleting this coupon permanently?');"><i class="fa-solid fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-ticket fa-3x mb-3"></i>
                                    <p class="mb-0">No discount coupons found in database.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
