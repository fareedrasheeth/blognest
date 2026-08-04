// assets/js/posts.js - Post Fetching, Single View & Delete Controllers

document.addEventListener('DOMContentLoaded', () => {
    // --- Delete Post Logic ---
    document.addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.delete-post-btn');
        if (!deleteBtn) return;

        const postId = deleteBtn.dataset.id;
        if (!postId) return;

        if (!confirm('Are you sure you want to delete this blog post? This action cannot be undone.')) {
            return;
        }

        try {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Deleting...';

            const response = await fetch(`api/posts/manage.php?id=${postId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _method: 'DELETE' })
            });

            const data = await response.json();

            if (data.success) {
                // If on single post view, redirect to home page
                if (window.location.pathname.endsWith('post.php')) {
                    window.location.href = 'index.php';
                } else {
                    // Remove post card dynamically from home grid
                    const postCard = deleteBtn.closest('.post-card');
                    if (postCard) {
                        postCard.style.opacity = '0';
                        setTimeout(() => postCard.remove(), 300);
                    }
                }
            } else {
                alert(data.message || 'Failed to delete post.');
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete';
            }
        } catch (err) {
            alert('Network error while deleting post.');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    });
});
