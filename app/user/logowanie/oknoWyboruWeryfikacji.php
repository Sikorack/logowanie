<?php
require_once(__DIR__ . "/../../../db/connection.php");
require_once(__DIR__ . '/../../../vendor/autoload.php');

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

session_start();

//jeśli użytkownik nie przeszedł pierwszego etapu weryfikacji tj. login i hasło to nie ma wstępu do tego pliku
if ($_SESSION['etapWeryfikacjaDwuetapowa'] == false) {
    header("Location: user/login.php");
    exit;
}

$stmt = "SELECT tylkoKlucz FROM users WHERE id=?";
$tylkoKlucz = $db->execute_query($stmt, [$_SESSION['userId']])->fetch_column();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $_SESSION['tym1'] = true;
    
    $id = $_SESSION['userId'];

    $stmt = "SELECT mail FROM users WHERE id=?";
    $mail = $db->execute_query($stmt, [$id])->fetch_column();

    $wygenerowanyKod = rand(100000, 999999);
    $_SESSION['logowanieKod'] = $wygenerowanyKod;

    $idWiadomosci = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
    $idWiadomosci2 = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
    $_SESSION['idWiadomosci'] = $idWiadomosci."-".$idWiadomosci2;

    $transport = Transport::fromDsn('smtps://logowanie.prosze.wpusc.mnie@gazeta.pl:haslodokonta1234!@smtp.gazeta.pl:465');
    $mailer = new Mailer($transport);

    $email = (new Email())
    ->from('logowanie.prosze.wpusc.mnie@gazeta.pl')
    ->to($mail)
    ->subject('Logowanie ['.$_SESSION['idWiadomosci'].']')
    ->html('Dzień dobry, ' . $_SESSION['login'] .'<br><br>Otrzymaliśmy Twoją prośbę o jednorazowy kod, który ma zostać użyty w celu zalogowania się. Jeśli to nie Ty, skontaktuj się z administratorem.<br><br>Oto twój kod weryfikacyjny: <b>' . htmlspecialchars($wygenerowanyKod) . '</b><br><br><img src="https://lh4.googleusercontent.com/proxy/D1c9vWAHuteTL7-G6NRJQPKOK5nyGlEv8UgqmsqO2rkILE_6HCfrnUUbHckyoPxEIm2vQRdB1h6t3dKixvtZT5iTc8x_lg"');
    $mailer->send($email);
    
    $_SESSION['ostatnioWyslanyMail'] = time();

    $_SESSION['weryfikacjaMailemOpcja'] = true;
    header("Location: weryfikacjaMailem.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weryfikacja dwuetapowa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="../../style/style.css">
    <script src="../../klucz/scriptt.js" defer></script>

</head>

<body class="d-flex align-items-center py-5">
    <div class="con m-auto text-white justify-content-center">

        <h1 class="display-4 mb-3 fw-bold text-center">Weryfikacja<br>dwuetapowa</h1>
        <hr>

        <?php if ($tylkoKlucz == 0): ?>
            <form action="" method="post">
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Wyślij kod na e-mail</button>
            </form>
        <?php endif; ?>

        <?php if ($_SESSION['czyUserMaKlucz']): ?>
            <button type="button" class="btn btn-primary w-100 py-2 mb-3" onclick="checkRegistration()">Uwierzytelnij YubiKey</button>
            <input type="hidden" value=<?= $_SESSION['login'] ?> id="userName">
        <?php endif; ?>

        <a href="wylogowanie.php" class="btn btn-secondary w-100 py-2 powrot">Powrót</a>
    </div>
</body>

</html>