// Pobieranie elementów
const emailInput = document.getElementById('mail');
const emailFeedback = document.getElementById('emailFeedback');

const passwordInput = document.getElementById('password');
const passwordFeedback = document.getElementById('passwordFeedback');

const passwordInput2 = document.getElementById('password2');
const passwordFeedback2 = document.getElementById('passwordFeedback2');

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePassword(password) {
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/; // Minimum 8 znaków, 1 litera, 1 cyfra
    return passwordRegex.test(password);
}

emailInput.addEventListener('input', () => {
    if (validateEmail(emailInput.value)) {
        emailFeedback.textContent = "";
    } else {
        emailFeedback.textContent = "Niepoprawny format e-maila.";
    }
});

passwordInput.addEventListener('input', () => {
    if (validatePassword(passwordInput.value)) {
        passwordFeedback.textContent = "";
    } else {
        passwordFeedback.textContent = "Hasło musi mieć min. 8 znaków, 1 literę i 1 cyfrę";
    }
});

passwordInput2.addEventListener('input', () => {
    if (passwordInput2.value == passwordInput.value) {
        passwordFeedback2.textContent = "";
    } else {
        passwordFeedback2.textContent = "Hasła się nie zgadzają.";
    }
});