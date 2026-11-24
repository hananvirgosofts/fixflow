<?php
require __DIR__ . '/includes/init.php';

$sampleLive = false;
$sample = null;
$quiet = connect_database(true);
if ($quiet) {
    try {
        $stmt = $quiet->query('SELECT c.ticket_no, c.title, c.location, c.priority, c.status, cat.name AS category_name, w.full_name AS worker_name
            FROM complaints c
            JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN users w ON w.id = c.assigned_to
            ORDER BY c.id ASC
            LIMIT 1');
        $sample = $stmt->fetch();
        $sampleLive = (bool) $sample;
    } catch (Exception $e) {
        $sample = null;
    }
}
if (!$sample) {
    $sample = [
        'ticket_no' => 'FF-' . date('Y') . '-1001',
        'title' => 'Projector will not power on',
        'location' => 'CS Lab 2, Academic Block',
        'priority' => 'high',
        'status' => 'in_progress',
        'category_name' => 'Electrical',
        'worker_name' => 'Electrical Unit',
    ];
}

$pageTitle = 'Campus maintenance desk';
$signedIn = !empty($_SESSION['user_id']);
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-lg-6">
                <div class="hero-copy">
                    <p class="campus-line">Campus maintenance for <?= e(UNIVERSITY) ?></p>
                    <h1>Report a fault.<br>Follow it until it is fixed.</h1>
                    <p class="lede">Students and staff file a fault in a classroom, laboratory, office, or hostel. The desk gives it a ticket, an administrator assigns a worker, and the same record stays open until the repair is rated.</p>
                    <div class="actions">
                        <?php if ($signedIn): ?>
                            <a class="ff-btn" href="dashboard.php">Open your overview</a>
                        <?php else: ?>
                            <a class="ff-btn" href="register.php">Create an account</a>
                            <a class="ff-btn secondary" href="login.php">Sign in</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-stage">
                <article class="ticket-sample">
                    <header class="ticket-top">
                        <span class="ticket-no"><?= e($sample['ticket_no']) ?></span>
                        <?php render_state($sample['status']); ?>
                    </header>
                    <h2><?= e($sample['title']) ?></h2>
                    <dl>
                        <div><dt>Where</dt><dd class="with-ico"><?= icon('pin') ?><?= e($sample['location']) ?></dd></div>
                        <div><dt>Category</dt><dd class="with-ico"><?= icon(category_icon_name($sample['category_name'])) ?><?= e($sample['category_name']) ?></dd></div>
                        <div><dt>Priority</dt><dd><?php render_priority($sample['priority']); ?></dd></div>
                        <div><dt>Assigned to</dt><dd><?= e($sample['worker_name'] ? $sample['worker_name'] : 'Not assigned yet') ?></dd></div>
                    </dl>
                    <p class="ticket-note"><?= $sampleLive ? 'From the practice database.' : 'A sample work order. Install the database to work with live records.' ?></p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="desk-strip" aria-label="What stays on the record">
    <div>
        <span class="mod-mark mod-file"><?= icon('complaints') ?></span>
        <strong>One ticket</strong>
        <span>Every complaint receives a number that does not change.</span>
    </div>
    <div>
        <span class="mod-mark mod-assign"><?= icon('user-check') ?></span>
        <strong>A visible status</strong>
        <span>Submitted, assigned, in progress, resolved, or rejected.</span>
    </div>
    <div>
        <span class="mod-mark mod-work"><?= icon('wrench') ?></span>
        <strong>The right unit</strong>
        <span>Electrical, plumbing, furniture, laboratory, building, and housekeeping.</span>
    </div>
    <div>
        <span class="mod-mark mod-rate"><?= icon('star') ?></span>
        <strong>A rating at the end</strong>
        <span>The person who filed the complaint rates the repair once.</span>
    </div>
</section>

<section class="container section" id="movement">
    <h2>How a complaint moves</h2>
    <p class="section-lead">The ticket number stays the same from the first report to the rating.</p>
    <div class="row g-0 process">
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="mod-mark mod-file"><?= icon('complaints') ?></span>
                <span class="step-no">1</span>
                <h3>File the complaint</h3>
                <p>Describe the fault, where it is, and how urgent it is. A photograph is optional.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="mod-mark mod-assign"><?= icon('user-check') ?></span>
                <span class="step-no">2</span>
                <h3>Assign a worker</h3>
                <p>An administrator reads the complaint and gives it to the maintenance unit that can repair it.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="mod-mark mod-work"><?= icon('wrench') ?></span>
                <span class="step-no">3</span>
                <h3>Update the job</h3>
                <p>The worker marks the job in progress and writes what was done when it is resolved.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="mod-mark mod-rate"><?= icon('star') ?></span>
                <span class="step-no">4</span>
                <h3>Leave feedback</h3>
                <p>After the complaint is resolved, the person who filed it rates the repair.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section" id="coverage">
    <h2>Problems the desk covers</h2>
    <p class="section-lead">Choose the unit that can repair the fault. A photograph can be attached when it helps.</p>
    <dl class="catalog">
        <?php foreach (category_catalog() as $category): ?>
            <?php $catIcon = category_icon_name($category[0]); ?>
            <div>
                <dt><span class="cat-ico cat-<?= e($catIcon) ?>"><?= icon($catIcon) ?></span><?= e($category[0]) ?></dt>
                <dd><?= e($category[1]) ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>

<section class="container section" id="people">
    <h2>Three people, one record</h2>
    <p class="section-lead">The person who reports the fault, the administrator, and the worker all see the same complaint.</p>
    <div class="roles">
        <section>
            <span class="mod-mark mod-file"><?= icon('complaints') ?></span>
            <h3>Students and staff</h3>
            <p>File a complaint for a classroom, laboratory, office, or hostel, then follow the status from Submitted through Resolved.</p>
            <p class="example">Example: the projector in CS Lab 2 will not turn on.</p>
        </section>
        <section>
            <span class="mod-mark mod-assign"><?= icon('user-check') ?></span>
            <h3>Administrator</h3>
            <p>Review new complaints, assign the right worker, reject duplicates, and read the maintenance report.</p>
            <p class="example">Example: send a leaking tap to the plumbing unit.</p>
        </section>
        <section>
            <span class="mod-mark mod-work"><?= icon('wrench') ?></span>
            <h3>Maintenance worker</h3>
            <p>Open the jobs assigned to your unit, mark them in progress, and record the repair when the work is finished.</p>
            <p class="example">Example: replace the microscope lamp and close the job.</p>
        </section>
    </div>
</section>

<section class="close-band">
    <div class="container">
        <h2>Open the maintenance desk</h2>
        <p>Create an account if you study or work at <?= e(UNIVERSITY) ?>. An administrator or a maintenance unit signs in with the account they were given.</p>
        <div class="actions">
            <?php if ($signedIn): ?>
                <a class="ff-btn" href="dashboard.php">Open your overview</a>
            <?php else: ?>
                <a class="ff-btn" href="register.php">Create an account</a>
                <a class="ff-btn secondary" href="login.php">Sign in</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
