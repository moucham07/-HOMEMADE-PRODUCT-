<?php
require_once __DIR__ . '/config.php';

try {
    // 1. Add admin_role to users table if not exists
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS admin_role ENUM('super_admin', 'editor') DEFAULT NULL");
    
    // Set current admins as super_admin
    $conn->exec("UPDATE users SET admin_role = 'super_admin' WHERE role = 'admin' AND admin_role IS NULL");
    
    // Create an editor admin for testing if not exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['editor@homemade.com']);
    if ($stmt->rowCount() == 0) {
        $conn->exec("INSERT INTO users (name, email, password, role, admin_role) VALUES 
        ('Jane Editor', 'editor@homemade.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'editor')");
    }

    // 2. Add columns to products
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active'");
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS is_digital BOOLEAN DEFAULT FALSE");
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS digital_file_url VARCHAR(255) DEFAULT NULL");

    // 3. Add columns to orders
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100) DEFAULT NULL");
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS refund_status ENUM('none', 'pending', 'refunded') DEFAULT 'none'");
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS refund_amount DECIMAL(10, 2) DEFAULT 0.00");

    // 4. Create settings table
    $conn->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` TEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Insert default settings
    $default_settings = [
        'site_name' => 'ArtisanHaven',
        'site_logo' => '',
        'site_footer' => 'Your premium marketplace for authentic, handcrafted products from independent artisans and creators around the world.',
        'currency' => 'INR',
        'tax_gst' => '18.00',
        'tax_vat' => '5.00',
        'payment_gateway' => 'paypal',
        'paypal_client_id' => 'mock_client_id_1234567890',
        'paypal_secret' => 'mock_secret_abc123xyz',
        'stripe_key' => 'pk_test_mock_key',
        'stripe_secret' => 'sk_test_mock_secret',
        'razorpay_key' => 'rzp_test_mock_key',
        'razorpay_secret' => 'rzp_test_mock_secret',
        'email_order_confirmation' => 'Dear {customer_name},\n\nYour order {order_id} has been placed successfully!\n\nTotal: {total_amount}\n\nThank you for shopping with ArtisanHaven!',
        'email_failed_payment' => 'Dear {customer_name},\n\nWe were unable to process your payment for order {order_id}. Please try again.\n\nThank you!'
    ];

    $stmt_insert = $conn->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");
    foreach ($default_settings as $k => $v) {
        $stmt_insert->execute([$k, $v]);
    }

    // 5. Create coupons table
    $conn->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('flat', 'percentage') NOT NULL,
        value DECIMAL(10, 2) NOT NULL,
        expiry_date DATE NOT NULL,
        usage_limit INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert mock coupons
    $conn->exec("INSERT IGNORE INTO coupons (code, type, value, expiry_date, usage_limit, status) VALUES 
    ('WELCOME10', 'percentage', 10.00, '2026-12-31', 100, 'active'),
    ('FLAT50', 'flat', 50.00, '2026-12-31', 50, 'active')");

    // 6. Create support_tickets table
    $conn->exec("CREATE TABLE IF NOT EXISTS support_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        reply TEXT DEFAULT NULL,
        status ENUM('open', 'resolved') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 7. Create faqs table
    $conn->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert mock faqs if empty
    $count_faqs = $conn->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
    if ($count_faqs == 0) {
        $conn->exec("INSERT INTO faqs (question, answer) VALUES 
        ('How do I know the products are truly handmade?', 'Every artisan on our platform goes through a strict verification process. We ensure that all products sold on ArtisanHaven are handcrafted, customized, or made in small batches by independent creators.'),
        ('What is your return policy?', 'Because most items are custom or handmade to order, return policies vary by seller. Generally, you have 14 days to request a return for non-customized items. Please check the specific seller\'s shop policies for exact details.'),
        ('How can I track my order?', 'Once your order ships, you will receive a tracking number via email. You can also track your order directly on our website by visiting the Track Order page and entering your Order ID.'),
        ('How do I become a seller?', 'We are always looking for talented artisans! Simply click the \"Become a Seller\" link in the footer, register for an account, and set up your storefront. It\'s free to join!')");
    }

    echo "Database migrated successfully!";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
