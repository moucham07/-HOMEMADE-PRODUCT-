<?php
require_once 'includes/admin_header.php';

// Handle Response Submission
if (isset($_POST['send_reply'])) {
    $tid = (int)$_POST['id'];
    $reply = trim($_POST['reply']);

    $stmt = $conn->prepare("UPDATE support_tickets SET reply = ?, status = 'resolved' WHERE id = ?");
    $stmt->execute([$reply, $tid]);
    redirect('support.php?msg=Reply submitted and ticket marked as Resolved');
}

// Fetch Search and Filter
$filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
$query_str = "SELECT * FROM support_tickets WHERE 1=1";
$params = [];

if ($filter_status !== '') {
    $query_str .= " AND status = ?";
    $params[] = $filter_status;
}

$query_str .= " ORDER BY id DESC";
$stmt_tickets = $conn->prepare($query_str);
$stmt_tickets->execute($params);
$tickets_list = $stmt_tickets->fetchAll();

// Loader for reply modal active load
$reply_ticket = null;
if (isset($_GET['reply_id'])) {
    $reply_id = (int)$_GET['reply_id'];
    $stmt_rt = $conn->prepare("SELECT * FROM support_tickets WHERE id = ?");
    $stmt_rt->execute([$reply_id]);
    $reply_ticket = $stmt_rt->fetch();
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
        <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-headset text-primary me-2"></i> Customer Support Tickets</h4>
        <small class="text-muted">Review incoming customer inquiries, send custom answers, and audit support statuses.</small>
    </div>
</div>

<!-- Filters Panel -->
<div class="card border-0 shadow-premium p-4 mb-4" style="border-radius: 20px;">
    <form action="support.php" method="GET" class="row g-3 align-items-center">
        <div class="col-md-8">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-filter me-2 text-muted"></i> Filter Support Inquiries</h6>
        </div>
        <div class="col-md-4 d-flex">
            <select name="filter_status" class="form-select border rounded-pill px-3 py-2 text-dark me-2" onchange="this.form.submit();">
                <option value="">All Tickets</option>
                <option value="open" <?php echo ($filter_status === 'open') ? 'selected' : ''; ?>>Open Tickets Only</option>
                <option value="resolved" <?php echo ($filter_status === 'resolved') ? 'selected' : ''; ?>>Resolved Tickets Only</option>
            </select>
            <a href="support.php" class="btn btn-outline-secondary btn-premium rounded-pill px-3"><i class="fa-solid fa-rotate-left"></i></a>
        </div>
    </form>
</div>

<!-- Tickets List Grid Table -->
<div class="premium-table-card">
    <div class="premium-table-header">
        <h6 class="fw-bold mb-0 text-dark">Customer Support Inquiries (<?php echo count($tickets_list); ?> total)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-premium mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>User Details</th>
                    <th>Issue Subject</th>
                    <th>Original Message</th>
                    <th>Ticket Status</th>
                    <th>Reply Note</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tickets_list) > 0): ?>
                    <?php foreach ($tickets_list as $tk): ?>
                        <tr>
                            <td class="font-monospace fw-bold">#<?php echo $tk['id']; ?></td>
                            <td>
                                <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($tk['name']); ?></span>
                                <small class="text-muted font-monospace"><?php echo htmlspecialchars($tk['email']); ?></small>
                            </td>
                            <td><span class="badge bg-light text-primary rounded-pill fw-bold text-uppercase"><?php echo htmlspecialchars($tk['subject']); ?></span></td>
                            <td><span class="text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 250px;" title="<?php echo htmlspecialchars($tk['message']); ?>"><?php echo htmlspecialchars($tk['message']); ?></span></td>
                            <td>
                                <?php if ($tk['status'] === 'resolved'): ?>
                                    <span class="badge badge-premium bg-success text-white"><i class="fa-solid fa-check me-1"></i> Resolved</span>
                                <?php else: ?>
                                    <span class="badge badge-premium bg-warning text-white"><i class="fa-solid fa-envelope-open me-1"></i> Open</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tk['reply'])): ?>
                                    <span class="text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 200px;"><?php echo htmlspecialchars($tk['reply']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted italic small">No reply drafted yet</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="support.php?reply_id=<?php echo $tk['id']; ?>" class="btn btn-primary btn-sm rounded-pill btn-premium"><i class="fa-solid fa-reply me-1"></i> <?php echo (!empty($tk['reply'])) ? 'Update Reply' : 'Draft Reply'; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-headset fa-3x mb-3"></i>
                            <p class="mb-0">No customer support inquiries found matching criteria.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- REPLY MODAL WINDOW OVERLAY -->
<?php if ($reply_ticket): ?>
    <div class="modal fade show" id="replyModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--shadow-premium);">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1"><i class="fa-solid fa-reply text-primary me-2"></i> Respond to Inquiry</h5>
                        <p class="text-muted small mb-0">Ticket #<?php echo $reply_ticket['id']; ?> from <?php echo htmlspecialchars($reply_ticket['name']); ?></p>
                    </div>
                    <a href="support.php" class="btn-close text-dark shadow-none" aria-label="Close"></a>
                </div>
                
                <form action="support.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $reply_ticket['id']; ?>">
                    <div class="modal-body px-4">
                        <div class="p-3 border rounded-4 bg-light mb-4">
                            <small class="text-muted fw-bold d-block mb-1">Customer Original Question:</small>
                            <span class="text-dark small d-block"><?php echo nl2br(htmlspecialchars($reply_ticket['message'])); ?></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" for="reply">Draft Reply Message</label>
                            <textarea id="reply" name="reply" class="form-control rounded-4 border px-3 py-2 text-dark" rows="5" required placeholder="Type your response to the customer here..."><?php echo htmlspecialchars($reply_ticket['reply']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 px-4 pb-4 pt-2">
                        <a href="support.php" class="btn btn-outline-secondary btn-premium rounded-pill px-4 me-2">Cancel</a>
                        <button type="submit" name="send_reply" class="btn btn-primary btn-premium rounded-pill px-4"><i class="fa-solid fa-paper-plane me-2"></i> Send Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
