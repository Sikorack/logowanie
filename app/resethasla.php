<?php
require_once(__DIR__ . "/../db/connection.php");
require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

session_start();


$_SESSION['kodResetHasla'] = $_SESSION['kodResetHasla'] ?? null;
$_SESSION['drugiEtapResetu'] = $_SESSION['drugiEtapResetu'] ?? null;
$_SESSION['trzeciEtapResetu'] = $_SESSION['trzeciEtapResetu'] ?? null;

$komunikat = null;

if (isset($_POST['wyslij1'])) {
    $mail = htmlspecialchars($_POST['mail']) ?? null;
    $_SESSION['mail'] = $mail;
    
    $stmt = "SELECT * FROM users WHERE mail=?";
    $user = $db->execute_query($stmt, [$mail])->fetch_column();

    if (!empty($user)) {
        $_SESSION['mail'] = $mail ?? null;
        $_SESSION['tym2'] = true;

        $wygenerowanyKod = rand(100000, 999999);
        $_SESSION['kodResetHasla'] = $wygenerowanyKod;

        $idWiadomosci = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
        $idWiadomosci2 = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
        $_SESSION['idWiadomosci'] = $idWiadomosci."-".$idWiadomosci2;

        $transport = Transport::fromDsn('smtps://logowanie.prosze.wpusc.mnie@gazeta.pl:haslodokonta1234!@smtp.gazeta.pl:465');
        $mailer = new Mailer($transport);
        $email = (new Email())
        ->from('logowanie.prosze.wpusc.mnie@gazeta.pl')
        ->to($mail)
        ->subject('Logowanie [' . $_SESSION['idWiadomosci'] . ']')
        ->html('Dzień dobry, ' . $_SESSION['login'] . '<br><br>Otrzymaliśmy Twoją prośbę o jednorazowy kod, który ma zostać użyty w celu zmiany hasła. Jeśli to nie Ty, skontaktuj się z administratorem.<br><br>Oto twój kod weryfikacyjny: <b>' . htmlspecialchars($wygenerowanyKod) . '</b><br><br><img src="https://lh4.googleusercontent.com/proxy/D1c9vWAHuteTL7-G6NRJQPKOK5nyGlEv8UgqmsqO2rkILE_6HCfrnUUbHckyoPxEIm2vQRdB1h6t3dKixvtZT5iTc8x_lg"');
        $mailer->send($email);

        $_SESSION['drugiEtapResetu'] = true;
        $_SESSION['ostatnioWyslanyMail'] = time();
        header("Location: resethasla.php");
    } else $komunikat = "Użytkownik z podanym mailem nie istnieje.";
}

if (isset($_POST["inputDrugiEtapResetu"]) && $_SESSION['drugiEtapResetu']) {

    $wpisanyKod = htmlspecialchars($_POST['wpisanyKodResetHasla']) ?? null;

    if ($wpisanyKod == $_SESSION['kodResetHasla']) {
        $_SESSION['trzeciEtapResetu'] = true;
        $_SESSION['drugiEtapResetu'] = false;
        header("Location: resethasla.php");
    } else $komunikat = "Niepoprawny kod";
}

if (isset($_POST["inputTrzeciEtapResetu"]) && $_SESSION['trzeciEtapResetu']) {
    $haslo = htmlspecialchars($_POST['haslo']) ?? null;
    $haslo2 = htmlspecialchars($_POST['haslo2']) ?? null;

    if($haslo == $haslo2) {
        if(preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $haslo)){
            $hashHasla = password_hash($haslo.$salt, PASSWORD_BCRYPT);

            $stmt = "UPDATE users SET hash = ? WHERE mail = ?";
            $resetHasla = $db->execute_query($stmt, [$hashHasla, $_SESSION['mail']]);
    
            echo "<script type='text/javascript'>alert('Poprawnie zmieniono hasło. Proszę zalogować się teraz na swoje konto.');
                window.location.href = 'user/logowanie/wylogowanie.php';</script>";
        } else $komunikat = "Hasło musi mieć 8 znaków,<br>1 literę i 1 cyfrę.";
    } else $komunikat = "Hasła się nie zgadzają.";
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="walidacjaHasla.js"></script>

    <title>Strona główna</title>
    <link rel="stylesheet" href="style/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="d-flex align-items-center py-5">
    <div class="con m-auto text-white justify-content-center">
        <?php if (!$_SESSION['drugiEtapResetu'] && !$_SESSION['trzeciEtapResetu']): ?>
            <form action="" method="post">
            <h1 class="display-4 mb-3 fw-bold text-center">Resetowanie<br>hasła</h1>
            <hr>
                <p class="text-center"><?= $komunikat ?></p>
                <div class="form-floating mb-3">
                    <input type="text" name="mail" class="form-control" id="floatingInput" placeholder="Wpisz adres e-mail"
                        required>
                    <label for="floatingInput" class="text-dark">Podaj mail</label>
                </div>

                <input type="submit" class="btn btn-primary w-100 py-2 mb-3" value="Wyślij kod na e-mail" name="wyslij1">
                <a href="user/logowanie/wylogowanie.php" class="btn btn-secondary w-100 py-2 powrot"><i class="bi bi-arrow-return-left"></i> Powrót</a>
            </form>
        <?php endif; ?>

        <?php if ($_SESSION['drugiEtapResetu']): ?>
            <form action="" method="post">
            <h1 class="mb-3 text-center"><i class="bi bi-envelope"></i></h1>
            <h1 class="mb-3 fw-bold text-center">Sprawdź maila</h1>
            <p class="text-center">Przepisz 6-cyfrowy kod z maila<br>o numerze [<?php echo $_SESSION['idWiadomosci'] ?>]</p>
            <hr>
                <p class="text-center"><?= $komunikat ?></p>
                <div class="form-floating mb-3">
                    <input type="text" name="wpisanyKodResetHasla" class="form-control" id="floatingInput" placeholder="Wpisz kod"
                        required>
                    <label for="floatingInput" class="text-dark">Podaj kod</label>
                </div>

                <input type="submit" class="btn btn-primary w-100 py-2 mb-3" value="Zatwierdź" name="inputDrugiEtapResetu">
                <p class="text-center"><span id="timer"></span></p>
                <p class="text-center"><span>Kod nie dotarł? <a href="user/wysylanieMailiPonownie/mailPonownieZmianaHasla.php" class="link">Wyślij maila ponownie</a></span></p>
                <a href="user/logowanie/wylogowanie.php" class="btn btn-secondary w-100 py-2 powrot"><i class="bi bi-arrow-return-left"></i> Powrót</a>
            </form>
        <?php endif; ?>

        <?php if ($_SESSION['trzeciEtapResetu']): ?>
            <form action="" method="post">
            <h1 class="display-4 mb-3 fw-bold text-center">Resetowanie<br>hasła</h1>
                <hr>
                <p class="text-center  text-danger"><?= $komunikat ?></p>
                <div class="form-floating mb-3">
                    <input type="password" name="haslo" class="form-control" id="floatingPassword"
                        placeholder="Wpisz hasło" required>
                    <label for="floatingPassword" class="text-dark">Nowe hasło</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" name="haslo2" class="form-control" id="floatingPassword"
                        placeholder="Wpisz hasło" required>
                    <label for="floatingPassword" class="text-dark">Powtórz nowe hasło</label>
                </div>

                <input type="submit" class="btn btn-primary w-100 py-2 mb-3" value="Zmień hasło" name="inputTrzeciEtapResetu">
                <a href="user/logowanie/wylogowanie.php" class="btn btn-secondary w-100 py-2 powrot"><i class="bi bi-arrow-return-left"></i> Powrót</a>
            </form>
        <?php endif; ?>
    </div>
</body>
<script src="user/skrypt.js"></script>
</html>