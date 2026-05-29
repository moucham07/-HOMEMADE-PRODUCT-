<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'user') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch some basic stats (mocked if no data)
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 text-center">
                        <i class="fas fa-shopping-bag fa-3x text-primary mb-3"></i>
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Total Orders</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $total_orders; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 text-center">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Wishlist Items</h6>
                        <h2 class="mb-0 fw-bold">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 text-center">
                        <i class="fas fa-star fa-3x text-warning mb-3"></i>
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Reviews Left</h6>
                        <h2 class="mb-0 fw-bold">0</h2>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Recent Activity</h5>
                    <?php if ($total_orders > 0): ?>
                        <p class="text-muted">Head over to <a href="orders.php">My Orders</a> to see your purchase history.</p>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">You haven't made any purchases yet.</p>
                            <a href="../products.php" class="btn btn-primary-custom">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
