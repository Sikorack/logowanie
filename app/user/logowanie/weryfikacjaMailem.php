<?php
require_once(__DIR__ . "/../../../db/connection.php");

session_start();
$komunikat = $_SESSION["komunikat2"] ?? null;

if (!isset($_SESSION["weryfikacjaMailemOpcja"])) {
    header("Location: logowanie.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wpisanyKod = htmlspecialchars($_POST['kod']) ?? null;

    if ($wpisanyKod == $_SESSION['logowanieKod']) {
        $_SESSION["CzyZalogowany"] = true;
        header("Location: ../../index.php");
        exit();
    } else {
        $komunikat = "Wprowadzony kod jest niepoprawny.";
    }
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="d-flex align-items-center py-5">
    <div class="con m-auto text-white justify-content-center">
        <h1 class="mb-3 text-center"><i class="bi bi-envelope"></i></h1>
        <h1 class="mb-3 fw-bold text-center">Sprawdź maila</h1>
        <p class="text-center">Przepisz 6-cyfrowy kod z maila<br>o numerze [<?php echo $_SESSION['idWiadomosci'] ?>]</p>
        <hr>
        <form action="" method="post">
            <p class="text-center text-danger"><?= $komunikat ?></p>
            <div class="form-floating mb-3">
                <input type="text" name="kod" class="form-control" id="floatingInput" placeholder="Wpisz kod" required>
                <label for="floatingInput" class="text-dark">Podaj kod</label>
            </div>

            <button class="btn btn-primary w-100 py-2 mb-3" type="submit">Zatwierdź</button>
            <p class="text-center"><span id="timer"></span></p>
            <p class="text-center"><span>Kod nie dotarł? <a href="../wysylanieMailiPonownie/mailPonownieLogowanie.php" class="link">Wyślij maila ponownie</a></span></p>
            <a href="wylogowanie.php" class="btn btn-secondary w-100 py-2 powrot">Powrót</a>
        </form>
    </div>
</body>
<script src="../skrypt.js"></script>
</html>