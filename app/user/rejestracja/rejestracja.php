<?php
require_once(__DIR__ . "/../../../db/connection.php");
require_once(__DIR__ . '/../../../vendor/autoload.php');

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

session_start();

$_SESSION['drugiEtapWeryfikacji'] = $_SESSION['drugiEtapWeryfikacji'] ?? null;
$komunikat = null;
$komunikat2 = null;

//pierwszy etap rejestracji - wpisanie wszystkich danych oraz wysłanie maila w celu weryfikacji użytkownika
if (isset($_POST['rejestracja'])) {

    $login = htmlspecialchars(trim($_POST['login'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');
    $mail = htmlspecialchars(trim($_POST['email'] ?? ''));

    $stmt = "SELECT login FROM users WHERE login=?";
    $loginused = $db->execute_query($stmt, [$login])->fetch_assoc();

    $stmt = "SELECT mail FROM users WHERE mail=?";
    $mailused = $db->execute_query($stmt, [$mail])->fetch_assoc();
    if(preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $login)){
        if (!empty($mail)) {
            if (!empty($login) && !empty($password) && !empty($password2)) {
                if (empty($loginused['login']) && empty($mailused['mail'])) {
                    if ($password == $password2) {
                        if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
                            $_SESSION['tym'] = true;
                            $wygenerowanyKod = rand(100000, 999999);
                            $_SESSION['kodd'] = $wygenerowanyKod;

                            $idWiadomosci = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
                            $idWiadomosci2 = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
                            $_SESSION['idWiadomosci'] = $idWiadomosci."-".$idWiadomosci2;

                            $userId = bin2hex(random_bytes(16));
                            $password .= $salt;
                            $hash = password_hash($password, PASSWORD_BCRYPT);

                            $_SESSION['userId'] = $userId;
                            $_SESSION['login'] = $login;
                            $_SESSION['hash'] = $hash;
                            $_SESSION['email'] = $mail;

                            //wysyłanie maila i przejście do strony, która zweryfikuje kod wysłany i kod wpisany
                            $transport = Transport::fromDsn('smtps://logowanie.prosze.wpusc.mnie@gazeta.pl:haslodokonta1234!@smtp.gazeta.pl:465');
                            $mailer = new Mailer($transport);
                            $email = (new Email())
                                ->from('logowanie.prosze.wpusc.mnie@gazeta.pl')
                                ->to($mail)
                                ->subject('Rejestracja konta ['.$_SESSION['idWiadomosci'].']')
                                ->html('Dzień dobry, ' . $login .'<br><br>Otrzymaliśmy Twoją prośbę o jednorazowy kod, który ma zostać użyty w celu rejestracji konta. Jeśli to nie Ty, skontaktuj się z administratorem.<br><br>Oto twój kod weryfikacyjny: <b>' . htmlspecialchars($wygenerowanyKod) . '</b><br><br><img src="https://lh4.googleusercontent.com/proxy/D1c9vWAHuteTL7-G6NRJQPKOK5nyGlEv8UgqmsqO2rkILE_6HCfrnUUbHckyoPxEIm2vQRdB1h6t3dKixvtZT5iTc8x_lg"');
                            $mailer->send($email);

                            $_SESSION['drugiEtapWeryfikacji'] = true;
                            $_SESSION['ostatnioWyslanyMail'] = time();
                            
                            header("Location: rejestracja.php");
                            echo 1;
                            exit;
                        } else $komunikat = "Hasło musi mieć 8 znaków, 1 literę i 1 cyfrę.";
                    } else $komunikat = "Hasła się nie zgadzają.";
                } else $komunikat = "Konto z takim loginem lub e-mailem istnieje.";
            } else $komunikat = "Wprowadź wszystkie dane";
        } else $komunikat = "Wprowadź poprawny mail";
    }else $komunikat = "Wprowadź poprawny login.";
}

//drugi etap weryfikacji: sprawdzenie czy użytkownik poprawnie zweryfikował maila i wpisanie go do bazy danych
if ($_SESSION['drugiEtapWeryfikacji'] && isset($_POST['drugiEtapWeryfikacji'])) {

    $wpisanyKod = filter_input(INPUT_POST, 'wpisanyKod', FILTER_SANITIZE_NUMBER_INT) ?? null;

    if (!empty($wpisanyKod)) {
        $login = $_SESSION['login'];
        $hash = $_SESSION['hash'];
        $userId = $_SESSION['userId'];
        $mail = $_SESSION['email'];

        if ($wpisanyKod == $_SESSION['kodd']) {
            try{
            $stmt = "INSERT INTO users (id, login, hash, mail, ustawienieWeryfikacji, tylkoKlucz) VALUES (?, ?, ?, ?, 0, 0);";
            $success = $db->execute_query($stmt, [$userId, $login, $hash, $mail]);
            $db->close();
            }catch(Exception $e){
                echo "". $e->getMessage();
                exit;
            }

            echo "<script type='text/javascript'>alert('Poprawnie zarejestrowano użytkownika. Proszę zalogować się teraz na swoje konto.');
            window.location.href = '../logowanie/wylogowanie.php';</script>";
            session_destroy();
        } else {
            $komunikat2 = "Wprowadzony kod jest niepoprawny.";
        }
    } else $komunikat2 = "Wprowadź 6-cyfrowy kod";
}
?>
<!DOCTYPE html>
<html lang="pl-PL">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja</title>
    <script src="../../klucz/scriptt.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../style/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="d-flex align-items-center py-5">
    <div class="con m-auto text-white justify-content-center">

        <?php if (!$_SESSION['drugiEtapWeryfikacji']): ?>

            <form action="" method="post">
                <h1 class="display-4 mb-3 fw-bold text-center">Rejestracja</h1>
                <p class="text-center">Utwórz konto na naszej stronie!</p>
                <hr>
                <p class="text-danger text-center"><?= $komunikat ?></p>
                <div class="form-floating mb-3">
                    <input type="text" name="login" class="form-control" id="floatingInput" placeholder="Wpisz login"
                        required>
                    <label for="floatingInput" class="text-dark">Login</label>
                </div>

                <div class="form-floating mb-3 text-wrap">
                    <input type="text" name="email" class="form-control" id="mail" placeholder="Wpisz e-mail"
                        required>
                    <label for="floatingInput" class="text-dark">E-mail</label>

                    <p id="emailFeedback" style="max-width: 20vw; color:red;"></p>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="password"
                        placeholder="Wpisz hasło">
                    <label for="floatingPassword" class="text-dark">Hasło</label>

                    <p id="passwordFeedback" style="max-width: 20vw; color:red;"></p>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" name="password2" class="form-control" id="password2"
                        placeholder="Wpisz hasło">
                    <label for="floatingPassword2" class="text-dark">Potwierdź hasło</label>

                    <p id="passwordFeedback2" style="max-width: 20vw; color:red;"></p>
                </div>

                <input type="submit" class="btn btn-primary w-100 py-2 mb-3" value="Zarejestruj się" name="rejestracja">
                <p class="text-center"><span>Masz już konto? <a href="../logowanie/wylogowanie.php">Zaloguj się</a></span></p>
            </form>
        <?php endif; ?>

        <?php if ($_SESSION['drugiEtapWeryfikacji']): ?>
            <h1 class="mb-3 text-center"><i class="bi bi-envelope"></i></h1>
            <h1 class="mb-3 fw-bold text-center">Sprawdź maila</h1>
            <p class="text-center">Przepisz 6-cyfrowy kod z maila<br>o numerze [<?php echo $_SESSION['idWiadomosci'] ?>]</p>
            <hr>
            <form action="" method="post">
                <p class="text-danger text-center"><?= $komunikat2 ?></p>
                <div class="form-floating mb-3">
                    <input type="text" name="wpisanyKod" class="form-control" id="floatingInput" placeholder="Wpisz kod"
                        required>
                    <label for="floatingInput" class="text-dark">Podaj kod</label>
                </div>

                <input type="submit" class="btn btn-primary w-100 py-2 mb-3" value="Zatwierdź" name="drugiEtapWeryfikacji">
                <p class="text-center"><span id="timer"></span></p>
                <p class="text-center"><span>Kod nie dotarł? <a href="../wysylanieMailiPonownie/mailPonownieRejestracja.php" class="link">Wyślij maila ponownie</a></span></p>
                <a href="powrot.php" class="btn btn-secondary w-100 py-2 powrot"><i class="bi bi-arrow-return-left"></i> Powrót</a>

            </form>
        <?php endif; ?>

    </div>
</body>
<script>
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
</script>
<script src="../skrypt.js"></script>
</html>