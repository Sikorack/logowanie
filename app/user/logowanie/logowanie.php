<?php
require_once(__DIR__ . "/../../../db/connection.php");

session_start();
$zalogowany = false;
$_SESSION['etapWeryfikacjaDwuetapowa'] = false;
$komunikat = null;
$_SESSION['komunikat'] = $_SESSION['komunikat'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $login = htmlspecialchars($_POST['login']) ?? null;
    $password = htmlspecialchars($_POST['password']) ?? null;

    $visitorId = $_POST['visitorId'];

    $stmt = "SELECT * FROM users WHERE login=?";
    $user = $db->execute_query($stmt, [$login])->fetch_assoc();

    if (!empty($user) && !empty($password) && !empty($login) && password_verify($password . $salt, $user['hash'])) {
        $_SESSION['userId'] = $user['id'];
        
        $zapytanie = "SELECT COUNT(*) FROM ykfido WHERE user_id = ?";
        $czyUserMaKlucz = $db->execute_query($zapytanie, [$user['id']])->fetch_column() > 0;
        $_SESSION['czyUserMaKlucz'] = $czyUserMaKlucz;
        
        //jeśli użytkownik ma wyłączoną weryfikację dwuetapową to po prostu go logujemy
        $stmt = "SELECT ustawienieWeryfikacji FROM users WHERE id=?";
        $ustawienieWeryfikacji = $db->execute_query($stmt, [$user['id']])->fetch_column();

        if ($ustawienieWeryfikacji == 0) {
            $_SESSION["CzyZalogowany"] = true;
            $_SESSION['ustawienieWeryfikacji'] = true;
            header("Location: ../../index.php");
            exit;
        }

        //jeśli weryfikację dwuetapową ma dodaną, ale chce, aby na zapamiętanym urządzeniu jej nie stosować to jej nie stosujemy
        if (isset($_COOKIE['user_token'])) {
            
            $stmt = "SELECT tokens.expires_at, tokens.user_id, fingerprint.fingerprint FROM tokens, fingerprint WHERE tokens.fp_id = fingerprint.id AND token=?;";
            $dbToken = $db->execute_query($stmt, [$_COOKIE['user_token']])->fetch_assoc();

            if (!empty($dbToken) && strtotime($dbToken["expires_at"]) > time() && $dbToken["user_id"] == $user["id"] && $dbToken['fingerprint'] == $visitorId) {
                $_SESSION["CzyZalogowany"] = true;

                header("Location: ../../index.php");
                exit();
            }
        }

        //jeśli weryfikacja dwuetapowa jest włączona to przechodzimy do niej
        $_SESSION['etapWeryfikacjaDwuetapowa'] = true;
        $_SESSION["userId"] = $user["id"];
        $_SESSION['login'] = $login;

        header("Location: oknoWyboruWeryfikacji.php");
    } else
        $komunikat = "Błędne dane logowania ";
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logowanie</title>
    <script src="../../klucz/scriptt.js" defer></script>
    <script src="haslo.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../style/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />

</head>

<body class="d-flex align-items-center py-5">
    <div class="con m-auto text-white justify-content-center">

        <form action="" method="post">
            <h1 class="display-4 mb-3 fw-bold text-center">Logowanie</h1>
            <hr>
            <p class="text-danger text-center"><?php echo $komunikat ?></p>
            <p class="text-center"><?php echo $_SESSION['komunikat'] ?></p>

            <div class="form-floating mb-3">
                <input type="text" name="login" autocomplete="off" class="form-control" id="floatingInput" placeholder="Wpisz login"
                    required>
                <label for="" class="text-dark">Login</label>
            </div>

            <div class="form-floating input-group">
                
                <input type="password" name="password" class="form-control" id="floatingPassword"
                    placeholder="Wpisz hasło" required>
                <label for="floatingPassword" class="text-dark">Hasło</label>
                
                <button type="button" id="togglePassword" class="btn border border-start-1 border-1 bg-white" tabindex="-1">
                        <i id="passwordIcon" class="bi bi-eye text-warning h4"></i>
                </button>

            </div>
            <p class="mt-1"><a href="../../resethasla.php">Zapomniałem hasła</a></p>

            <input type="hidden" id="visitorId" name="visitorId">
            <script src="../../fingerprint/fp.js"></script>

            <button class="btn btn-primary w-100 py-2 mb-3" type="submit">Zaloguj się</button>
        </form>

        <p class="text-center"><span>Nie masz jeszcze konta? <a href="../rejestracja/rejestracja.php">Zarejestruj się</a></span></p>
    </div>
</body>

</html>