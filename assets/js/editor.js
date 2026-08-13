// assets/js/editor.js - Blog Editor & Live Markdown Preview Controller

document.addEventListener('DOMContentLoaded', () => {
    const editorForm    = document.getElementById('postEditorForm');
    const contentInput  = document.getElementById('content');
    const previewTabBtn = document.getElementById('previewTabBtn');
    const writeTabBtn   = document.getElementById('writeTabBtn');
    const writePane     = document.getElementById('writePane');
    const previewPane   = document.getElementById('previewPane');
    const previewBox    = document.getElementById('previewContent');

    // --- Live Markdown Preview Tabs ---
    if (previewTabBtn && writeTabBtn && contentInput) {
        const updatePreview = () => {
            const rawText = contentInput.value.trim();
            if (!rawText) {
                previewBox.innerHTML = '<p class="text-muted" style="font-style: italic;">Nothing to preview yet. Start typing your blog content in Markdown!</p>';
            } else if (typeof marked !== 'undefined') {
                previewBox.innerHTML = marked.parse(rawText);
            } else {
                previewBox.textContent = rawText;
            }
        };

        previewTabBtn.addEventListener('click', () => {
            writeTabBtn.classList.remove('active');
            previewTabBtn.classList.add('active');
            writePane.classList.add('hidden');
            previewPane.classList.remove('hidden');
            updatePreview();
        });

        writeTabBtn.addEventListener('click', () => {
            previewTabBtn.classList.remove('active');
            writeTabBtn.classList.add('active');
            previewPane.classList.add('hidden');
            writePane.classList.remove('hidden');
        });

        contentInput.addEventListener('input', () => {
            if (previewPane && !previewPane.classList.contains('hidden')) {
                updatePreview();
            }
        });
    }

    // --- Form Submit Handler (Create & Update) ---
    if (editorForm) {
        editorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn  = editorForm.querySelector('button[type="submit"]');
            const alertBoxId = 'editorAlertBox';
            const isEditMode = editorForm.dataset.mode === 'edit';
            const postId     = editorForm.dataset.postId;

            const title   = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!title || !content) {
                showAlert(alertBoxId, 'Please provide both a title and content for your post.');
                return;
            }

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = isEditMode ? 'Saving Changes...' : 'Publishing Post...';

                let endpoint = 'api/posts/index.php';
                let method   = 'POST';
                let payload  = { title, content };

                if (isEditMode && postId) {
                    endpoint = `api/posts/manage.php?id=${postId}`;
                    payload._method = 'PUT';
                }

                const response = await fetch(endpoint, {
                    method: 'POST', // Sent as POST with _method parameter if updating
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(alertBoxId, data.message, 'success');
                    setTimeout(() => {
                        const targetId = isEditMode ? postId : (data.data?.post_id || '');
                        window.location.href = targetId ? `post.php?id=${targetId}` : 'index.php';
                    }, 1000);
                } else {
                    showAlert(alertBoxId, data.message || 'Failed to save post.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = isEditMode ? 'Save Changes' : 'Publish Post';
                }
            } catch (err) {
                showAlert(alertBoxId, 'Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = isEditMode ? 'Save Changes' : 'Publish Post';
            }
        });
    }
});
