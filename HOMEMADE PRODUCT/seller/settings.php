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
                <h1 class="h2 fw-bold text-dark">Store Settings</h1>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Store Information</h5>
                            <p class="text-muted">This page is currently showing dummy data. Database integration coming soon!</p>
                            <form>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Store Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['name']); ?>'s Store">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Store Description</label>
                                    <textarea class="form-control" rows="4">We create beautiful, handcrafted items for your home.</textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Payment Email (PayPal/Bank)</label>
                                    <input type="email" class="form-control" value="payments@example.com">
                                </div>
                                <button type="button" class="btn btn-primary-custom">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
