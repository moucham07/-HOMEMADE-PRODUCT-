<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Haven - Homemade Product Marketplace</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/HOMEMADE PRODUCT/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
  <div class="container">
    <!-- Brand -->
    <a class="navbar-brand" href="/HOMEMADE PRODUCT/index.php">
      Artisan<span>Haven</span>
    </a>

    <!-- Toggle button -->
    <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Collapsible wrapper -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Left links -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="/HOMEMADE PRODUCT/index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/HOMEMADE PRODUCT/products.php">Shop</a>
        </li>
      </ul>

      <!-- Right elements -->
      <div class="d-flex align-items-center">
        <!-- Cart Icon -->
        <a class="text-reset me-4" href="/HOMEMADE PRODUCT/cart.php">
          <i class="fas fa-shopping-cart fa-lg text-secondary"></i>
          <?php if(isLoggedIn()): 
              $stmt_cart = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
              $stmt_cart->execute([$_SESSION['user_id']]);
              $cart_count = $stmt_cart->fetchColumn();
              $cart_count = $cart_count ? $cart_count : 0;
          ?>
              <span class="badge rounded-pill badge-notification bg-danger"><?php echo $cart_count; ?></span>
          <?php endif; ?>
        </a>

        <?php if(isLoggedIn()): ?>
          <?php
          $profileLink = '/HOMEMADE PRODUCT/user/index.php';
          if(getUserRole() == 'admin') {
              $profileLink = '/HOMEMADE PRODUCT/admin/index.php';
          } elseif(getUserRole() == 'seller') {
              $profileLink = '/HOMEMADE PRODUCT/seller/index.php';
          }
          ?>
          <a class="d-flex align-items-center text-secondary me-3" href="<?php echo $profileLink; ?>" title="My Profile">
            <i class="fas fa-user-circle fa-2x"></i>
          </a>
          <a href="/HOMEMADE PRODUCT/logout.php" class="btn btn-outline-danger btn-sm btn-rounded">Logout</a>
        <?php else: ?>
            <a href="/HOMEMADE PRODUCT/login.php" class="btn btn-outline-secondary btn-sm me-2 btn-rounded">Login</a>
            <a href="/HOMEMADE PRODUCT/register.php" class="btn btn-primary-custom btn-sm">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<!-- Navbar -->
