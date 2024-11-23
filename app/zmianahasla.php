<?php
require_once(__DIR__ . "/../db/connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $starehaslo = htmlspecialchars($_POST['starehaslo']) ?? null;
    $nowehaslo = htmlspecialchars($_POST['nowehaslo']) ?? null;
    $nowehaslo2 = htmlspecialchars($_POST['nowehaslo2']) ?? null;

    if (!empty($starehaslo) && !empty($nowehaslo) && !empty($nowehaslo2)) {
        $stmt = "SELECT * FROM users WHERE login=?";
        $user = $db->execute_query($stmt, [$_SESSION['login']])->fetch_assoc();
        if (password_verify($starehaslo . $salt, $user['hash'])) {
            if ($nowehaslo == $nowehaslo2) {
                if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $nowehaslo)) {
                    $hash = password_hash($nowehaslo . $salt, PASSWORD_BCRYPT);
                    $stmt = "UPDATE users SET hash = ? WHERE login = ?";
                    $zmianaHasla = $db->execute_query($stmt, [$hash, $_SESSION['login']]);
                    echo "<script type='text/javascript'>alert('Poprawnie zmieniono hasło. Proszę zalogować się teraz na swoje konto.');
                        window.location.href = 'user/logowanie/wylogowanie.php';</script>";
                    exit;
                } else $komunikat = "Hasło musi mieć 8 znaków, 1 literę i 1 cyfrę.";
            } else
                $komunikat = "Hasła się nie zgadzają!";
        } else
            $komunikat = "Stare hasło się nie zgadza!";
    } else
        $komunikat = "Wprowadź wszystkie dane!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zmiana hasła</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style/style.css">
</head>

<body class="d-flex align-items-center py-4">
    <div class="con m-auto text-white">
        <a href="index.php" class="btn btn-secondary py-2 powrot"><svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5" />
            </svg> Powrót</a>
        <form action="" method="post">
            <h1 class="mb-3 fw-bold">Zmiana hasła</h1>
            <p class="text-danger text-center"><?= $komunikat ?></p>
            <hr>
            <div class="form-floating mb-3">
                <input type="text" name="starehaslo" class="form-control" id="floatingInput" placeholder="" required>
                <label for="floatingInput" class="text-dark">Stare hasło</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="nowehaslo" class="form-control" id="floatingPassword"
                    placeholder="Wpisz hasło" required>
                <label for="floatingPassword" class="text-dark">Nowe hasło</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="nowehaslo2" class="form-control" id="floatingPassword"
                    placeholder="Wpisz hasło" required>
                <label for="floatingPassword" class="text-dark">Powtórz nowe hasło</label>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">Zmień hasło</button>
        </form>
    </div>
</body>

</html>