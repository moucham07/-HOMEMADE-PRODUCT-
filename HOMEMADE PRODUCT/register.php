<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user'; // default to user

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email, and password are required.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $phone, $role])) {
                $success = "Registration successful! You can now login.";
                // Optionally auto-login here
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4 font-weight-bold" style="color: var(--secondary-color);">Create Account</h2>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($success); ?>
                                <br><a href="login.php" class="alert-link">Click here to login</a>
                            </div>
                        <?php else: ?>
                            <form action="register.php" method="POST">
                                <!-- Role Selection -->
                                <div class="mb-4 text-center">
                                    <div class="btn-group" role="group" aria-label="Role Selection">
                                      <input type="radio" class="btn-check" name="role" id="roleUser" value="user" checked autocomplete="off" />
                                      <label class="btn btn-outline-primary" for="roleUser">Customer</label>

                                      <input type="radio" class="btn-check" name="role" id="roleSeller" value="seller" autocomplete="off" />
                                      <label class="btn btn-outline-primary" for="roleSeller">Seller</label>
                                    </div>
                                    <div class="form-text mt-2">Are you looking to buy or sell?</div>
                                </div>

                                <!-- Name input -->
                                <div class="form-outline mb-4" data-mdb-input-init>
                                    <input type="text" id="name" name="name" class="form-control form-control-lg" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
                                    <label class="form-label" for="name">Full Name</label>
                                </div>

                                <!-- Email input -->
                                <div class="form-outline mb-4" data-mdb-input-init>
                                    <input type="email" id="email" name="email" class="form-control form-control-lg" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                                    <label class="form-label" for="email">Email address</label>
                                </div>

                                <!-- Phone input -->
                                <div class="form-outline mb-4" data-mdb-input-init>
                                    <input type="text" id="phone" name="phone" class="form-control form-control-lg" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
                                    <label class="form-label" for="phone">Phone Number (Optional)</label>
                                </div>

                                <!-- Password input -->
                                <div class="form-outline mb-4" data-mdb-input-init>
                                    <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="password">Password</label>
                                </div>

                                <!-- Submit button -->
                                <button type="submit" class="btn btn-primary-custom btn-block btn-lg w-100 mb-4" data-mdb-ripple-init>Sign Up</button>

                                <!-- Register buttons -->
                                <div class="text-center">
                                    <p>Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login</a></p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
