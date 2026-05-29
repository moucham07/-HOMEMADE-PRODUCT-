<?php
require_once 'includes/admin_header.php';

// Handle Add Category Action
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_dir = __DIR__ . '/../../assets/images/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $image = $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
    $stmt->execute([$name, $description, $image]);
    redirect('categories.php?msg=Category added successfully');
}

// Handle Edit Category Action
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_dir = __DIR__ . '/../../assets/images/';
        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $description, $filename, $id]);
        }
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
    }
    redirect('categories.php?msg=Category updated successfully');
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('categories.php?msg=Category deleted successfully');
}

// Fetch all categories
$categories_list = $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

// Edit pre-loader
$edit_cat = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_e = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt_e->execute([$edit_id]);
    $edit_cat = $stmt_e->fetch();
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
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-tags text-primary me-2"></i> Category Management</h4>
        <small class="text-muted">Maintain structure, edit covers, and modify product classifications.</small>
    </div>
</div>

<div class="row">
    <!-- Category Manager Card Form (Add / Edit) -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
            <h5 class="fw-bold text-dark mb-4">
                <?php echo ($edit_cat) ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Category' : '<i class="fa-solid fa-folder-plus text-success me-2"></i> Add New Category'; ?>
            </h5>
            
            <form action="categories.php" method="POST" enctype="multipart/form-data">
                <?php if ($edit_cat): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_cat['id']; ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="name">Category Name</label>
                    <input type="text" id="name" name="name" class="form-control rounded-pill px-3 py-2 text-dark border" required value="<?php echo ($edit_cat) ? htmlspecialchars($edit_cat['name']) : ''; ?>" placeholder="e.g. Handmade Pottery">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="description">Short Description</label>
                    <textarea id="description" name="description" class="form-control rounded-4 px-3 py-2 text-dark border" rows="3" required placeholder="Describe category classification details..."><?php echo ($edit_cat) ? htmlspecialchars($edit_cat['description']) : ''; ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="image">Category Cover Thumbnail</label>
                    <input type="file" id="image" name="image" class="form-control rounded-pill border px-3 py-2 text-dark" <?php echo ($edit_cat) ? '' : 'required'; ?>>
                    <?php if ($edit_cat && $edit_cat['image']): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Current Cover Image:</small>
                            <img src="/HOMEMADE PRODUCT/assets/images/<?php echo htmlspecialchars($edit_cat['image']); ?>" class="img-thumbnail rounded-4" style="height: 100px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="<?php echo ($edit_cat) ? 'edit_category' : 'add_category'; ?>" class="btn <?php echo ($edit_cat) ? 'btn-warning' : 'btn-primary'; ?> btn-premium w-100 rounded-pill py-2">
                    <?php echo ($edit_cat) ? '<i class="fa-solid fa-save me-2"></i> Save Changes' : '<i class="fa-solid fa-plus me-2"></i> Create Category'; ?>
                </button>

                <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn btn-outline-secondary btn-premium w-100 rounded-pill py-2 mt-2"><i class="fa-solid fa-rotate-left me-2"></i> Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Category List Table Grid -->
    <div class="col-lg-8 mb-4">
        <div class="premium-table-card">
            <div class="premium-table-header">
                <h6 class="fw-bold mb-0 text-dark">Shop Product Categories (<?php echo count($categories_list); ?> total)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Cover Image</th>
                            <th>Category Title</th>
                            <th>Description Detail</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories_list) > 0): ?>
                            <?php foreach($categories_list as $cat): ?>
                                <tr>
                                    <td>
                                        <img src="/HOMEMADE PRODUCT/assets/images/<?php echo !empty($cat['image']) ? htmlspecialchars($cat['image']) : 'default_cat.jpg'; ?>" class="rounded-4 border" style="width: 70px; height: 50px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1610701596007-11502861dcfa?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80'">
                                    </td>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($cat['name']); ?></span></td>
                                    <td><span class="text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($cat['description']); ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <a href="categories.php?edit_id=<?php echo $cat['id']; ?>" class="btn btn-outline-warning btn-floating btn-sm shadow-0" title="Edit Category"><i class="fa-solid fa-pencil"></i></a>
                                            <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-outline-danger btn-floating btn-sm shadow-0" title="Delete Category" onclick="return confirm('WARNING: Deleting this category will delete all items under it. Continue?');"><i class="fa-solid fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-tags fa-3x mb-3"></i>
                                    <p class="mb-0">No categories found in the database.</p>
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
