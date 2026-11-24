<?php
require __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$id = (int) (isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : 0));
$complaint = $id ? fetch_complaint($id) : null;

if (!$complaint || !can_view_complaint($user, $complaint)) {
    http_response_code(404);
    $pageTitle = 'Complaint not found';
    $pageIntro = 'That complaint is not on your account.';
    $activeNav = 'complaints';
    require __DIR__ . '/includes/app-header.php';
    empty_box('Complaint not found', 'It may belong to someone else, or the link is out of date.', 'complaints.php', 'Back to complaints');
    require __DIR__ . '/includes/app-footer.php';
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    } else {
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
        if ($action === 'assign') {
            $result = assign_complaint($user, $id, (int) (isset($_POST['worker_id']) ? $_POST['worker_id'] : 0), clean_block(isset($_POST['note']) ? $_POST['note'] : '', 1000));
        } elseif ($action === 'reject') {
            $result = transition_complaint($user, $id, 'reject', clean_line(isset($_POST['reason']) ? $_POST['reason'] : '', 255));
        } elseif ($action === 'feedback') {
            $result = save_feedback($user, $complaint, (int) (isset($_POST['rating']) ? $_POST['rating'] : 0), clean_block(isset($_POST['comment']) ? $_POST['comment'] : '', 1000));
        } else {
            $result = transition_complaint($user, $id, $action, clean_block(isset($_POST['note']) ? $_POST['note'] : '', 1000));
        }
        if (!empty($result['errors'])) {
            $errors = $result['errors'];
        } else {
            flash('success', $result['message']);
            redirect('complaint.php?id=' . $id);
        }
    }
    $complaint = fetch_complaint($id);
}

$updates = fetch_updates($id);
$feedback = fetch_feedback($id);
$workers = $user['role'] === 'admin' ? active_workers() : [];
if ($workers) {
    $categoryName = $complaint['category_name'];
    usort($workers, function ($a, $b) use ($categoryName) {
        $aMatch = $a['specialty'] === $categoryName ? 0 : 1;
        $bMatch = $b['specialty'] === $categoryName ? 0 : 1;
        if ($aMatch === $bMatch) {
            return strcasecmp($a['full_name'], $b['full_name']);
        }
        return $aMatch - $bMatch;
    });
}

$image = public_image($complaint['image_path']);
$canChange = !in_array($complaint['status'], ['resolved', 'rejected'], true);
$pageTitle = $complaint['title'];
$pageIntro = $complaint['location'];
$activeNav = 'complaints';
$backHref = 'complaints.php';
$backLabel = $user['role'] === 'worker' ? 'My jobs' : 'All complaints';
require __DIR__ . '/includes/app-header.php';
render_errors($errors);
?>
<div class="detail">
    <article class="ticket-sheet">
        <header class="ticket-top">
            <span class="ticket-no"><?= e($complaint['ticket_no']) ?></span>
            <?php render_state($complaint['status']); ?>
        </header>
        <?php render_flow($complaint['status']); ?>
        <?php if ($complaint['status'] === 'rejected' && $complaint['rejection_reason']): ?>
            <div class="callout"><?= e($complaint['rejection_reason']) ?></div>
        <?php endif; ?>
        <dl class="facts">
            <div><dt>Where</dt><dd><?= e($complaint['location']) ?></dd></div>
            <div><dt>Category</dt><dd><?= e($complaint['category_name']) ?></dd></div>
            <div><dt>Priority</dt><dd><?php render_priority($complaint['priority']); ?></dd></div>
            <div><dt>Filed by</dt><dd><?= e($complaint['reporter_name']) ?><?php if ($complaint['reporter_department']): ?>, <?= e($complaint['reporter_department']) ?><?php endif; ?></dd></div>
            <?php if ($user['role'] === 'admin' || $user['role'] === 'worker'): ?>
                <div><dt>Phone</dt><dd><?= e($complaint['reporter_phone'] ? $complaint['reporter_phone'] : 'Not provided') ?></dd></div>
            <?php endif; ?>
            <div><dt>Assigned to</dt><dd><?= e($complaint['worker_name'] ? $complaint['worker_name'] . ($complaint['worker_specialty'] ? ', ' . $complaint['worker_specialty'] : '') : 'Not assigned yet') ?></dd></div>
            <div><dt>Filed</dt><dd><?= e(format_when($complaint['created_at'])) ?></dd></div>
            <?php if ($complaint['resolved_at']): ?>
                <div><dt>Resolved</dt><dd><?= e(format_when($complaint['resolved_at'])) ?></dd></div>
            <?php endif; ?>
        </dl>

        <h2>What was reported</h2>
        <p class="prose"><?= e($complaint['description']) ?></p>

        <?php if ($image): ?>
            <h2>Photograph</h2>
            <img class="photo" src="<?= e($image) ?>" alt="Photograph attached to <?= e($complaint['title']) ?>">
        <?php endif; ?>

        <h2>Progress</h2>
        <ol class="timeline">
            <?php foreach ($updates as $update): ?>
                <li>
                    <?php render_state($update['status']); ?>
                    <p><?= e($update['note']) ?></p>
                    <p class="by"><?= e($update['full_name']) ?> · <?= e(format_when($update['created_at'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if ($feedback): ?>
            <h2>Feedback</h2>
            <div class="callout good">
                <p>Rated <?= (int) $feedback['rating'] ?> out of 5.</p>
                <?php if ($feedback['comment']): ?><p class="prose"><?= e($feedback['comment']) ?></p><?php endif; ?>
                <p class="by"><?= e($feedback['full_name']) ?> · <?= e(format_when($feedback['created_at'])) ?></p>
            </div>
        <?php elseif ($complaint['status'] === 'resolved' && (int) $complaint['user_id'] !== (int) $user['id']): ?>
            <h2>Feedback</h2>
            <p>No feedback yet.</p>
        <?php endif; ?>
    </article>

    <aside class="detail-actions">
        <?php if ($user['role'] === 'admin' && $canChange): ?>
            <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                <input type="hidden" name="action" value="assign">
                <h2><?= $complaint['assigned_to'] ? 'Change the worker' : 'Assign a worker' ?></h2>
                <p class="help">Choose the unit that should take this job. A matching specialty is listed first.</p>
                <div class="field">
                    <label for="worker_id">Worker</label>
                    <select class="control" id="worker_id" name="worker_id" required>
                        <option value="">Select a worker</option>
                        <?php foreach ($workers as $worker): ?>
                            <?php $matches = $worker['specialty'] === $complaint['category_name']; ?>
                            <option value="<?= (int) $worker['id'] ?>" <?= (int) $complaint['assigned_to'] === (int) $worker['id'] ? 'selected' : '' ?>>
                                <?= e($worker['full_name']) ?>, <?= e($worker['specialty']) ?><?= $matches ? ' (matches)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="assign-note">Note</label>
                    <textarea class="control" id="assign-note" name="note" maxlength="1000"></textarea>
                </div>
                <button class="ff-btn" type="submit"><?= $complaint['assigned_to'] ? 'Reassign' : 'Assign worker' ?></button>
            </form>
            <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>" data-confirm="Reject this complaint?">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <h2>Reject this complaint</h2>
                <p class="help">Reject it when the complaint is a duplicate or is not a maintenance job.</p>
                <div class="field">
                    <label for="reason">Reason</label>
                    <input class="control" type="text" id="reason" name="reason" maxlength="255" required>
                </div>
                <button class="ff-btn danger" type="submit">Reject complaint</button>
            </form>
            <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                <input type="hidden" name="action" value="note">
                <h2>Add a desk note</h2>
                <div class="field">
                    <label for="admin-note">Note</label>
                    <textarea class="control" id="admin-note" name="note" maxlength="1000" required></textarea>
                </div>
                <button class="ff-btn secondary" type="submit">Add note</button>
            </form>
        <?php endif; ?>

        <?php if ($user['role'] === 'worker' && (int) $complaint['assigned_to'] === (int) $user['id']): ?>
            <?php if ($complaint['status'] === 'assigned'): ?>
                <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                    <input type="hidden" name="action" value="start">
                    <h2>Start this job</h2>
                    <p class="help">Mark it in progress when you begin the repair.</p>
                    <div class="field">
                        <label for="start-note">Note</label>
                        <textarea class="control" id="start-note" name="note" maxlength="1000"></textarea>
                    </div>
                    <button class="ff-btn" type="submit">Start work</button>
                </form>
            <?php elseif ($complaint['status'] === 'in_progress'): ?>
                <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                    <input type="hidden" name="action" value="note">
                    <h2>Add a progress note</h2>
                    <div class="field">
                        <label for="progress-note">Note</label>
                        <textarea class="control" id="progress-note" name="note" maxlength="1000" required></textarea>
                    </div>
                    <button class="ff-btn secondary" type="submit">Add note</button>
                </form>
                <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                    <input type="hidden" name="action" value="resolve">
                    <h2>Finish this job</h2>
                    <p class="help">Say what you repaired. The person who filed the complaint can then leave feedback.</p>
                    <div class="field">
                        <label for="resolve-note">What was done</label>
                        <textarea class="control" id="resolve-note" name="note" maxlength="1000" required></textarea>
                    </div>
                    <button class="ff-btn" type="submit">Mark resolved</button>
                </form>
            <?php elseif ($complaint['status'] === 'resolved'): ?>
                <div class="action-panel">
                    <h2>This job is closed</h2>
                    <p>The repair has been recorded.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ((int) $complaint['user_id'] === (int) $user['id'] && $user['role'] !== 'admin'): ?>
            <?php if ($complaint['status'] === 'submitted'): ?>
                <div class="action-panel">
                    <h2>Waiting for assignment</h2>
                    <p>An administrator will assign this to a maintenance worker.</p>
                </div>
            <?php elseif ($complaint['status'] === 'assigned'): ?>
                <div class="action-panel">
                    <h2>Assigned</h2>
                    <p><?= e($complaint['worker_name']) ?> has this job. Work has not started.</p>
                </div>
            <?php elseif ($complaint['status'] === 'in_progress'): ?>
                <div class="action-panel">
                    <h2>In progress</h2>
                    <p><?= e($complaint['worker_name']) ?> is working on this.</p>
                </div>
            <?php elseif ($complaint['status'] === 'resolved' && !$feedback): ?>
                <form class="action-panel" method="post" action="complaint.php?id=<?= (int) $complaint['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $complaint['id'] ?>">
                    <input type="hidden" name="action" value="feedback">
                    <fieldset class="rating">
                        <legend>How was the repair?</legend>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label>
                                    <input type="radio" name="rating" value="<?= $i ?>" required>
                                    <span><?= $i ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <p class="help">1 is poor and 5 is excellent.</p>
                    </fieldset>
                    <div class="field">
                        <label for="comment">Comment</label>
                        <textarea class="control" id="comment" name="comment" maxlength="1000"></textarea>
                    </div>
                    <button class="ff-btn" type="submit">Save feedback</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </aside>
</div>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
