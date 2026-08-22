// assets/js/auth.js - Authentication Form Handlers (Login & Register)

document.addEventListener('DOMContentLoaded', () => {
    // --- Registration Form Handler ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const alertBoxId = 'authAlertBox';
            
            const username = document.getElementById('username').value.trim();
            const email    = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Client-side Validation
            if (!username || !email || !password || !confirmPassword) {
                showAlert(alertBoxId, 'Please fill in all fields.');
                return;
            }

            if (password.length < 6) {
                showAlert(alertBoxId, 'Password must be at least 6 characters long.');
                return;
            }

            if (password !== confirmPassword) {
                showAlert(alertBoxId, 'Passwords do not match.');
                return;
            }

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating Account...';

                const response = await fetch('api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email, password, confirmPassword })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    showAlert(alertBoxId, data.message || 'Registration failed.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Register Account';
                }
            } catch (err) {
                showAlert(alertBoxId, 'Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register Account';
            }
        });
    }

    // --- Login Form Handler ---
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const alertBoxId = 'authAlertBox';
            
            const identifier = document.getElementById('identifier').value.trim();
            const password   = document.getElementById('password').value;

            if (!identifier || !password) {
                showAlert(alertBoxId, 'Please enter both your credentials and password.');
                return;
            }

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing In...';

                const response = await fetch('api/auth/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ identifier, password })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    showAlert(alertBoxId, data.message || 'Invalid credentials.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            } catch (err) {
                showAlert(alertBoxId, 'Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        });
    }
});
