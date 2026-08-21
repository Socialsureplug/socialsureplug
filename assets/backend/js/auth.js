const passwordIcon = document.getElementById('password-icon');

passwordIcon.addEventListener('click', () => {
    const passwordInput = document.getElementById('password');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('la-eye');
        passwordIcon.classList.add('la-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('la-eye-slash');
        passwordIcon.classList.add('la-eye');
    }
});