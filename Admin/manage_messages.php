<?php
require 'session_check.php';
require '../db_connect.php';

$messages = [];
try {
    $stmt = $pdo->query("SELECT message_id, full_name, email, phone, subject, message, sent_at FROM contact_messages ORDER BY sent_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
}

$viewId = $_GET['view'] ?? '';
$activeMessage = null;
foreach ($messages as $m) {
    if ((string)$m['message_id'] === (string)$viewId) {
        $activeMessage = $m;
        break;
    }
}

$deleteMsg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages | Gym Gear Store</title>
<link rel="stylesheet" href="assets/admin.css?v=2">
<style>
    .msg-layout { display: grid; grid-template-columns: 380px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1000px) { .msg-layout { grid-template-columns: 1fr; } }

    .msg-list { max-height: 620px; overflow-y: auto; }
    .msg-row {
        display: block; padding: 14px 16px; border-radius: 8px; margin-bottom: 8px;
        border: 1px solid var(--border); text-decoration: none; color: inherit;
    }
    .msg-row:hover { background: #f8f9fb; }
    .msg-row.active { border-color: var(--orange); background: #fff6f2; }
    .msg-row .row-top { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
    .msg-row .name { font-weight: bold; color: var(--navy); font-size: 13px; }
    .msg-row .time { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
    .msg-row .subject { font-size: 13px; color: #333; margin-bottom: 3px; }
    .msg-row .preview { font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .msg-detail { min-height: 300px; }
    .msg-detail .detail-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--border); }
    .msg-detail h2 { font-size: 18px; color: var(--navy); margin-bottom: 8px; }
    .msg-detail .meta-line { font-size: 13px; color: var(--text-muted); margin-bottom: 3px; }
    .msg-detail .meta-line strong { color: #333; }
    .msg-detail .body-text { font-size: 14px; line-height: 1.7; color: #333; white-space: pre-wrap; }
    .msg-empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>GYM GEAR STORE</h2>
        <span>Admin Panel</span>
    </div>
    <nav>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="manage_orders.php">Orders</a></li>
            <li><a href="manage_clients.php">Registered Clients</a></li>
            <li><a href="manage_messages.php" class="active">Messages</a></li>
        </ul>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Messages</h1>
            <div class="date"><?= count($messages) ?> message<?= count($messages) === 1 ? '' : 's' ?> received</div>
        </div>
        <div class="topbar-actions">
            <div class="admin-chip">
                <span class="dot"></span>
                <?= htmlspecialchars($_SESSION['admin_name']) ?>
            </div>
            <a href="logout.php" class="topbar-logout">Log Out</a>
        </div>
    </div>

    <?php if ($deleteMsg): ?><div class="alert alert-success"><?= htmlspecialchars($deleteMsg) ?></div><?php endif; ?>

    <div class="msg-layout">
        <div class="panel msg-list">
            <?php if (empty($messages)): ?>
                <div class="empty-row">No messages yet.</div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <a class="msg-row <?= $activeMessage && $activeMessage['message_id'] === $m['message_id'] ? 'active' : '' ?>"
                       href="manage_messages.php?view=<?= $m['message_id'] ?>">
                        <div class="row-top">
                            <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                            <span class="time"><?= date('M j, g:i A', strtotime($m['sent_at'])) ?></span>
                        </div>
                        <div class="subject"><?= htmlspecialchars($m['subject']) ?></div>
                        <div class="preview"><?= htmlspecialchars(mb_strimwidth($m['message'], 0, 60, '...')) ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="panel msg-detail">
            <?php if (!$activeMessage): ?>
                <div class="msg-empty-state">Select a message on the left to read it.</div>
            <?php else: ?>
                <div class="detail-head">
                    <div>
                        <h2><?= htmlspecialchars($activeMessage['subject']) ?></h2>
                        <div class="meta-line"><strong>From:</strong> <?= htmlspecialchars($activeMessage['full_name']) ?></div>
                        <div class="meta-line"><strong>Email:</strong> <?= htmlspecialchars($activeMessage['email']) ?></div>
                        <?php if (!empty($activeMessage['phone'])): ?>
                            <div class="meta-line"><strong>Phone:</strong> <?= htmlspecialchars($activeMessage['phone']) ?></div>
                        <?php endif; ?>
                        <div class="meta-line"><strong>Received:</strong> <?= date('M j, Y \a\t g:i A', strtotime($activeMessage['sent_at'])) ?></div>
                    </div>
                    <a class="btn-icon btn-delete"
                       href="delete_message.php?id=<?= $activeMessage['message_id'] ?>"
                       onclick="return confirm('Delete this message?')">Delete</a>
                </div>
                <div class="body-text"><?= htmlspecialchars($activeMessage['message']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>