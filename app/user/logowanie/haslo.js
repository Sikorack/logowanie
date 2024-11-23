document.addEventListener("DOMContentLoaded", function () {

    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("floatingPassword");
    const passwordIcon = document.getElementById("passwordIcon");

    togglePassword.addEventListener("click", function () {
        const currentType = passwordField.type;
        
        if (currentType === "password") {
            passwordField.type = "text";
            passwordIcon.classList.remove("bi-eye");
            passwordIcon.classList.add("bi-eye-slash");
        } else {
            passwordField.type = "password";
            passwordIcon.classList.remove("bi-eye-slash");
            passwordIcon.classList.add("bi-eye");
        }
    });

});