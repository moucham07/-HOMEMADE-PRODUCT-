<?php
require_once 'includes/admin_header.php';

// Handle Action callback approvals/rejections
if (isset($_GET['approve_seller'])) {
    $seller_id = (int)$_GET['approve_seller'];
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'seller'");
    $stmt->execute([$seller_id]);
    redirect('index.php?msg=Seller approved successfully');
}

if (isset($_GET['reject_seller'])) {
    $seller_id = (int)$_GET['reject_seller'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'seller'");
    $stmt->execute([$seller_id]);
    redirect('index.php?msg=Seller request deleted');
}

// Fetch dynamic counts
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_sellers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'")->fetchColumn();
$total_revenue = $total_revenue ? (float)$total_revenue : 0.00;

// Fetch sales chart data (last 7 days completed orders)
$sales_query = $conn->query("SELECT DATE(created_at) as sale_date, SUM(total_amount) as daily_sum FROM orders WHERE payment_status = 'completed' GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 7");
$sales_data = $sales_query->fetchAll();
$sales_data = array_reverse($sales_data);

$labels = [];
$values = [];
foreach ($sales_data as $sd) {
    $labels[] = date('M d', strtotime($sd['sale_date']));
    $values[] = (float)$sd['daily_sum'];
}

// Default dummy values for premium design look if DB has no orders yet
if (empty($labels)) {
    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $values = [14500, 23000, 19800, 31000, 42000, 38000, 49200];
}

// Fetch pending seller approvals
$stmt_pending = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE role = 'seller' AND status = 'pending' ORDER BY id DESC");
$stmt_pending->execute();
$pending_sellers = $stmt_pending->fetchAll();

// Fetch top selling products
$top_products = $conn->query("SELECT p.title, COUNT(oi.id) as sales_count, SUM(oi.quantity * oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY sales_count DESC LIMIT 5")->fetchAll();

// System Activities List (Orders placed, support issues)
$activities = [];
$orders_act = $conn->query("SELECT id, total_amount, created_at FROM orders ORDER BY id DESC LIMIT 3")->fetchAll();
foreach ($orders_act as $oa) {
    $activities[] = [
        'text' => "New order #{$oa['id']} placed - " . getCurrencySymbol() . number_format($oa['total_amount'], 2),
        'time' => date('h:i A', strtotime($oa['created_at'])),
        'color' => 'text-success'
    ];
}
$users_act = $conn->query("SELECT name, created_at FROM users WHERE role = 'user' ORDER BY id DESC LIMIT 3")->fetchAll();
foreach ($users_act as $ua) {
    $activities[] = [
        'text' => "New customer '{$ua['name']}' registered",
        'time' => date('h:i A', strtotime($ua['created_at'])),
        'color' => 'text-primary'
    ];
}
$support_act = $conn->query("SELECT subject, created_at FROM support_tickets ORDER BY id DESC LIMIT 2")->fetchAll();
foreach ($support_act as $sa) {
    $activities[] = [
        'text' => "Support ticket submitted: '{$sa['subject']}'",
        'time' => date('h:i A', strtotime($sa['created_at'])),
        'color' => 'text-warning'
    ];
}
?>

<!-- Info Alerts -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Dashboard Summary Stats Row -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Customers</h6>
            <h3 class="mb-0 fw-extrabold text-dark"><?php echo number_format($total_users); ?></h3>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                <i class="fa-solid fa-store"></i>
            </div>
            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Sellers</h6>
            <h3 class="mb-0 fw-extrabold text-dark"><?php echo number_format($total_sellers); ?></h3>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Orders</h6>
            <h3 class="mb-0 fw-extrabold text-dark"><?php echo number_format($total_orders); ?></h3>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon-box bg-info bg-opacity-10 text-info">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Gross Revenue</h6>
            <h3 class="mb-0 fw-extrabold text-dark"><?php echo getCurrencySymbol() . number_format($total_revenue, 2); ?></h3>
        </div>
    </div>
</div>

<div class="row mb-5">
    <!-- Chart Box -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-premium p-4 h-100" style="border-radius: 20px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i> Sales Tracking</h5>
                <span class="badge bg-light text-dark py-2 px-3 fw-bold">Live Tracking</span>
            </div>
            <div style="position: relative; height: 320px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Pending Seller Approvals -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-premium h-100" style="border-radius: 20px;">
            <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-shield-halved text-warning me-2"></i> Pending Sellers</h5>
                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill"><?php echo count($pending_sellers); ?> Waiting</span>
            </div>
            <div class="card-body py-0 overflow-auto" style="max-height: 320px;">
                <?php if (count($pending_sellers) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($pending_sellers as $seller): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0 border-bottom">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($seller['name']); ?>&background=random" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($seller['name']); ?></h6>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($seller['email']); ?></small>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <a href="index.php?approve_seller=<?php echo $seller['id']; ?>" class="btn btn-success btn-floating btn-sm me-1 shadow-0" title="Approve"><i class="fas fa-check"></i></a>
                                    <a href="index.php?reject_seller=<?php echo $seller['id']; ?>" class="btn btn-danger btn-floating btn-sm shadow-0" title="Reject" onclick="return confirm('Are you sure you want to decline this seller?');"><i class="fas fa-times"></i></a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-store-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No pending seller registrations found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products Table -->
    <div class="col-lg-6 mb-4">
        <div class="premium-table-card h-100">
            <div class="premium-table-header">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-fire text-danger me-2"></i> Top-Selling Products</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($top_products) > 0): ?>
                            <?php foreach($top_products as $tp): ?>
                                <tr>
                                    <td><span class="fw-bold"><?php echo htmlspecialchars($tp['title']); ?></span></td>
                                    <td class="text-center"><span class="badge rounded-pill bg-light text-dark fw-bold"><?php echo $tp['sales_count']; ?> sold</span></td>
                                    <td class="text-end fw-bold text-success"><?php echo getCurrencySymbol() . number_format($tp['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Beautiful Dummy Data for Premium Look -->
                            <tr>
                                <td><span class="fw-bold">Handcrafted Clay Coffee Mug</span></td>
                                <td class="text-center"><span class="badge rounded-pill bg-light text-dark fw-bold">42 sold</span></td>
                                <td class="text-end fw-bold text-success">₹18,900.00</td>
                            </tr>
                            <tr>
                                <td><span class="fw-bold">Abstract Botanical Canvas Painting</span></td>
                                <td class="text-center"><span class="badge rounded-pill bg-light text-dark fw-bold">29 sold</span></td>
                                <td class="text-end fw-bold text-success">₹34,800.00</td>
                            </tr>
                            <tr>
                                <td><span class="fw-bold">Natural Soy Wax Scented Candle Set</span></td>
                                <td class="text-center"><span class="badge rounded-pill bg-light text-dark fw-bold">21 sold</span></td>
                                <td class="text-end fw-bold text-success">₹8,400.00</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- System Activities & Logs -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-premium h-100" style="border-radius: 20px;">
            <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-info me-2"></i> System Logs & Activity</h5>
                <span class="badge bg-light text-dark py-2 px-3 fw-bold">Live Stream</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($activities) > 0): ?>
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td class="px-4 py-3 border-0 border-bottom">
                                        <i class="fas fa-circle <?php echo $act['color']; ?> small me-2" style="font-size: 8px;"></i>
                                        <span class="fw-semibold text-dark"><?php echo $act['text']; ?></span>
                                    </td>
                                    <td class="text-muted text-end px-4 py-3 border-0 border-bottom font-monospace" style="font-size: 0.8rem;">
                                        <?php echo $act['time']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-bell-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent activities log recorded.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Load ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Gross Sales (<?php echo getCurrencySymbol(); ?>)',
                    data: <?php echo json_encode($values); ?>,
                    borderColor: '#fca311',
                    borderWidth: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#14213d',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    backgroundColor: 'rgba(252, 163, 17, 0.05)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Plus Jakarta Sans',
                                weight: '600'
                            }
                        }
                    },
                    y: {
                        grid: {
                            borderDash: [5, 5],
                            color: '#e2e8f0'
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Plus Jakarta Sans',
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php require_once 'includes/admin_footer.php'; ?>
