document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('txtpassword');
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.classList.toggle('fa-eye-slash');
    });
});
