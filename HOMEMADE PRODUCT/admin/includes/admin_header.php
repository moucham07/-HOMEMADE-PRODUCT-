<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('/HOMEMADE PRODUCT/login.php');
}

$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'editor';
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';

// Helper to determine active menu item
function isActive($pageName) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $pageName) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars(getSetting('site_name', 'ArtisanHaven')); ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --primary-color: #fca311;
            --secondary-color: #14213d;
            --sidebar-width: 260px;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
            --shadow-premium: 0 8px 30px rgba(0,0,0,0.06);
            --accent-gradient: linear-gradient(135deg, #14213d 0%, #0a1128 100%);
            --font-outfit: 'Outfit', sans-serif;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* Sidebar Glassmorphism */
        .admin-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: var(--accent-gradient);
            color: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .brand-section {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            font-family: var(--font-outfit);
        }

        .brand-logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .brand-logo span {
            color: var(--primary-color);
        }

        .admin-profile {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
        }

        .profile-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            margin-right: 12px;
        }

        .profile-info h6 {
            margin: 0;
            font-weight: 700;
            font-size: 0.95rem;
            color: #ffffff;
        }

        .profile-info span {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Sidebar Navigation */
        .admin-nav {
            padding: 20px 12px;
        }

        .nav-header {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 10px 16px;
            margin-top: 10px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #94a3b8;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 4px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .admin-nav-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            transition: all 0.2s ease;
        }

        .admin-nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-link.active {
            color: #ffffff;
            background-color: var(--primary-color);
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(252, 163, 17, 0.25);
        }

        .admin-nav-link.active i {
            color: #ffffff;
        }

        /* Main Panel Wrapper */
        .admin-main {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Top Navbar Customization */
        .admin-top-nav {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 35px;
            box-shadow: var(--shadow-premium);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Premium Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow-premium);
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.09);
        }

        .stat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .premium-table-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: var(--shadow-premium);
            border: none;
            overflow: hidden;
        }

        .premium-table-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 24px;
        }

        .table-premium th {
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            border-bottom: 2px solid #f1f5f9;
        }

        .table-premium td {
            padding: 16px 24px;
            vertical-align: middle;
            color: #334155;
            font-weight: 500;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-premium {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .btn-premium {
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            text-transform: none;
            font-size: 0.85rem;
            box-shadow: none;
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Access Denied Shield */
        .access-denied-container {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Sidebar Section -->
<div class="admin-sidebar">
    <div class="brand-section">
        <a href="/HOMEMADE PRODUCT/admin/index.php" class="brand-logo">
            Artisan<span>Haven</span>
        </a>
    </div>
    
    <div class="admin-profile">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>&background=fca311&color=14213d&bold=true" class="profile-img" alt="Admin avatar">
        <div class="profile-info">
            <h6><?php echo htmlspecialchars($admin_name); ?></h6>
            <span><?php echo ($admin_role === 'super_admin') ? 'Super Admin' : 'Editor'; ?></span>
        </div>
    </div>
    
    <div class="admin-nav">
        <div class="nav-header">Overview</div>
        <a href="/HOMEMADE PRODUCT/admin/index.php" class="admin-nav-link <?php echo isActive('index.php'); ?>">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
        
        <div class="nav-header">Management</div>
        <a href="/HOMEMADE PRODUCT/admin/products.php" class="admin-nav-link <?php echo isActive('products.php'); ?>">
            <i class="fa-solid fa-box-archive"></i> Products
        </a>
        <a href="/HOMEMADE PRODUCT/admin/categories.php" class="admin-nav-link <?php echo isActive('categories.php'); ?>">
            <i class="fa-solid fa-tags"></i> Categories
        </a>
        <a href="/HOMEMADE PRODUCT/admin/orders.php" class="admin-nav-link <?php echo isActive('orders.php'); ?>">
            <i class="fa-solid fa-receipt"></i> Orders & Payments
        </a>
        <a href="/HOMEMADE PRODUCT/admin/users.php" class="admin-nav-link <?php echo isActive('users.php'); ?>">
            <i class="fa-solid fa-users-gear"></i> Users & Sellers
        </a>
        <a href="/HOMEMADE PRODUCT/admin/coupons.php" class="admin-nav-link <?php echo isActive('coupons.php'); ?>">
            <i class="fa-solid fa-percent"></i> Coupons
        </a>

        <div class="nav-header">Support & FAQ</div>
        <a href="/HOMEMADE PRODUCT/admin/support.php" class="admin-nav-link <?php echo isActive('support.php'); ?>">
            <i class="fa-solid fa-headset"></i> Support Tickets
        </a>
        <a href="/HOMEMADE PRODUCT/admin/faq.php" class="admin-nav-link <?php echo isActive('faq.php'); ?>">
            <i class="fa-solid fa-circle-question"></i> FAQs
        </a>
        
        <?php if ($admin_role === 'super_admin'): ?>
            <div class="nav-header">System</div>
            <a href="/HOMEMADE PRODUCT/admin/settings.php" class="admin-nav-link <?php echo isActive('settings.php'); ?>">
                <i class="fa-solid fa-sliders"></i> Site Settings
            </a>
        <?php endif; ?>

        <div class="nav-header">Actions</div>
        <a href="/HOMEMADE PRODUCT/logout.php" class="admin-nav-link text-danger">
            <i class="fa-solid fa-right-from-bracket text-danger"></i> Logout
        </a>
    </div>
</div>

<!-- Main Workspace Wrapper -->
<div class="admin-main">
    
    <!-- Top Glassmorphism Navigation Bar -->
    <div class="admin-top-nav">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Control Panel</h5>
            <small class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>.</small>
        </div>
        <div class="d-flex align-items-center">
            <a href="/HOMEMADE PRODUCT/index.php" class="btn btn-outline-secondary btn-premium me-3" target="_blank">
                <i class="fa-solid fa-globe me-2"></i> Visit Live Store
            </a>
            <div class="dropdown">
                <a class="dropdown-toggle d-flex align-items-center hidden-arrow text-dark" href="#" id="navbarDropdownMenuAvatar" role="button" data-mdb-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>&background=fca311&color=14213d&bold=true" class="rounded-circle" height="35" alt="Avatar" loading="lazy" />
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0" aria-labelledby="navbarDropdownMenuAvatar" style="border-radius: 12px;">
                    <li>
                        <a class="dropdown-item py-2" href="/HOMEMADE%20PRODUCT/admin/index.php"><i class="fa-solid fa-chart-line me-2 text-muted"></i> Dashboard</a>
                    </li>
                    <?php if ($admin_role === 'super_admin'): ?>
                        <li>
                            <a class="dropdown-item py-2" href="/HOMEMADE%20PRODUCT/admin/settings.php"><i class="fa-solid fa-cogs me-2 text-muted"></i> Settings</a>
                        </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 text-danger" href="/HOMEMADE%20PRODUCT/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
