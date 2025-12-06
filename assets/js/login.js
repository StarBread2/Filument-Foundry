document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('inputPassword');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            togglePassword.setAttribute(
                'src',
                type === 'password'
                    ? togglePassword.dataset.eye
                    : togglePassword.dataset.eyeOff
            );
        });
    }
});
