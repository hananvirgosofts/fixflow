<?php
require __DIR__ . '/includes/init.php';
require_role(['admin']);

$user = current_user();
$filters = read_filters();
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    export_complaints_csv($user, $filters);
}

$counts = [
    'submitted' => 0,
    'assigned' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'rejected' => 0,
];
list($where, $params) = complaint_conditions($user, $filters);
$statusStmt = db()->prepare("SELECT c.status, COUNT(*) AS total FROM complaints c WHERE $where GROUP BY c.status");
$statusStmt->execute($params);
foreach ($statusStmt as $row) {
    if (isset($counts[$row['status']])) {
        $counts[$row['status']] = (int) $row['total'];
    }
}
$counts['total'] = array_sum($counts);
$counts['pending'] = $counts['submitted'] + $counts['assigned'];
$categories = category_stats($filters);
$maxCategory = 0;
foreach ($categories as $category) {
    if ((int) $category['total'] > $maxCategory) {
        $maxCategory = (int) $category['total'];
    }
}
$workers = worker_rows();
$totalRows = count_complaints($user, $filters);
$rows = fetch_complaints($user, $filters, 200, 0);
$allCategories = all_categories();

$pageTitle = 'Reports';
$pageIntro = 'Counts follow the filters below. Pending means submitted or assigned, and not yet in progress.';
$activeNav = 'reports';
require __DIR__ . '/includes/app-header.php';
?>
<p class="print-only">FixFlow maintenance report · <?= e(date('d M Y, H:i')) ?></p>
<form class="filters no-print" method="get" action="reports.php">
    <div class="field">
        <label for="from">From</label>
        <input class="control" type="date" id="from" name="from" value="<?= e($filters['from']) ?>">
    </div>
    <div class="field">
        <label for="to">To</label>
        <input class="control" type="date" id="to" name="to" value="<?= e($filters['to']) ?>">
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
            <?php foreach ($allCategories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-actions">
        <button class="ff-btn" type="submit">Apply</button>
        <a class="ff-btn secondary" href="reports.php?<?= e(http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>">Download CSV</a>
        <button class="ff-btn secondary" type="button" data-print>Print report</button>
    </div>
</form>

<dl class="ledger">
    <div><div class="stat"><dt>Total</dt><dd><?= (int) $counts['total'] ?></dd></div></div>
    <div><div class="stat"><dt>Pending</dt><dd><?= (int) $counts['pending'] ?></dd></div></div>
    <div><div class="stat"><dt>In Progress</dt><dd><?= (int) $counts['in_progress'] ?></dd></div></div>
    <div><div class="stat"><dt>Resolved</dt><dd><?= (int) $counts['resolved'] ?></dd></div></div>
</dl>
<p class="help ledger-note">Rejected in this filter: <?= (int) $counts['rejected'] ?>.</p>

<h2>How the work is split</h2>
<?php render_status_chart($counts); ?>

<h2>By category</h2>
<?php if ($maxCategory === 0): ?>
    <p>No complaints match these filters.</p>
<?php else: ?>
    <?php $barIndex = 0; ?>
    <?php foreach ($categories as $category): ?>
        <?php $width = $maxCategory > 0 ? (int) round(((int) $category['total'] / $maxCategory) * 100) : 0; ?>
        <div class="bar-row">
            <span class="with-ico"><?= icon(category_icon_name($category['name'])) ?><?= e($category['name']) ?></span>
            <span class="bar-track"><span class="bar-fill" style="width: <?= $width ?>%; animation-delay: <?= (int) $barIndex * 70 ?>ms"></span></span>
            <span class="bar-num"><?= (int) $category['total'] ?></span>
        </div>
        <?php $barIndex++; ?>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Worker performance</h2>
<p class="help">Open jobs, resolved jobs, and ratings count every complaint, not only the date filter.</p>
<p class="sheet-hint">Swipe sideways to see every column.</p>
<div class="sheet-wrap">
    <table class="sheet">
        <thead>
            <tr>
                <th>Worker</th>
                <th>Specialty</th>
                <th>Open jobs</th>
                <th>Resolved</th>
                <th>Average rating</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($workers as $worker): ?>
                <tr>
                    <td><?= e($worker['full_name']) ?></td>
                    <td><?= e($worker['specialty']) ?></td>
                    <td><?= (int) $worker['open_jobs'] ?></td>
                    <td><?= (int) $worker['done_jobs'] ?></td>
                    <td><?php if ($worker['avg_rating'] === null): ?>—<?php else: ?><span class="with-ico star-ico"><?= icon('star') ?><?= e($worker['avg_rating']) ?></span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2>Complaint list</h2>
<p class="help"><?php if ($totalRows > 200): ?>Showing the latest 200 of <?= (int) $totalRows ?>. Download the CSV for the full list.<?php else: ?><?= (int) $totalRows ?> complaint<?= $totalRows === 1 ? '' : 's' ?> in this filter.<?php endif; ?></p>
<div class="sheet-wrap">
    <table class="sheet">
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Problem</th>
                <th>Category</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Filed by</th>
                <th>Assigned to</th>
                <th>Filed</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="9">No complaints match these filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><a href="complaint.php?id=<?= (int) $row['id'] ?>"><?= e($row['ticket_no']) ?></a></td>
                    <td><?= e($row['title']) ?></td>
                    <td><?= e($row['category_name']) ?></td>
                    <td><?= e($row['location']) ?></td>
                    <td><?= e(priority_label($row['priority'])) ?></td>
                    <td><?php render_state($row['status']); ?></td>
                    <td><?= e($row['reporter_name']) ?></td>
                    <td><?= e($row['worker_name'] ? $row['worker_name'] : '—') ?></td>
                    <td><?= e(format_day($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
