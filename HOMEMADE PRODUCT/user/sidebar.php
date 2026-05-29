<!-- Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-white py-4 shadow-sm">
    <div class="position-sticky">
        <div class="text-center mb-4">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>&background=007bff&color=fff" class="rounded-circle mb-2" width="80" alt="User Avatar">
            <h6 class="fw-bold"><?php echo htmlspecialchars($_SESSION['name']); ?></h6>
            <small class="text-muted">Customer Account</small>
        </div>
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="sidebar-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="sidebar-link <?php echo $currentPage == 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag me-2"></i> My Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="sidebar-link <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog me-2"></i> Profile Settings
                </a>
            </li>
        </ul>
    </div>
</div>
