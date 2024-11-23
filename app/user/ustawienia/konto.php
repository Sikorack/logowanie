<?php
require_once(__DIR__ . "/../../../db/connection.php");
session_start();



if (!isset($_SESSION["CzyZalogowany"]) || !$_SESSION["CzyZalogowany"]) {
    header("Location: user/login.php");
    exit;
}

if (isset($_POST['zmianaHasla'])) {
    $_SESSION['zmianaHasla'] = true;
    header("Location: ../../zmianahasla.php");
}

if (isset($_POST['usunKonto'])) {
    setcookie("user_token", "", -1, "/", "", false, true);


    $stmt = "DELETE FROM ykfido WHERE user_id=?;";
    $db->execute_query($stmt, [$_SESSION['userId']]);

    $stmt = "DELETE FROM tokens WHERE user_id=?;";
    $db->execute_query($stmt, [$_SESSION['userId']]);

    $stmt1 = "DELETE FROM fingerprint WHERE user_id=?;";
    $db->execute_query($stmt1, [$_SESSION['userId']]);

    $stmt = "DELETE FROM users WHERE id=?;";
    $db->execute_query($stmt, [$_SESSION["userId"]]);

    echo "<script type='text/javascript'>alert('Poprawnie usunięto konto.');
            window.location.href = '../logowanie/wylogowanie.php';</script>";
    exit;
}

$stmt = "SELECT login, mail FROM users WHERE id=?";
$dane = $db->execute_query($stmt, [$_SESSION['userId']])->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="klucz/scriptt.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Ustawienia konta</title>
</head>

<body class="d-flex align-items-center py-4">
    <div class="con m-auto text-white">
        <a href="../../index.php" class="btn btn-secondary py-2 powrot"><svg xmlns="http://www.w3.org/2000/svg"
                width="16" height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5" />
            </svg> Powrót</a>
        <h1 class="h1 mb-3 fw-normal text-center">Ustawienia konta</h1>
        <hr>

        <div>
            <div class="row d-flex align-items-center justify-content-center">
                <div class="col-auto">

                    <div class="card h-100 mb-3">
                        <h5 class="card-header">Twoje dane</h5>

                        <div class="card-body">
                            <div class="text-center">
                                <i class="bi bi-person-circle" style="font-size:64px"></i>
                            </div>
                            <p>Login: <?php echo $dane['login'] ?></p>
                            <p>E-mail: <?php echo $dane['mail'] ?></p>

                            <form action="" method="post">
                                <input type="submit" class="btn btn-danger w-100 py-2" value="Usuń konto"
                                    name="usunKonto">
                            </form>
                        </div>
                    </div>


                    <form action="" method="post">
                        <div class="card">
                            <h5 class="card-header">Zmiana hasła</h5>
                            <div class="card-body">
                                <input type="submit" class="btn btn-primary w-100 py-2" value="Zmień hasło"
                                    name="zmianaHasla">
                            </div>
                        </div>
                    </form>
                </div>                
            </div>
        </div>
    </div>

    </div>
</body>

</html>