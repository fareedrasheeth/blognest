// assets/js/main.js - Global JS Utilities & Event Handlers

document.addEventListener('DOMContentLoaded', () => {
    // Global Logout Confirmation
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch('api/auth/logout.php?json=1', { method: 'POST' });
                const resData = await response.json();
                if (resData.success) {
                    window.location.href = 'index.php';
                }
            } catch (err) {
                window.location.href = 'api/auth/logout.php';
            }
        });
    }
});

/**
 * Show temporary alert message on page
 */
function showAlert(containerId, message, type = 'danger') {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = `
        <div class="alert alert-${type}">
            <span>${type === 'danger' ? '⚠️' : '✅'}</span>
            <div>${message}</div>
        </div>
    `;
}
