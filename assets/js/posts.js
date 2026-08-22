// assets/js/posts.js - Post Fetching, Single View & Delete Controllers

document.addEventListener('DOMContentLoaded', () => {
    const showDeleteConfirmation = (deleteBtn) => {
        const actions = deleteBtn.closest('.post-actions');
        if (!actions || actions.querySelector('.delete-confirmation')) return;

        const confirmation = document.createElement('div');
        confirmation.className = 'delete-confirmation';
        confirmation.innerHTML = `
            <span>Delete this article?</span>
            <button type="button" class="delete-confirm-btn">Delete</button>
            <button type="button" class="delete-cancel-btn">Cancel</button>
        `;
        actions.appendChild(confirmation);
    };

    const deletePost = async (deleteBtn, confirmation) => {
        const postId = deleteBtn.dataset.id;
        if (!postId) return;

        const confirmBtn = confirmation.querySelector('.delete-confirm-btn');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';

        try {
            const response = await fetch(`api/posts/manage.php?id=${postId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _method: 'DELETE' })
            });

            const data = await response.json();

            if (data.success) {
                if (window.location.pathname.endsWith('post.php')) {
                    window.location.href = 'index.php';
                } else {
                    const postCard = deleteBtn.closest('.post-card');
                    if (postCard) {
                        postCard.style.opacity = '0';
                        setTimeout(() => postCard.remove(), 300);
                    }
                }
            } else {
                confirmation.querySelector('span').textContent = data.message || 'Could not delete article.';
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Delete';
            }
        } catch (err) {
            confirmation.querySelector('span').textContent = 'Network error. Please try again.';
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete';
        }
    };

    document.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.delete-post-btn');
        if (deleteBtn) {
            e.preventDefault();
            showDeleteConfirmation(deleteBtn);
            return;
        }

        const cancelBtn = e.target.closest('.delete-cancel-btn');
        if (cancelBtn) {
            cancelBtn.closest('.delete-confirmation').remove();
            return;
        }

        const confirmBtn = e.target.closest('.delete-confirm-btn');
        if (confirmBtn) {
            const confirmation = confirmBtn.closest('.delete-confirmation');
            const actions = confirmation.closest('.post-actions');
            deletePost(actions.querySelector('.delete-post-btn'), confirmation);
        }
    });
});
