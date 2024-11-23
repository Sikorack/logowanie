<?php
require_once(__DIR__ . "/../db/connection.php");
session_start();

//sprawdzamy czy użytkownik wcześniej się zalogował - jeśli nie to odsyłamy go do logowania
if (!isset($_SESSION["CzyZalogowany"]) || !$_SESSION["CzyZalogowany"]) {
  header("Location: user/logowanie/logowanie.php");
  exit;
}

//pobieramy z bazy danych nazwę klucza, id klucza oraz login użytkownika
$userID = $_SESSION['userId'] ?? null;
$login = $db->execute_query("SELECT login FROM users WHERE id=?", [$userID])->fetch_column();
$_SESSION['login'] = $login;
$nazwaKlucza = $db->execute_query("SELECT ykfido.nazwa, ykfido.credential_id FROM ykfido WHERE user_id=?", [$userID])->fetch_all(MYSQLI_ASSOC);
$db->close();




if($_SERVER['REQUEST_METHOD'] === "POST"){
  $_SESSION['visitorId'] = $_POST['visitorId'];
  header("Location: user/ustawienia/prywatnosc.php");
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

  <title>Strona główna</title>
  <link rel="stylesheet" href="style/style.css">

</head>

<body class="d-flex w-100 align-items-center">
  <div class="con m-auto text-white text-center">

    <h1 class="h1 fw-normal text-center">Cześć <?php echo htmlspecialchars(ucfirst($login)); ?> 👋</h1>
    <img src="https://lh5.googleusercontent.com/proxy/PSv0S3Ywtc1GPfq7fDDgHEiSaFtMOkTNenS0k8HxmKyD9Mpwhe-bI-RtQFuqof8QKrdSlDh03MPir2LMhYgp3C9DmJrw7Q" class="img-fluid mb-3" style="max-height:200px">
    
    <div class="d-flex justify-content-center">
      <div class="dropdown">

        <button class="btn dropdown-toggle py-2 text-white" type="button" data-bs-toggle="dropdown"
          aria-expanded="false"><i class="bi bi-gear"></i> Ustawienia</button>

        <ul class="dropdown-menu bg-dark">

          <li>
            <form action="" method="post">
              <input type="hidden" id="visitorId" name="visitorId">
              <script src="fingerprint/fp.js"></script>
              <button type="submit" class = "dropdown-item text-white bg-dark"><i class="bi bi-shield-lock"></i> Bezpieczeństwo</button>
            </form>
          </li>

          <li>
            <a class="dropdown-item text-white bg-dark" href="user/ustawienia/konto.php">
              <i class="bi bi-person-circle"></i>
              Konto</a>
          </li>

          <li>
            <a class="dropdown-item text-danger bg-dark" href="user/logowanie/wylogowanie.php">
              <i class="bi bi-box-arrow-left"></i>
              Wyloguj się</a>
          </li>

        </ul>
      </div>

    </div>

</body>

</html>