<?php
require_once(__DIR__ . "/../../../db/connection.php");
session_start();
$_SESSION['czyUserMaKlucz'] = $_SESSION['czyUserMaKlucz'] ?? null;
if (!isset($_SESSION["CzyZalogowany"]) || !$_SESSION["CzyZalogowany"]) {
  header("Location: user/login.php");
  exit;
}


$visitorId = $_SESSION['visitorId'];

$stmtpobierzTokenZBazy = "SELECT token FROM tokens, fingerprint WHERE tokens.fp_id=fingerprint.id AND tokens.user_id = ? AND fingerprint.fingerprint=?";
$pobierzTokenZBazy = $db->execute_query($stmtpobierzTokenZBazy, [$_SESSION['userId'], $visitorId])->fetch_column();

$stmt = "SELECT ustawienieWeryfikacji FROM users WHERE id = ?";
$ustawienieWeryfikacji = $db->execute_query($stmt, [$_SESSION['userId']])->fetch_column();

$stmt = "SELECT tylkoKlucz FROM users WHERE id=?";
$tylkoKlucz = $db->execute_query($stmt, [$_SESSION['userId']])->fetch_column();

if (isset($_POST['wylaczWeryfikacjeOgolnie'])) {
  setcookie("user_token", "", -1, "/", "", false, true);
  
  $stmt = "UPDATE users SET ustawienieWeryfikacji = 0  WHERE id = ?";
  $db->execute_query($stmt, [$_SESSION["userId"]]);

  $stmt = "DELETE FROM ykfido WHERE user_id=?;";
  $db->execute_query($stmt, [$_SESSION['userId']]);

  $stmt = "DELETE FROM tokens WHERE user_id=?;";
  $db->execute_query($stmt, [$_SESSION['userId']]);

  $stmt1 = "DELETE FROM fingerprint WHERE user_id=?;";
  $db->execute_query($stmt1, [$_SESSION['userId']]);
  header("Location: prywatnosc.php");
}

if (isset($_POST['wlaczWeryfikacjeOgolnie'])) {
  $stmt = "UPDATE users SET ustawienieWeryfikacji = 1  WHERE id = ?";
  $db->execute_query($stmt, [$_SESSION["userId"]]);
  header("Location: prywatnosc.php");
}

if (isset($_POST['wlaczWeryfikacjeNaTymUrzadzeniu'])) {

  try {
    

    $expires = time() + (60 * 60 * 24 * 30); // 30 dni w sekundach
    $formatedExpires = date('Y-m-d H:i:s', $expires);
    $token = bin2hex(random_bytes(16));

    $stmt = "INSERT IGNORE INTO fingerprint (id,fingerprint, user_id, expires_at) VALUES(null,?,?,?)";
    $dodanieFingerprinta = $db->execute_query($stmt, [$visitorId, $_SESSION['userId'], $formatedExpires]);

    $stmt = "SELECT id FROM fingerprint WHERE fingerprint = ?";
    $fpId = $db->execute_query($stmt, [$visitorId])->fetch_column();

    $stmt = "INSERT INTO tokens (id, token, user_id, fp_id, expires_at) VALUES (null, ?, ?, ?, ?);";
    $updatesuccess = $db->execute_query($stmt, [$token, $_SESSION["userId"], $fpId, $formatedExpires]);

    setcookie("user_token", $token, $expires, "/", "", false, true);

    header("Location: prywatnosc.php");
  } catch (Exception $e) {
    echo $e->getMessage() . $db->error;
  }
}

if (isset($_POST['wylaczWeryfikacjeNaTymUrzadzeniu'])) {

  setcookie("user_token", "", -1, "/", "", false, true);

  $stmt = "DELETE FROM tokens WHERE user_id=?;";
  $db->execute_query($stmt, [$_SESSION['userId']]);

  $stmt1 = "DELETE FROM fingerprint WHERE user_id=?;";
  $db->execute_query($stmt1, [$_SESSION['userId']]);
  header("Location: prywatnosc.php");
}

if (isset($_POST['wlaczTylkoKlucz'])) {
  $stmt = "UPDATE users SET tylkoKlucz = 0 WHERE id=?";
  $success = $db->execute_query($stmt, [$_SESSION['userId']]);
  header("Location: prywatnosc.php");
}

if (isset($_POST['wylaczTylkoKlucz'])) {
  $stmt = "UPDATE users SET tylkoKlucz = 1 WHERE id=?";
  $db->execute_query($stmt, [$_SESSION['userId']]);
  header("Location: prywatnosc.php");
}

$login = $db->execute_query("SELECT login FROM users WHERE id=?", [$_SESSION["userId"]])->fetch_column();
$_SESSION['login'] = $login;

$nazwaKlucza = $db->execute_query("SELECT ykfido.nazwa, ykfido.credential_id FROM ykfido WHERE user_id=?", [$_SESSION["userId"]])->fetch_all(MYSQLI_ASSOC);
$db->close();
?>

<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="../../style/style.css">
  <title>Ustawienia prywatności</title>

  <script src="../../klucz/scriptt.js" defer></script>

</head>

<body class="d-flex align-items-center py-4">
  <div class="con m-auto text-white">
    
    <form action="" method="post">
      <input type="hidden" id="visitorId" name="visitorId">
      <script src="../../fingerprint/fp.js"></script>
    </form>

    <form action="" method="post">
      <a href="../../index.php" class="btn btn-secondary py-2 powrot"><svg xmlns="http://www.w3.org/2000/svg" width="16"
          height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5" />
        </svg> Powrót</a>
      <h1 class="h1 mb-3 fw-normal text-center">Ustawienia prywatności</h1>
      <hr>

      <div class="card mb-3">
        <h5 class="card-header">Weryfikacja dwuetapowa</h5>
        <div class="card-body">
          <?php if ($ustawienieWeryfikacji != 0): ?>
            <div class="alert alert-success" role="alert">
              Twoje konto wymaga weryfikacji dwuetapowej
            </div>
            <input type="submit" class="btn btn-primary py-2 w-100" value="Nie wymagaj" name="wylaczWeryfikacjeOgolnie">
          <?php else: ?>
            <div class="alert alert-danger" role="alert">
              Twoje konto nie wymaga weryfikacji dwuetapowej
            </div>
            <input type="submit" class="btn btn-primary py-2 w-100" value="Wymagaj" name="wlaczWeryfikacjeOgolnie">
          <?php endif; ?>
        </div>
      </div>
      <?php if ($ustawienieWeryfikacji == 1): ?>


        <div class="card mb-3">
          <h5 class="card-header">Weryfikacja dwuetapowa<br>na tym urządzeniu</h5>
          <div class="card-body">
            <form action="" method="post">

              <?php if (empty($pobierzTokenZBazy)): ?>
                <div class="alert alert-success" role="alert">
                  Twoje konto wymaga weryfikacji dwuetapowej<br>na tym urządzeniu przy następnym logowaniu
                </div>

                <input type="hidden" id="visitorId" name="visitorId">
                <script src="../../fingerprint/fp.js"></script>

                <input type="submit" class="btn btn-primary py-2 w-100" value="Nie wymagaj"
                  name="wlaczWeryfikacjeNaTymUrzadzeniu">
              <?php else: ?>
                <div class="alert alert-danger" role="alert">
                  Twoje konto nie wymaga weryfikacji dwuetapowej<br>na tym urządzeniu przy następnym logowaniu
                </div>

                

                <input type="submit" class="btn btn-primary py-2 w-100" value="Wymagaj"
                  name="wylaczWeryfikacjeNaTymUrzadzeniu">
              <?php endif; ?>

            </form>
          </div>
        </div>


        <?php if (!empty($nazwaKlucza)): ?>

          <div class="card mb-3">
            <h5 class="card-header">Weryfikacji wyłącznie kluczem YubiKey</h5>
            <div class="card-body">
              <form action="" method="post">
                <?php if ($tylkoKlucz == 1): ?>



                  <div class="alert alert-success" role="alert">
                    Twoje konto wymaga weryfikacji tylko kluczem.<br>Włączenie tej opcji spowoduje możliwość<br>weryfikacji
                    mailem.
                  </div>

                  <input type="submit" class="btn btn-primary py-2 w-100" value="Nie wymagaj" name="wlaczTylkoKlucz">

                <?php else: ?>
                  <div class="alert alert-danger" role="alert">
                    Twoje konto nie wymaga weryfikacji tylko kluczem.<br>Wyłączenie tej opcji spowoduje brak
                    możliwości<br>weryfikacji mailem.
                  </div>

                  <input type="submit" class="btn btn-primary py-2 w-100" value="Wymagaj" name="wylaczTylkoKlucz">

                <?php endif; ?>
              </form>
            </div>
          </div>

        <?php endif; ?>

        <div class="card mb-3">
          <h5 class="card-header">Twoje klucze uwierzytelniające</h5>
          <div class="card-body">
            <?php if (!empty($nazwaKlucza)): ?>

              <h1 class="h5 mb-3 fw-normal text-center">Twoje klucze:</h1>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nazwa klucza</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($nazwaKlucza as $index => $row): ?>

                    <tr>
                      <th scope='row'><?= $index + 1 ?></th>
                      <td><?= empty($row["nazwa"]) ? "Bez nazwy" : $row["nazwa"] ?></td>
                      <td>
                        <button type="button" class="btn btn-primary h-auto py-2"
                          onclick="zmienNazwe('<?= $row['credential_id'] ?>')">Zmień nazwę</button>
                        <button type="button" class="btn btn-primary h-auto py-2"
                          onclick="usunKlucz('<?= $row['credential_id'] ?>')">Usuń klucz</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="alert alert-danger" role="alert">Nie masz dodanych kluczy uwierzytelniających.</div>
            <?php endif; ?>

            <button type="button" class="btn btn-primary py-2 w-100" onclick="createRegistration()">Dodaj nowy klucz
              YubiKey</button>

            <input type="hidden" value=<?= $login ?> id="userName">

          </div>

      </form>
    <?php endif; ?>
  </div>

</body>

</html>