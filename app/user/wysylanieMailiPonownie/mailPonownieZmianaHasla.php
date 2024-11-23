<?php
require_once(__DIR__ . "/../../../db/connection.php");
require_once(__DIR__ . '/../../../vendor/autoload.php');

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

session_start();

if($_SESSION['ostatnioWyslanyMail'] + 10 > time()){
    header("Location: ../../resethasla.php");
    exit;
}

if ($_SESSION['tym2']) {
    
    $mail = $_SESSION['mail'];

    $stmt = "SELECT mail FROM users WHERE mail=?";
    $mail = $db->execute_query($stmt, [$mail])->fetch_column();

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
    ->subject('Logowanie ['.$_SESSION['idWiadomosci'].']')
    ->html('Dzień dobry, ' . $mail .'<br><br>Otrzymaliśmy Twoją prośbę o jednorazowy kod, który ma zostać użyty w celu zmiany hasła. Jeśli to nie Ty, skontaktuj się z administratorem.<br><br>Oto twój kod weryfikacyjny: <b>' . htmlspecialchars($wygenerowanyKod) . '</b><br><br><img src="https://lh4.googleusercontent.com/proxy/D1c9vWAHuteTL7-G6NRJQPKOK5nyGlEv8UgqmsqO2rkILE_6HCfrnUUbHckyoPxEIm2vQRdB1h6t3dKixvtZT5iTc8x_lg"');
    $mailer->send($email);

    $_SESSION['weryfikacjaMailemOpcja'] = true;
    $_SESSION['ostatnioWyslanyMail'] = time();
    header("Location: ../../resethasla.php");
}else{
    header("Location: ../../user/logowanie/wylogowanie.php");
}
?>