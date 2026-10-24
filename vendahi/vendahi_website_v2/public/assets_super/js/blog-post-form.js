document.addEventListener('DOMContentLoaded', function() {
    var data = window.BlogPostData || {};
    var trans = data.trans || {};

    // Auto-slug from title
    var titleInput = document.getElementById('postTitle');
    var slugInput = document.getElementById('postSlug');
    var isNew = data.isNew;
    var slugEdited = false;

    if (isNew) {
        slugInput.addEventListener('input', function() { slugEdited = true; });
        titleInput.addEventListener('input', function() {
            if (!slugEdited) {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }
        });
    }

    // Visual preview toggle
    var showingPreview = false;
    var toggleBtn = document.getElementById('toggleEditor');
    var toggleLabel = document.getElementById('toggleLabel');
    var codeEditor = document.getElementById('codeEditor');
    var visualPreview = document.getElementById('visualPreview');
    var textarea = document.getElementById('postContent');
    var previewContent = document.getElementById('previewContent');

    toggleBtn.addEventListener('click', function() {
        showingPreview = !showingPreview;
        if (showingPreview) {
            previewContent.innerHTML = textarea.value;
            visualPreview.style.display = 'block';
            codeEditor.style.display = 'none';
            toggleLabel.textContent = trans.labelSource;
        } else {
            visualPreview.style.display = 'none';
            codeEditor.style.display = '';
            toggleLabel.textContent = trans.labelVisual;
        }
    });

    // SEO character counters (recommended lengths shown next to the count)
    function bindCounter(inputId, countId) {
        var input = document.getElementById(inputId);
        var count = document.getElementById(countId);
        if (!input || !count) { return; }
        var update = function() { count.textContent = String(input.value.length); };
        input.addEventListener('input', update);
        update();
    }
    bindCounter('metaTitle', 'metaTitleCount');
    bindCounter('metaDescription', 'metaDescriptionCount');

    // Cover image client-side preview
    var coverInput = document.getElementById('coverImageInput');
    var coverPreview = document.getElementById('coverImagePreview');
    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function() {
            var file = this.files && this.files[0];
            if (!file) { return; }
            var reader = new FileReader();
            reader.onload = function(e) {
                coverPreview.src = e.target.result;
                coverPreview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});
