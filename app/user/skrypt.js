
document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.link');
    const timerElement = document.getElementById('timer');
    let timeLeft = 10;

    const disableLinks = () => {
        links.forEach(link => {
            link.classList.add('disabled-link'); // Dodaj klasę stylów
            link.addEventListener('click', preventDefaultAction);
        });
    };

    const enableLinks = () => {
        links.forEach(link => {
            link.classList.remove('disabled-link');
            link.removeEventListener('click', preventDefaultAction);
        });
    };

    const preventDefaultAction = (event) => {
        event.preventDefault();
        alert("Link jest tymczasowo zablokowany.");
    };

    const updateTimer = () => {
        timerElement.textContent = `Pozostały czas: ${timeLeft} sekund`;
    };

    disableLinks();
    updateTimer();

    const countdown = setInterval(() => {
        timeLeft--;
        updateTimer();

        if (timeLeft <= 0) {
            clearInterval(countdown);
            enableLinks();
            timerElement.textContent = "";
        }
    }, 1000);
});