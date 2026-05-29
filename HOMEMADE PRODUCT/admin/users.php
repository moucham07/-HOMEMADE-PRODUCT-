<?php
require_once 'includes/admin_header.php';

// Handle Action Toggles (Block / Unblock)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $uid = (int)$_GET['id'];
    
    // Safety check: Fetch target user role
    $stmt_chk = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_chk->execute([$uid]);
    $target_role = $stmt_chk->fetchColumn();
    
    if ($target_role === 'admin' && $admin_role !== 'super_admin') {
        redirect('users.php?err=Only Super Admin can manage administrator users');
    }
    
    if ($uid === (int)$_SESSION['user_id']) {
        redirect('users.php?err=You cannot block your own account');
    }
    
    if ($action === 'block') {
        $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
        $stmt->execute([$uid]);
        redirect('users.php?msg=User account blocked successfully');
    } elseif ($action === 'unblock') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$uid]);
        redirect('users.php?msg=User account unblocked successfully');
    }
}

// Search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['filter_role']) ? trim($_GET['filter_role']) : '';

$query_str = "SELECT id, name, email, role, status, created_at FROM users WHERE 1=1";
$params = [];

if ($search !== '') {
    $query_str .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_role !== '') {
    $query_str .= " AND role = ?";
    $params[] = $filter_role;
}

$query_str .= " ORDER BY id DESC";
$stmt_users = $conn->prepare($query_str);
$stmt_users->execute($params);
$users_list = $stmt_users->fetchAll();

// Handle Purchase history deep dive modal view
$history_orders = [];
$history_user = null;
if (isset($_GET['view_history'])) {
    $hist_uid = (int)$_GET['view_history'];
    $stmt_hu = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt_hu->execute([$hist_uid]);
    $history_user = $stmt_hu->fetch();
    
    if ($history_user) {
        $stmt_ho = $conn->prepare("SELECT o.id as order_id, o.total_amount, o.order_status, o.payment_status, o.created_at, oi.quantity, oi.price, p.title 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id 
            WHERE o.user_id = ? 
            ORDER BY o.id DESC");
        $stmt_ho->execute([$hist_uid]);
        $history_orders = $stmt_ho->fetchAll();
    }
}
?>

<!-- Message Banners -->
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
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-users-gear text-primary me-2"></i> User & Seller Management</h4>
        <small class="text-muted">Perform user role auditing, system account suspensions, and query shopping history.</small>
    </div>
</div>

<!-- Filters and Controls Card -->
<div class="card border-0 shadow-premium p-4 mb-4" style="border-radius: 20px;">
    <form action="users.php" method="GET" class="row g-3">
        <div class="col-md-5">
            <div class="input-group border rounded-pill px-3 py-1 bg-light">
                <i class="fa-solid fa-magnifying-glass text-muted d-flex align-items-center me-2"></i>
                <input type="text" name="search" class="form-control bg-transparent border-0 shadow-none text-dark" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="filter_role" class="form-select border rounded-pill px-3 py-2 text-dark">
                <option value="">All Account Roles</option>
                <option value="user" <?php echo ($filter_role === 'user') ? 'selected' : ''; ?>>Customer Only</option>
                <option value="seller" <?php echo ($filter_role === 'seller') ? 'selected' : ''; ?>>Sellers Only</option>
                <option value="admin" <?php echo ($filter_role === 'admin') ? 'selected' : ''; ?>>Administrators Only</option>
            </select>
        </div>
        <div class="col-md-3 d-flex g-2">
            <button type="submit" class="btn btn-primary btn-premium rounded-pill w-50 me-2"><i class="fa-solid fa-filter me-2"></i> Filter</button>
            <a href="users.php" class="btn btn-outline-secondary btn-premium rounded-pill w-50"><i class="fa-solid fa-rotate-left me-2"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Users List Grid Table -->
<div class="premium-table-card">
    <div class="premium-table-header">
        <h6 class="fw-bold mb-0 text-dark">All Registered Accounts (<?php echo count($users_list); ?> total)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-premium mb-0 align-middle">
            <thead>
                <tr>
                    <th>User Details</th>
                    <th>Email Address</th>
                    <th>Account Role</th>
                    <th>Account Status</th>
                    <th>Signed Up</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users_list) > 0): ?>
                    <?php foreach($users_list as $usr): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usr['name']); ?>&background=random" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($usr['name']); ?></span>
                                        <small class="text-muted font-monospace">UID: #<?php echo $usr['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="font-monospace text-muted"><?php echo htmlspecialchars($usr['email']); ?></span></td>
                            <td>
                                <?php if ($usr['role'] === 'admin'): ?>
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger fw-bold"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                                <?php elseif ($usr['role'] === 'seller'): ?>
                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning fw-bold"><i class="fa-solid fa-store me-1"></i> Seller</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fw-bold"><i class="fa-solid fa-cart-shopping me-1"></i> Customer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usr['status'] === 'blocked'): ?>
                                    <span class="badge badge-premium bg-danger text-white"><i class="fa-solid fa-ban me-1"></i> Blocked</span>
                                <?php else: ?>
                                    <span class="badge badge-premium bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted small"><?php echo date('M d, Y', strtotime($usr['created_at'])); ?></span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <a href="users.php?view_history=<?php echo $usr['id']; ?>" class="btn btn-light btn-floating btn-sm shadow-0" title="Purchase History"><i class="fa-solid fa-receipt text-secondary"></i></a>
                                    
                                    <?php if ($usr['id'] !== (int)$_SESSION['user_id']): ?>
                                        <?php if ($usr['status'] === 'blocked'): ?>
                                            <a href="users.php?action=unblock&id=<?php echo $usr['id']; ?>" class="btn btn-outline-success btn-floating btn-sm shadow-0" title="Activate Account" onclick="return confirm('Confirm activating this user account?');"><i class="fa-solid fa-unlock-keyhole"></i></a>
                                        <?php else: ?>
                                            <a href="users.php?action=block&id=<?php echo $usr['id']; ?>" class="btn btn-outline-danger btn-floating btn-sm shadow-0" title="Block Account" onclick="return confirm('Confirm suspending this user account?');"><i class="fa-solid fa-ban text-danger"></i></a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-large-slash fa-3x mb-3"></i>
                            <p class="mb-0">No registered users matched your current selection.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PURCHASE HISTORY MODAL OVERLAY -->
<?php if ($history_user): ?>
    <div class="modal fade show" id="historyModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--shadow-premium);">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1"><i class="fa-solid fa-receipt text-primary me-2"></i> Purchase History</h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($history_user['name']); ?> (<?php echo htmlspecialchars($history_user['email']); ?>)</p>
                    </div>
                    <a href="users.php" class="btn-close text-dark shadow-none" aria-label="Close"></a>
                </div>
                <div class="modal-body px-4 overflow-auto" style="max-height: 400px;">
                    <?php if (count($history_orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-muted small fw-bold">
                                        <th>Order ID</th>
                                        <th>Product Title</th>
                                        <th>Quantity</th>
                                        <th class="text-center">Payment Status</th>
                                        <th class="text-center">Order Status</th>
                                        <th class="text-end">Amount Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history_orders as $ord): ?>
                                        <tr>
                                            <td class="font-monospace fw-bold">#<?php echo $ord['order_id']; ?></td>
                                            <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($ord['title']); ?></span></td>
                                            <td class="text-center"><span class="badge rounded-pill bg-light text-dark fw-bold"><?php echo $ord['quantity']; ?></span></td>
                                            <td class="text-center">
                                                <span class="badge badge-premium bg-<?php echo ($ord['payment_status'] === 'completed') ? 'success' : 'warning'; ?> text-white text-uppercase" style="font-size: 0.65rem;">
                                                    <?php echo $ord['payment_status']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-secondary rounded-pill font-weight-bold" style="font-size: 0.75rem;">
                                                    <?php echo ucfirst($ord['order_status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-success"><?php echo getCurrencySymbol() . number_format($ord['quantity'] * $ord['price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-basket-shopping fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">This user has not placed any orders yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <a href="users.php" class="btn btn-outline-secondary btn-premium rounded-pill px-4">Close Window</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
