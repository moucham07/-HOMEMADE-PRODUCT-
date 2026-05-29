<?php
require_once 'includes/admin_header.php';

// Handle Add Product
if (isset($_POST['add_product'])) {
    $seller_id = (int)$_POST['seller_id'];
    $category_id = (int)$_POST['category_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $materials = trim($_POST['materials']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    $status = $_POST['status'];
    $digital_file_url = '';

    if ($is_digital && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['digital_file']['tmp_name'];
        $filename = 'digital_' . time() . '_' . basename($_FILES['digital_file']['name']);
        $target_dir = __DIR__ . '/../../uploads/digital_products/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
            file_put_contents($target_dir . '.htaccess', "Deny from all");
        }
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $digital_file_url = $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (seller_id, category_id, title, description, materials, price, stock, is_featured, is_digital, digital_file_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$seller_id, $category_id, $title, $description, $materials, $price, $stock, $is_featured, $is_digital, $digital_file_url, $status]);
    $product_id = $conn->lastInsertId();

    // Image Upload
    if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['primary_image']['tmp_name'];
        $filename = 'product_' . time() . '_' . basename($_FILES['primary_image']['name']);
        $target_dir = __DIR__ . '/../../assets/images/';
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, TRUE)");
            $stmt_img->execute([$product_id, $filename]);
        }
    }

    redirect('products.php?msg=Product added successfully');
}

// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['id'];
    $category_id = (int)$_POST['category_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $materials = trim($_POST['materials']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, materials = ?, price = ?, stock = ?, is_featured = ?, is_digital = ?, status = ? WHERE id = ?");
    $stmt->execute([$category_id, $title, $description, $materials, $price, $stock, $is_featured, $is_digital, $status, $id]);

    // Handle digital file update
    if ($is_digital && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['digital_file']['tmp_name'];
        $filename = 'digital_' . time() . '_' . basename($_FILES['digital_file']['name']);
        $target_dir = __DIR__ . '/../../uploads/digital_products/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
            file_put_contents($target_dir . '.htaccess', "Deny from all");
        }
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $stmt_df = $conn->prepare("UPDATE products SET digital_file_url = ? WHERE id = ?");
            $stmt_df->execute([$filename, $id]);
        }
    }

    // Handle primary image update
    if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['primary_image']['tmp_name'];
        $filename = 'product_' . time() . '_' . basename($_FILES['primary_image']['name']);
        $target_dir = __DIR__ . '/../../assets/images/';
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            // Delete old primary images
            $stmt_del = $conn->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = TRUE");
            $stmt_del->execute([$id]);

            $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, TRUE)");
            $stmt_img->execute([$id, $filename]);
        }
    }

    redirect('products.php?msg=Product updated successfully');
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('products.php?msg=Product deleted successfully');
}

// Fetch lists for fields
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$sellers = $conn->query("SELECT id, name FROM users WHERE role = 'seller' AND status = 'active' ORDER BY name ASC")->fetchAll();

// Search and listings
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query_str = "SELECT p.*, c.name as category_name, u.name as seller_name, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as primary_image FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $query_str .= " AND p.title LIKE ?";
    $params[] = "%$search%";
}

$query_str .= " ORDER BY p.id DESC";
$stmt_prod = $conn->prepare($query_str);
$stmt_prod->execute($params);
$products_list = $stmt_prod->fetchAll();

// Loader for edit mode
$edit_prod = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_ep = $conn->prepare("SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as primary_image FROM products p WHERE p.id = ?");
    $stmt_ep->execute([$edit_id]);
    $edit_prod = $stmt_ep->fetch();
}
?>

<!-- Alerts -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Dashboard Section Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-box-archive text-primary me-2"></i> Product Management</h4>
        <small class="text-muted">Manage product specifications, configure digital item uploads, and monitor stock lists.</small>
    </div>
</div>

<div class="row">
    <!-- Form Card Add/Edit -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                <?php echo ($edit_prod) ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Product' : '<i class="fa-solid fa-plus-circle text-success me-2"></i> Add New Product'; ?>
            </h5>

            <form action="products.php" method="POST" enctype="multipart/form-data">
                <?php if ($edit_prod): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_prod['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="title">Product Title</label>
                    <input type="text" id="title" name="title" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo ($edit_prod) ? htmlspecialchars($edit_prod['title']) : ''; ?>" required placeholder="e.g. Clay Vase">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-select rounded-pill border px-3 py-2 text-dark" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_prod && $edit_prod['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!$edit_prod): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="seller_id">Assigned Seller</label>
                        <select id="seller_id" name="seller_id" class="form-select rounded-pill border px-3 py-2 text-dark" required>
                            <option value="">Select Active Seller</option>
                            <?php foreach($sellers as $sel): ?>
                                <option value="<?php echo $sel['id']; ?>"><?php echo htmlspecialchars($sel['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control rounded-4 border px-3 py-2 text-dark" rows="3" required placeholder="Describe product details..."><?php echo ($edit_prod) ? htmlspecialchars($edit_prod['description']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="materials">Materials Used</label>
                    <input type="text" id="materials" name="materials" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo ($edit_prod) ? htmlspecialchars($edit_prod['materials']) : ''; ?>" placeholder="e.g. Organic Clay, Glaze">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold text-dark" for="price">Price (<?php echo getCurrencySymbol(); ?>)</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo ($edit_prod) ? htmlspecialchars($edit_prod['price']) : ''; ?>" required placeholder="0.00">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold text-dark" for="stock">Stock Inventory</label>
                        <input type="number" id="stock" name="stock" class="form-control rounded-pill border px-3 py-2 text-dark" value="<?php echo ($edit_prod) ? htmlspecialchars($edit_prod['stock']) : '0'; ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold text-dark" for="status">Display Status</label>
                        <select id="status" name="status" class="form-select rounded-pill border px-3 py-2 text-dark">
                            <option value="active" <?php echo ($edit_prod && $edit_prod['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_prod && $edit_prod['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-6 d-flex align-items-center mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo ($edit_prod && $edit_prod['is_featured']) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold text-dark" for="is_featured">Featured Product</label>
                        </div>
                    </div>
                </div>

                <!-- Digital Product Secure Gate settings -->
                <div class="p-3 border rounded-4 bg-light mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_digital" name="is_digital" value="1" <?php echo ($edit_prod && $edit_prod['is_digital']) ? 'checked' : ''; ?> onchange="document.getElementById('digital_file_section').style.display = this.checked ? 'block' : 'none';">
                        <label class="form-check-label fw-bold text-dark" for="is_digital"><i class="fa-solid fa-cloud-arrow-down text-primary me-1"></i> Digital Product</label>
                    </div>
                    <div id="digital_file_section" style="display: <?php echo ($edit_prod && $edit_prod['is_digital']) ? 'block' : 'none'; ?>;">
                        <label class="form-label small fw-bold text-muted mb-1" for="digital_file">Secure File Upload</label>
                        <input type="file" id="digital_file" name="digital_file" class="form-control rounded-pill border px-3 py-2 text-dark">
                        <?php if ($edit_prod && $edit_prod['digital_file_url']): ?>
                            <small class="text-success d-block mt-1 font-monospace"><i class="fa-solid fa-file-shield"></i> File secured on disk.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="primary_image">Product Screenshot/Image</label>
                    <input type="file" id="primary_image" name="primary_image" class="form-control rounded-pill border px-3 py-2 text-dark" <?php echo ($edit_prod) ? '' : 'required'; ?>>
                    <?php if ($edit_prod && $edit_prod['primary_image']): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Current Image:</small>
                            <img src="/HOMEMADE PRODUCT/assets/images/<?php echo htmlspecialchars($edit_prod['primary_image']); ?>" class="img-thumbnail rounded-4" style="height: 100px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="<?php echo ($edit_prod) ? 'edit_product' : 'add_product'; ?>" class="btn <?php echo ($edit_prod) ? 'btn-warning' : 'btn-success'; ?> btn-premium w-100 rounded-pill py-2">
                    <?php echo ($edit_prod) ? '<i class="fa-solid fa-save me-2"></i> Save Product' : '<i class="fa-solid fa-circle-plus me-2"></i> Add Product'; ?>
                </button>

                <?php if ($edit_prod): ?>
                    <a href="products.php" class="btn btn-outline-secondary btn-premium w-100 rounded-pill py-2 mt-2"><i class="fa-solid fa-rotate-left me-2"></i> Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Products Table Grid -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-premium p-4 mb-4" style="border-radius: 20px;">
            <form action="products.php" method="GET" class="d-flex">
                <div class="input-group border rounded-pill px-3 py-1 bg-light w-75 me-2">
                    <i class="fa-solid fa-magnifying-glass text-muted d-flex align-items-center me-2"></i>
                    <input type="text" name="search" class="form-control bg-transparent border-0 shadow-none text-dark" placeholder="Search products by title..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-premium rounded-pill w-25"><i class="fa-solid fa-search"></i> Search</button>
            </form>
        </div>

        <div class="premium-table-card">
            <div class="premium-table-header">
                <h6 class="fw-bold mb-0 text-dark">Store Inventory Items (<?php echo count($products_list); ?> total)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Seller / Owner</th>
                            <th>Category</th>
                            <th>Pricing</th>
                            <th>Inventory</th>
                            <th>Product Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products_list) > 0): ?>
                            <?php foreach($products_list as $prod): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="/HOMEMADE PRODUCT/assets/images/<?php echo !empty($prod['primary_image']) ? htmlspecialchars($prod['primary_image']) : 'default_prod.jpg'; ?>" class="rounded-4 border me-3" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80'">
                                            <div>
                                                <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($prod['title']); ?></span>
                                                <?php if ($prod['is_digital']): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary small font-weight-bold" style="font-size: 0.65rem;"><i class="fa-solid fa-file-arrow-down"></i> Secure Digital</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary small font-weight-bold" style="font-size: 0.65rem;"><i class="fa-solid fa-truck-fast"></i> Physical</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($prod['seller_name']); ?></span></td>
                                    <td><span class="badge bg-light text-muted fw-bold"><?php echo htmlspecialchars($prod['category_name']); ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo getCurrencySymbol() . number_format($prod['price'], 2); ?></td>
                                    <td>
                                        <?php if ($prod['stock'] > 0): ?>
                                            <span class="text-success fw-bold"><?php echo $prod['stock']; ?> items</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prod['status'] === 'inactive'): ?>
                                            <span class="badge badge-premium bg-light text-muted border"><i class="fa-solid fa-eye-slash me-1"></i> Inactive</span>
                                        <?php else: ?>
                                            <span class="badge badge-premium bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <a href="products.php?edit_id=<?php echo $prod['id']; ?>" class="btn btn-outline-warning btn-floating btn-sm shadow-0" title="Edit Product"><i class="fa-solid fa-pencil"></i></a>
                                            <a href="products.php?delete=<?php echo $prod['id']; ?>" class="btn btn-outline-danger btn-floating btn-sm shadow-0" title="Delete Product" onclick="return confirm('Confirm deleting this product permanently from marketplace?');"><i class="fa-solid fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fa-3x mb-3"></i>
                                    <p class="mb-0">No marketplace products found matching criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
