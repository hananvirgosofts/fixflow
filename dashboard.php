<?php
require __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$counts = counts_for($user);
$focus = blank_filters();
$focus['status'] = $user['role'] === 'admin' ? 'submitted' : 'open';
$focusRows = fetch_complaints($user, $focus, 5, 0);
$updates = recent_updates($user, 6);
$rating = $user['role'] === 'worker' ? average_rating_for($user['id']) : null;

if ($user['role'] === 'admin') {
    $pageIntro = 'The maintenance desk for Thal University Bhakkar.';
    $focusTitle = 'Waiting for a worker';
    $focusEmpty = 'No complaint is waiting for a worker.';
    $focusLink = 'complaints.php?status=submitted';
    $focusLinkLabel = 'Review unassigned complaints';
} elseif ($user['role'] === 'worker') {
    $pageIntro = 'Jobs assigned to your unit.';
    $focusTitle = 'Jobs to update';
    $focusEmpty = 'You have no open jobs.';
    $focusLink = 'complaints.php?status=open';
    $focusLinkLabel = 'View open jobs';
} else {
    $pageIntro = 'Complaints you have filed, and where each one stands.';
    $focusTitle = 'Still open';
    $focusEmpty = 'Nothing is waiting on a repair.';
    $focusLink = 'complaints.php?status=open';
    $focusLinkLabel = 'View open complaints';
}

$pageTitle = 'Hello, ' . greeting_name($user['full_name']);
$activeNav = 'dashboard';
require __DIR__ . '/includes/app-header.php';
?>
<div class="page-actions">
    <?php if ($user['role'] === 'student' || $user['role'] === 'staff'): ?>
        <a class="ff-btn" href="complaint-new.php">File a complaint</a>
    <?php elseif ($user['role'] === 'admin'): ?>
        <a class="ff-btn" href="complaints.php?status=submitted">Assign waiting complaints</a>
        <a class="ff-btn secondary" href="reports.php">Open reports</a>
    <?php else: ?>
        <a class="ff-btn" href="complaints.php?status=open">Open my jobs</a>
    <?php endif; ?>
</div>
<dl class="ledger">
    <?php if ($user['role'] === 'worker'): ?>
        <div><a href="complaints.php?status=assigned"><dt>Not started</dt><dd><?= (int) $counts['assigned'] ?></dd></a></div>
        <div><a href="complaints.php?status=in_progress"><dt>In Progress</dt><dd><?= (int) $counts['in_progress'] ?></dd></a></div>
        <div><a href="complaints.php?status=resolved"><dt>Resolved</dt><dd><?= (int) $counts['resolved'] ?></dd></a></div>
        <div><div class="stat"><dt>Average rating</dt><dd><?= $rating === null ? '—' : e($rating) ?></dd></div></div>
    <?php elseif ($user['role'] === 'admin'): ?>
        <div><a href="complaints.php"><dt>Total complaints</dt><dd><?= (int) $counts['total'] ?></dd></a></div>
        <div><a href="complaints.php?status=pending"><dt>Pending</dt><dd><?= (int) $counts['pending'] ?></dd></a></div>
        <div><a href="complaints.php?status=in_progress"><dt>In Progress</dt><dd><?= (int) $counts['in_progress'] ?></dd></a></div>
        <div><a href="complaints.php?status=resolved"><dt>Resolved</dt><dd><?= (int) $counts['resolved'] ?></dd></a></div>
    <?php else: ?>
        <div><a href="complaints.php"><dt>Your complaints</dt><dd><?= (int) $counts['total'] ?></dd></a></div>
        <div><a href="complaints.php?status=open"><dt>Still open</dt><dd><?= (int) $counts['open'] ?></dd></a></div>
        <div><a href="complaints.php?status=resolved"><dt>Resolved</dt><dd><?= (int) $counts['resolved'] ?></dd></a></div>
        <div><a href="complaints.php?status=rejected"><dt>Rejected</dt><dd><?= (int) $counts['rejected'] ?></dd></a></div>
    <?php endif; ?>
</dl>
<?php if ($user['role'] === 'admin'): ?>
    <p class="help ledger-note">Pending means submitted or assigned, and not yet in progress. Rejected complaints: <?= (int) $counts['rejected'] ?>.</p>
<?php endif; ?>

<h2>How the work is split</h2>
<?php render_status_chart($counts); ?>

<div class="split">
    <section>
        <h2><?= e($focusTitle) ?></h2>
        <?php if (!$focusRows): ?>
            <?php if ($user['role'] !== 'worker' && $user['role'] !== 'admin' && (int) $counts['total'] === 0): ?>
                <?php empty_box('You have not filed a complaint yet.', 'Describe the fault, the room, and how urgent it is.', 'complaint-new.php', 'File a complaint'); ?>
            <?php else: ?>
                <p><?= e($focusEmpty) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="ticket-list">
                <?php foreach ($focusRows as $row): ?>
                    <?php render_ticket($row, $user['role']); ?>
                <?php endforeach; ?>
            </div>
            <p class="section-link"><a class="text-link" href="<?= e($focusLink) ?>"><?= e($focusLinkLabel) ?></a></p>
        <?php endif; ?>
    </section>
    <section>
        <h2>Recent updates</h2>
        <?php if (!$updates): ?>
            <p>No updates have been recorded yet.</p>
        <?php else: ?>
            <ol class="timeline">
                <?php foreach ($updates as $update): ?>
                    <li>
                        <a class="text-link" href="complaint.php?id=<?= (int) $update['id'] ?>"><?= e($update['ticket_no']) ?></a>
                        <?php render_state($update['status']); ?>
                        <p><?= e($update['note']) ?></p>
                        <p class="by"><?= e($update['full_name']) ?> · <?= e(format_when($update['created_at'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
