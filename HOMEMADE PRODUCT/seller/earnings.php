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
                <h1 class="h2 fw-bold text-dark">Earnings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary-custom">
                        <i class="fas fa-university me-2"></i> Withdraw Funds
                    </button>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Available for Withdrawal</h6>
                        <h2 class="mb-0 fw-bold text-success">₹8,450.00</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Pending Clearance</h6>
                        <h2 class="mb-0 fw-bold text-warning">₹1,800.00</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted text-uppercase fw-bold mb-2">Total Earnings (All Time)</h6>
                        <h2 class="mb-0 fw-bold">₹24,500.00</h2>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Earning History</h5>
                    <p class="text-muted">This page is currently showing dummy data. Database integration coming soon!</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Oct 15, 2025</td>
                                    <td>Sale: Abstract Sunset Painting (Order #ORD-4521)</td>
                                    <td class="text-success fw-bold">+₹1,800.00</td>
                                    <td><span class="badge bg-warning rounded-pill">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>Oct 12, 2025</td>
                                    <td>Bank Withdrawal</td>
                                    <td class="text-danger fw-bold">-₹5,000.00</td>
                                    <td><span class="badge bg-success rounded-pill">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>Oct 10, 2025</td>
                                    <td>Sale: Ceramic Vase (Order #ORD-4501)</td>
                                    <td class="text-success fw-bold">+₹360.00</td>
                                    <td><span class="badge bg-success rounded-pill">Cleared</span></td>
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
