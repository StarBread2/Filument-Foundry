
// SHOW PASSWORD TOGGLE 
    document.addEventListener('DOMContentLoaded', () => {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('inputPassword');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', () => {
                // Toggle the input type
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Optional: toggle icon
                togglePassword.textContent = type === 'password' ? '👁' : '👁';
            });
        }
    });


