<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();

// Filter by status
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($statusFilter && in_array($statusFilter, ['new', 'read', 'replied'])) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['message_id'])) {
    $messageId = (int)$_POST['message_id'];
    $newStatus = sanitize($_POST['status'] ?? '');
    
    if (in_array($newStatus, ['new', 'read', 'replied'])) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $messageId]);
        setFlashMessage('success', 'Message status updated.');
        redirect('index.php' . ($statusFilter ? '?status=' . $statusFilter : ''));
    }
}

$pageTitle = 'Contact Messages';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage customer contact messages</p>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Status Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="btn-group" role="group">
                <a href="index.php" class="btn btn-<?= !$statusFilter ? 'primary' : 'outline-primary' ?>">All</a>
                <a href="index.php?status=new" class="btn btn-<?= $statusFilter === 'new' ? 'primary' : 'outline-primary' ?>">New</a>
                <a href="index.php?status=read" class="btn btn-<?= $statusFilter === 'read' ? 'primary' : 'outline-primary' ?>">Read</a>
                <a href="index.php?status=replied" class="btn btn-<?= $statusFilter === 'replied' ? 'primary' : 'outline-primary' ?>">Replied</a>
            </div>
        </div>
    </div>

    <?php if (empty($messages)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">No messages found.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($message['name']) ?></td>
                                <td><?= htmlspecialchars($message['email']) ?></td>
                                <td><?= htmlspecialchars($message['subject']) ?></td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($message['message']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= match($message['status']) {
                                        'new' => 'primary',
                                        'read' => 'info',
                                        'replied' => 'success',
                                        default => 'secondary'
                                    } ?>">
                                        <?= ucfirst($message['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($message['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#messageModal<?= $message['id'] ?>">
                                            View
                                        </button>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                            <input type="hidden" name="status" value="<?= $message['status'] === 'new' ? 'read' : 'replied' ?>">
                                            <button type="submit" name="update_status" class="btn btn-outline-success">
                                                <?= $message['status'] === 'new' ? 'Mark Read' : 'Mark Replied' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Modal -->
                            <div class="modal fade" id="messageModal<?= $message['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Message from <?= htmlspecialchars($message['name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Email:</strong> <?= htmlspecialchars($message['email']) ?></p>
                                            <p><strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?></p>
                                            <hr>
                                            <p><strong>Message:</strong></p>
                                            <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                            <?php if ($message['admin_notes']): ?>
                                                <hr>
                                                <p><strong>Admin Notes:</strong></p>
                                                <p class="text-muted"><?= nl2br(htmlspecialchars($message['admin_notes'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['subject']) ?>" 
                                               class="btn btn-primary">Reply via Email</a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

