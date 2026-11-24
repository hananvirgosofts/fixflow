<?php
require __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$filters = read_filters();
$page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
$total = count_complaints($user, $filters);
$pages = max(1, (int) ceil($total / PER_PAGE));
if ($page > $pages) {
    $page = $pages;
}
$rows = fetch_complaints($user, $filters, PER_PAGE, ($page - 1) * PER_PAGE);
$categories = all_categories();

if ($user['role'] === 'worker') {
    $pageTitle = 'My jobs';
    $pageIntro = 'Complaints assigned to your unit.';
} elseif ($user['role'] === 'admin') {
    $pageTitle = 'Complaints';
    $pageIntro = 'Every complaint on the maintenance desk.';
} else {
    $pageTitle = 'Complaints';
    $pageIntro = 'Complaints filed by you.';
}
$activeNav = 'complaints';
require __DIR__ . '/includes/app-header.php';
?>
<div class="toolbar">
    <form class="filters" method="get" action="complaints.php">
        <div class="field">
            <label for="q">Search</label>
            <input class="control" type="search" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Ticket, title, or place">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select class="control" id="status" name="status"><?= status_filter_options($filters['status']) ?></select>
        </div>
        <div class="field">
            <label for="priority">Priority</label>
            <select class="control" id="priority" name="priority"><?= priority_options($filters['priority'], true) ?></select>
        </div>
        <div class="field">
            <label for="category">Category</label>
            <select class="control" id="category" name="category">
                <option value="0">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button class="ff-btn" type="submit">Apply</button>
        </div>
    </form>
    <?php if ($user['role'] === 'student' || $user['role'] === 'staff'): ?>
        <a class="ff-btn" href="complaint-new.php">File a complaint</a>
    <?php endif; ?>
</div>
<?php
$filtered = $filters['q'] !== '' || $filters['status'] !== '' || $filters['priority'] !== '' || (int) $filters['category'] > 0;
?>
<?php if ($rows): ?>
    <p class="help">Showing <?= count($rows) ?> of <?= (int) $total ?>.<?php if ($filtered): ?> <a class="text-link" href="complaints.php">Clear filters</a><?php endif; ?></p>
<?php endif; ?>

<?php if (!$rows && !$filtered && ($user['role'] === 'student' || $user['role'] === 'staff')): ?>
    <?php empty_box('You have not filed a complaint yet.', 'Describe the fault, the room, and how urgent it is.', 'complaint-new.php', 'File a complaint'); ?>
<?php elseif (!$rows): ?>
    <?php empty_box('No complaints to show.', $user['role'] === 'worker' ? 'When an administrator assigns a job, it will appear here.' : 'Try a different filter, or wait for a new complaint.'); ?>
<?php else: ?>
    <div class="ticket-list">
        <?php foreach ($rows as $row): ?>
            <?php render_ticket($row, $user['role']); ?>
        <?php endforeach; ?>
    </div>
    <?php render_pager($page, $pages, 'complaints.php'); ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
