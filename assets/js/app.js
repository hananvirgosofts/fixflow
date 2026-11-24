(function () {
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-toggle-password]');
        if (toggle) {
            var input = document.getElementById(toggle.getAttribute('data-toggle-password'));
            if (!input) {
                return;
            }
            var showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            toggle.textContent = showing ? 'Hide' : 'Show';
            return;
        }
        if (event.target.closest('[data-print]')) {
            window.print();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        var message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });

    var description = document.getElementById('description');
    var counter = document.querySelector('[data-count-for="description"]');
    if (description && counter) {
        var max = parseInt(counter.getAttribute('data-max') || '2000', 10);
        var updateCount = function () {
            counter.textContent = description.value.length + ' of ' + max + ' characters';
        };
        description.addEventListener('input', updateCount);
        updateCount();
    }

    var fileInput = document.querySelector('[data-preview]');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var preview = document.querySelector('[data-preview-target]');
            var note = document.querySelector('[data-preview-note]');
            var chosen = fileInput.files && fileInput.files[0];
            if (!chosen || !preview) {
                return;
            }
            if (chosen.size > 2 * 1024 * 1024) {
                if (note) {
                    note.textContent = 'That file is larger than 2 MB.';
                }
                fileInput.value = '';
                preview.hidden = true;
                return;
            }
            var reader = new FileReader();
            reader.onload = function () {
                preview.src = reader.result;
                preview.hidden = false;
            };
            reader.readAsDataURL(chosen);
            if (note) {
                note.textContent = chosen.name;
            }
        });
    }

    var category = document.querySelector('[data-category]');
    var categoryHelp = document.querySelector('[data-category-help]');
    if (category && categoryHelp) {
        var syncCategory = function () {
            var option = category.options[category.selectedIndex];
            categoryHelp.textContent = option ? (option.getAttribute('data-description') || '') : '';
        };
        category.addEventListener('change', syncCategory);
        syncCategory();
    }
})();
