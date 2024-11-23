<?php
require_once(__DIR__ . "/../../../db/connection.php");
require_once(__DIR__ . '/../../../vendor/autoload.php');

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

session_start();

if($_SESSION['ostatnioWyslanyMail'] + 10 > time()){
    header("Location: ../../user/rejestracja/rejestracja.php");
    exit;
}

if ($_SESSION['tym']) {
    $wygenerowanyKod = rand(100000, 999999);
    $_SESSION['kodd'] = $wygenerowanyKod;

    $idWiadomosci = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
    $idWiadomosci2 = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
    $_SESSION['idWiadomosci'] = $idWiadomosci . "-" . $idWiadomosci2;

    $transport = Transport::fromDsn('smtps://logowanie.prosze.wpusc.mnie@gazeta.pl:haslodokonta1234!@smtp.gazeta.pl:465');
    $mailer = new Mailer($transport);
    $email = (new Email())
        ->from('logowanie.prosze.wpusc.mnie@gazeta.pl')
        ->to($_SESSION['email'])
        ->subject('Rejestracja konta [' . $_SESSION['idWiadomosci'] . ']')
        ->html('Dzień dobry, ' . $_SESSION['login'] . '<br><br>Otrzymaliśmy Twoją prośbę o jednorazowy kod, który ma zostać użyty w celu rejestracji konta. Jeśli to nie Ty, skontaktuj się z administratorem.<br><br>Oto twój kod weryfikacyjny: <b>' . htmlspecialchars($wygenerowanyKod) . '</b><br><br><img src="https://lh4.googleusercontent.com/proxy/D1c9vWAHuteTL7-G6NRJQPKOK5nyGlEv8UgqmsqO2rkILE_6HCfrnUUbHckyoPxEIm2vQRdB1h6t3dKixvtZT5iTc8x_lg"');
    $mailer->send($email);

    $_SESSION['ostatnioWyslanyMail'] = time();
    $_SESSION['drugiEtapWeryfikacji'] = true;

    header("Location: ../../user/rejestracja/rejestracja.php");
}else{
    header("Location: ../../user/rejestracja/powrot.php");
}
?>