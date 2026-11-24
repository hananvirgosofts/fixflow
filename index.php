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

$pageTitle = 'Campus maintenance';
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
                    <p class="lede">Students and staff file maintenance problems in classrooms, laboratories, offices, and hostels. An administrator assigns each complaint to a maintenance worker, and the status stays on the record until the job is closed.</p>
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

<section class="container section">
    <h2>How a complaint moves</h2>
    <div class="row g-0 process">
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="step-no"><span>1</span><?= icon('complaints') ?></span>
                <h3>File the complaint</h3>
                <p>Describe the fault, where it is, and how urgent it is. A photograph is optional.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="step-no"><span>2</span><?= icon('user-check') ?></span>
                <h3>Assign a worker</h3>
                <p>An administrator reads the complaint and gives it to the maintenance unit that can repair it.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="step-no"><span>3</span><?= icon('wrench') ?></span>
                <h3>Update the job</h3>
                <p>The worker marks the job in progress and writes what was done when it is resolved.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-3">
            <article class="step">
                <span class="step-no"><span>4</span><?= icon('star') ?></span>
                <h3>Leave feedback</h3>
                <p>After the complaint is resolved, the person who filed it rates the repair.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section">
    <h2>Problems the desk covers</h2>
    <dl class="catalog">
        <?php foreach (category_catalog() as $category): ?>
            <div>
                <dt class="with-ico"><?= icon(category_icon_name($category[0])) ?><?= e($category[0]) ?></dt>
                <dd><?= e($category[1]) ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>

<section class="container section">
    <h2>Three people, one record</h2>
    <div class="roles">
        <section>
            <h3>Students and staff</h3>
            <p>File a complaint for a classroom, laboratory, office, or hostel, then follow the status from Submitted through Resolved.</p>
            <p class="example">Example: the projector in CS Lab 2 will not turn on.</p>
        </section>
        <section>
            <h3>Administrator</h3>
            <p>Review new complaints, assign the right worker, reject duplicates, and read the maintenance report.</p>
            <p class="example">Example: send a leaking tap to the plumbing unit.</p>
        </section>
        <section>
            <h3>Maintenance worker</h3>
            <p>Open the jobs assigned to your unit, mark them in progress, and record the repair when the work is finished.</p>
            <p class="example">Example: replace the microscope lamp and close the job.</p>
        </section>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
