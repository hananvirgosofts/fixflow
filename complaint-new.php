<?php
require __DIR__ . '/includes/init.php';
require_role(['student', 'staff']);

$user = current_user();
$categories = all_categories();
$errors = [];
$old = [
    'title' => '',
    'category_id' => '',
    'priority' => 'medium',
    'location' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
        $errors[] = 'The upload is too large. Use an image smaller than 2 MB.';
    } elseif (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    }

    $old['title'] = clean_line(isset($_POST['title']) ? $_POST['title'] : '', 160);
    $old['category_id'] = (string) (int) (isset($_POST['category_id']) ? $_POST['category_id'] : 0);
    $old['priority'] = isset($_POST['priority']) ? (string) $_POST['priority'] : '';
    $old['location'] = clean_line(isset($_POST['location']) ? $_POST['location'] : '', 160);
    $old['description'] = clean_block(isset($_POST['description']) ? $_POST['description'] : '', 2000);

    if ($old['title'] === '') {
        $errors[] = 'Enter a title.';
    }
    $categoryOk = false;
    foreach ($categories as $category) {
        if ((string) $category['id'] === $old['category_id']) {
            $categoryOk = true;
        }
    }
    if (!$categoryOk) {
        $errors[] = 'Choose a category.';
    }
    if (!in_array($old['priority'], ['low', 'medium', 'high', 'urgent'], true)) {
        $errors[] = 'Choose a priority.';
    }
    if ($old['location'] === '') {
        $errors[] = 'Enter the location.';
    }
    if (text_length($old['description']) < 15) {
        $errors[] = 'Describe the problem in at least a short sentence.';
    }

    $imageName = null;
    if (!$errors) {
        $upload = store_upload(isset($_FILES['image']) ? $_FILES['image'] : ['error' => UPLOAD_ERR_NO_FILE]);
        if (isset($upload['error'])) {
            $errors[] = $upload['error'];
        } else {
            $imageName = $upload['name'];
            $result = create_complaint($user, [
                'title' => $old['title'],
                'category_id' => (int) $old['category_id'],
                'priority' => $old['priority'],
                'location' => $old['location'],
                'description' => $old['description'],
            ], $imageName);
            if ($result['errors']) {
                if ($imageName) {
                    @unlink(__DIR__ . '/uploads/' . $imageName);
                }
                $errors = $result['errors'];
            } else {
                flash('success', 'Complaint ' . $result['ticket_no'] . ' filed.');
                redirect('complaint.php?id=' . (int) $result['id']);
            }
        }
    }
}

$pageTitle = 'File a complaint';
$pageIntro = 'Tell the maintenance desk what is broken, where it is, and how urgent it is.';
$activeNav = 'new';
require __DIR__ . '/includes/app-header.php';
?>
<form class="form-panel narrow" method="post" action="complaint-new.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php render_errors($errors); ?>
    <div class="field">
        <label for="title">Title</label>
        <input class="control" type="text" id="title" name="title" value="<?= e($old['title']) ?>" maxlength="160" required>
    </div>
    <div class="field-row">
        <div class="field">
            <label for="category_id">Category</label>
            <select class="control" id="category_id" name="category_id" data-category required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" data-description="<?= e($category['description']) ?>" <?= (string) $category['id'] === (string) $old['category_id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="help" data-category-help></p>
        </div>
        <div class="field">
            <label for="priority">Priority</label>
            <select class="control" id="priority" name="priority" required>
                <?= priority_options($old['priority'], false) ?>
            </select>
        </div>
    </div>
    <div class="field">
        <label for="location">Location</label>
        <input class="control" type="text" id="location" name="location" value="<?= e($old['location']) ?>" maxlength="160" placeholder="CS Lab 2, Academic Block" required>
    </div>
    <div class="field">
        <label for="description">Description</label>
        <textarea class="control" id="description" name="description" maxlength="2000" required><?= e($old['description']) ?></textarea>
        <p class="help" data-count-for="description" data-max="2000">0 of 2000 characters</p>
    </div>
    <div class="field">
        <label for="image">Photograph</label>
        <input class="control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview>
        <p class="help">Optional. One JPG, PNG, or WebP image, up to 2 MB.</p>
        <p class="help" data-preview-note></p>
        <img class="preview" alt="Selected photograph preview" data-preview-target hidden>
    </div>
    <div class="form-actions">
        <button class="ff-btn" type="submit">File complaint</button>
    </div>
</form>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
