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
                <h1 class="h2 fw-bold text-dark">Customer Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <p class="text-muted">This page is currently showing dummy data. Database integration coming soon!</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="fw-bold">#ORD-4521</span></td>
                                    <td>Abstract Sunset Painting</td>
                                    <td>John Doe<br><small class="text-muted">john@example.com</small></td>
                                    <td>₹1,800.00</td>
                                    <td>Oct 15, 2025</td>
                                    <td><span class="badge bg-warning rounded-pill">Pending</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Process</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-bold">#ORD-4520</span></td>
                                    <td>Ceramic Vase</td>
                                    <td>Sarah Smith<br><small class="text-muted">sarah@example.com</small></td>
                                    <td>₹360.00</td>
                                    <td>Oct 14, 2025</td>
                                    <td><span class="badge bg-success rounded-pill">Shipped</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
