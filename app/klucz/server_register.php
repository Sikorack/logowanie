<?php
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . "/../../db/connection.php");
require_once(__DIR__ . '/../../vendor/lbuchs/webauthn/src/WebAuthn.php');

session_start();

$formats = ['packed', 'fido-u2f', 'none'];
$WebAuthn = new lbuchs\WebAuthn\WebAuthn('2p-info', $rpId, $formats);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $userName = $_GET['userName'] ?? null;
    if (!empty($userName)) {
        $stmt = "SELECT id FROM users WHERE login=?;";
        $userId = $db->execute_query($stmt, [$userName])->fetch_column();
        $stmt2 = "SELECT credential_id FROM ykfido";
        $result = $db->execute_query($stmt2)->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $ids = [];
        foreach ($result as $row) {
            $ids[] = base64_decode($row['credential_id']);
        }

        $createArgs = $WebAuthn->getCreateArgs($userId, $userName, $userName, 60*5, true, false, true, $ids);
        $_SESSION['challenge'] = $WebAuthn->getChallenge();
        $_SESSION['userId'] = $userId;

        header('Content-Type: application/json; charset=utf-8');
        print(json_encode($createArgs));
        exit;
    } else {
        header('Location: ../user/register.php');
        exit;
    }

} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $post = trim(file_get_contents('php://input'));
        if ($post) {
            $post = json_decode($post, null, 512, JSON_THROW_ON_ERROR);
        }
        $clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : null;
        $attestationObject = !empty($post->attestationObject) ? base64_decode($post->attestationObject) : null;
        $challenge = $_SESSION['challenge'] ?? null;

        $data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, false, false, false, false);
        $userId = $_SESSION['userId'] ?? null;
        $credentialId = base64_encode($data->credentialId);
        $publicKey = $data->credentialPublicKey;
        $signCounter = !empty($data->signatureCounter) ? $data->signatureCounter : 0;
        $stmt3 = "INSERT INTO ykfido (credential_id, user_id, publicKey, sign_count) VALUES (?, ?, ?, ?);";
        $db->execute_query($stmt3, [$credentialId, $userId, $publicKey, $signCounter]);
        $db->close();
        $msg = 'Poprawnie dodałeś klucz YubiKey. Zaloguj się ponownie na swoje konto.';
        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;

        header('Content-Type: application/json; charset=utf-8');
        print(json_encode($return));
        session_destroy();
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}
