<?php
require_once 'includes/admin_header.php';

// Handle Add FAQ
if (isset($_POST['add_faq'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    $stmt = $conn->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
    $stmt->execute([$question, $answer]);
    redirect('faq.php?msg=FAQ item added successfully');
}

// Handle Edit FAQ
if (isset($_POST['edit_faq'])) {
    $id = (int)$_POST['id'];
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ? WHERE id = ?");
    $stmt->execute([$question, $answer, $id]);
    redirect('faq.php?msg=FAQ item updated successfully');
}

// Handle Delete FAQ
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('faq.php?msg=FAQ item deleted successfully');
}

// Fetch all FAQs
$faqs_list = $conn->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll();

// Edit Loader
$edit_faq = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_e = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt_e->execute([$edit_id]);
    $edit_faq = $stmt_e->fetch();
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
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-circle-question text-primary me-2"></i> FAQ Management</h4>
        <small class="text-muted">Maintain, edit, and extend the list of Frequently Asked Questions shown to customers.</small>
    </div>
</div>

<div class="row">
    <!-- Form Card Add / Edit -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-premium p-4" style="border-radius: 20px;">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                <?php echo ($edit_faq) ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit FAQ' : '<i class="fa-solid fa-circle-plus text-success me-2"></i> Create FAQ'; ?>
            </h5>

            <form action="faq.php" method="POST">
                <?php if ($edit_faq): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_faq['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="question">Inquiry Question</label>
                    <textarea id="question" name="question" class="form-control rounded-4 border px-3 py-2 text-dark" rows="3" required placeholder="Type customer question..."><?php echo ($edit_faq) ? htmlspecialchars($edit_faq['question']) : ''; ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark" for="answer">Answer Details</label>
                    <textarea id="answer" name="answer" class="form-control rounded-4 border px-3 py-2 text-dark" rows="5" required placeholder="Type complete helpful response details..."><?php echo ($edit_faq) ? htmlspecialchars($edit_faq['answer']) : ''; ?></textarea>
                </div>

                <button type="submit" name="<?php echo ($edit_faq) ? 'edit_faq' : 'add_faq'; ?>" class="btn <?php echo ($edit_faq) ? 'btn-warning' : 'btn-primary'; ?> btn-premium w-100 rounded-pill py-2">
                    <?php echo ($edit_faq) ? '<i class="fa-solid fa-save me-2"></i> Save Changes' : '<i class="fa-solid fa-circle-plus me-2"></i> Add FAQ Item'; ?>
                </button>

                <?php if ($edit_faq): ?>
                    <a href="faq.php" class="btn btn-outline-secondary btn-premium w-100 rounded-pill py-2 mt-2"><i class="fa-solid fa-rotate-left me-2"></i> Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- FAQ Items List Table Grid -->
    <div class="col-lg-8 mb-4">
        <div class="premium-table-card">
            <div class="premium-table-header">
                <h6 class="fw-bold mb-0 text-dark">Active FAQ Database Listings (<?php echo count($faqs_list); ?> total)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Question</th>
                            <th style="width: 55%;">Answer Description</th>
                            <th class="text-end" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($faqs_list) > 0): ?>
                            <?php foreach ($faqs_list as $faq): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($faq['question']); ?></span></td>
                                    <td><span class="text-muted small" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($faq['answer']); ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <a href="faq.php?edit_id=<?php echo $faq['id']; ?>" class="btn btn-outline-warning btn-floating btn-sm shadow-0" title="Edit FAQ"><i class="fa-solid fa-pencil"></i></a>
                                            <a href="faq.php?delete=<?php echo $faq['id']; ?>" class="btn btn-outline-danger btn-floating btn-sm shadow-0" title="Delete FAQ" onclick="return confirm('Confirm deleting this FAQ item permanently?');"><i class="fa-solid fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-circle-question fa-3x mb-3"></i>
                                    <p class="mb-0">No FAQ articles found in database.</p>
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
