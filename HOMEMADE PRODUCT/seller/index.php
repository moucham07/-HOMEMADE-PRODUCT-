<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'seller') {
    redirect('../login.php');
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">Dashboard Overview</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary-custom">
                        <i class="fas fa-plus me-2"></i> Add New Product
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 5px solid var(--primary-color) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase fw-bold mb-2">Total Sales</h6>
                                    <h2 class="mb-0 fw-bold">₹1,245.00</h2>
                                </div>
                                <div class="bg-light rounded-circle p-3">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 5px solid #28a745 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase fw-bold mb-2">Active Orders</h6>
                                    <h2 class="mb-0 fw-bold">12</h2>
                                </div>
                                <div class="bg-light rounded-circle p-3">
                                    <i class="fas fa-shopping-cart fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-3" style="border-left: 5px solid var(--secondary-color) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase fw-bold mb-2">Total Products</h6>
                                    <h2 class="mb-0 fw-bold">34</h2>
                                </div>
                                <div class="bg-light rounded-circle p-3">
                                    <i class="fas fa-boxes fa-2x" style="color: var(--secondary-color);"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Orders -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4">Recent Orders</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Product</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-4521</td>
                                            <td>Abstract Canvas</td>
                                            <td>John Doe</td>
                                            <td>Oct 15, 2025</td>
                                            <td><span class="badge bg-warning rounded-pill">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-4520</td>
                                            <td>Ceramic Vase</td>
                                            <td>Sarah Smith</td>
                                            <td>Oct 14, 2025</td>
                                            <td><span class="badge bg-success rounded-pill">Shipped</span></td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-4519</td>
                                            <td>Silver Necklace</td>
                                            <td>Mike Johnson</td>
                                            <td>Oct 13, 2025</td>
                                            <td><span class="badge bg-primary rounded-pill">Delivered</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4">Top Selling Products</h5>
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=50&h=50&q=80" class="rounded me-3" alt="Product">
                                <div>
                                    <h6 class="mb-0 fw-bold">Abstract Sunset</h6>
                                    <small class="text-muted">15 Sales</small>
                                </div>
                                <span class="ms-auto fw-bold text-success">₹1,800</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=50&h=50&q=80" class="rounded me-3" alt="Product">
                                <div>
                                    <h6 class="mb-0 fw-bold">Ceramic Vase</h6>
                                    <small class="text-muted">8 Sales</small>
                                </div>
                                <span class="ms-auto fw-bold text-success">₹360</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
