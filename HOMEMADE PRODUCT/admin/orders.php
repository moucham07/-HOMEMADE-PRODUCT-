<?php
require_once 'includes/admin_header.php';

// Handle Order Status Update
if (isset($_POST['update_status'])) {
    $oid = (int)$_POST['id'];
    $status = $_POST['order_status'];

    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$status, $oid]);
    redirect('orders.php?msg=Order status updated successfully');
}

// Handle Order Refunds
if (isset($_GET['refund_id'])) {
    $oid = (int)$_GET['refund_id'];

    // Fetch order total and current payment status
    $stmt_o = $conn->prepare("SELECT total_amount, payment_status FROM orders WHERE id = ?");
    $stmt_o->execute([$oid]);
    $order = $stmt_o->fetch();

    if ($order) {
        if ($order['payment_status'] === 'completed') {
            $stmt_r = $conn->prepare("UPDATE orders SET payment_status = 'refunded', refund_status = 'refunded', refund_amount = ? WHERE id = ?");
            $stmt_r->execute([$order['total_amount'], $oid]);
            redirect('orders.php?msg=Refund processed successfully. Refund amount of ' . getCurrencySymbol() . number_format($order['total_amount'], 2) . ' credited back to the customer gateway account.');
        } else {
            redirect('orders.php?err=Only completed orders can be refunded.');
        }
    } else {
        redirect('orders.php?err=Order not found.');
    }
}

// Fetch Search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query_str = "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $query_str .= " AND (o.id = ? OR o.transaction_id LIKE ?)";
    $params[] = $search;
    $params[] = "%$search%";
}

$query_str .= " ORDER BY o.id DESC";
$stmt_orders = $conn->prepare($query_str);
$stmt_orders->execute($params);
$orders_list = $stmt_orders->fetchAll();
?>

<!-- Message Alerts -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($_GET['err']); ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Dashboard Section Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-receipt text-primary me-2"></i> Order & Payment Management</h4>
        <small class="text-muted">Monitor client transaction records, control order statuses, and process payment refunds.</small>
    </div>
    <div class="bg-white border rounded-pill px-4 py-2 shadow-sm">
        <span class="small text-muted fw-bold me-2">Active Configs:</span>
        <span class="badge rounded-pill bg-light text-dark me-2">GST: <?php echo getSetting('tax_gst', '18.00'); ?>%</span>
        <span class="badge rounded-pill bg-light text-dark me-2">VAT: <?php echo getSetting('tax_vat', '5.00'); ?>%</span>
        <span class="badge rounded-pill bg-primary"><?php echo getSetting('currency', 'INR'); ?> (<?php echo getCurrencySymbol(); ?>)</span>
    </div>
</div>

<!-- Search Panel -->
<div class="card border-0 shadow-premium p-4 mb-4" style="border-radius: 20px;">
    <form action="orders.php" method="GET" class="d-flex">
        <div class="input-group border rounded-pill px-3 py-1 bg-light w-75 me-2">
            <i class="fa-solid fa-magnifying-glass text-muted d-flex align-items-center me-2"></i>
            <input type="text" name="search" class="form-control bg-transparent border-0 shadow-none text-dark" placeholder="Search orders by Order ID or Transaction ID..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-premium rounded-pill w-25"><i class="fa-solid fa-search"></i> Search</button>
    </form>
</div>

<!-- Orders Grid Table -->
<div class="premium-table-card">
    <div class="premium-table-header">
        <h6 class="fw-bold mb-0 text-dark">Order History Logs (<?php echo count($orders_list); ?> orders)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-premium mb-0 align-middle">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Date Placed</th>
                    <th>Transaction ID</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Total Price</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders_list) > 0): ?>
                    <?php foreach ($orders_list as $ord): ?>
                        <tr>
                            <td class="font-monospace fw-bold text-dark">#<?php echo $ord['id']; ?></td>
                            <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($ord['customer_name']); ?></span></td>
                            <td><span class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($ord['created_at'])); ?></span></td>
                            <td><span class="font-monospace text-muted" style="font-size: 0.8rem;"><?php echo !empty($ord['transaction_id']) ? htmlspecialchars($ord['transaction_id']) : 'COD / Offline'; ?></span></td>
                            <td>
                                <?php if ($ord['payment_status'] === 'completed'): ?>
                                    <span class="badge badge-premium bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Paid</span>
                                <?php elseif ($ord['payment_status'] === 'refunded'): ?>
                                    <span class="badge badge-premium bg-danger text-white"><i class="fa-solid fa-arrow-rotate-left me-1"></i> Refunded</span>
                                <?php else: ?>
                                    <span class="badge badge-premium bg-warning text-white"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="orders.php" method="POST" class="d-inline-block">
                                    <input type="hidden" name="id" value="<?php echo $ord['id']; ?>">
                                    <select name="order_status" class="form-select form-select-sm border rounded-pill text-dark" style="font-size: 0.8rem; font-weight: 600;" onchange="this.form.submit();">
                                        <option value="pending" <?php echo ($ord['order_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($ord['order_status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo ($ord['order_status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo ($ord['order_status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo ($ord['order_status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="fw-bold text-dark"><?php echo getCurrencySymbol() . number_format($ord['total_amount'], 2); ?></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <?php if ($ord['payment_status'] === 'completed'): ?>
                                        <a href="orders.php?refund_id=<?php echo $ord['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill btn-premium" title="Refund order" onclick="return confirm('WARNING: Are you sure you want to process a refund for this order? This will cancel checkout access and credit client account.');"><i class="fa-solid fa-arrow-rotate-left me-1"></i> Refund</a>
                                    <?php elseif ($ord['payment_status'] === 'refunded'): ?>
                                        <small class="text-muted d-block font-monospace">Refunded: <?php echo getCurrencySymbol() . number_format($ord['refund_amount'], 2); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted small">No actions</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-receipt fa-3x mb-3"></i>
                            <p class="mb-0">No order logs found in database.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
