<?php
require_once '../config/config.php';

if (!isLoggedIn() || getUserRole() !== 'user') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = "Name is required.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
            if ($stmt->execute([$name, $phone, $address, $user_id])) {
                $success = "Profile updated successfully!";
                $_SESSION['name'] = $name;
            } else {
                $error = "Failed to update profile.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5" style="background-color: var(--background-light); min-height: 100vh;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">Profile Settings</h1>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="settings.php" method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="email">Email Address</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                                    <div class="form-text">Your email address cannot be changed.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($user['name']); ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="address">Shipping Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-2"></i> Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
