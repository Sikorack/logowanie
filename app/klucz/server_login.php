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
        $stmt2 = "SELECT credential_id FROM ykfido WHERE user_id = ?;";
        $result = $db->execute_query($stmt2, [$userId])->fetch_all(MYSQLI_ASSOC);
        $db->close();
        
        $ids = [];
        foreach ($result as $row) {
            $ids[] = base64_decode($row['credential_id']);
        }

        $getArgs = $WebAuthn->getGetArgs($ids, 60 * 5, true, false, false, false, false, true);

        header('Content-Type: application/json');
        print(json_encode($getArgs));

        $_SESSION['challenge'] = $WebAuthn->getChallenge();
        $_SESSION['userId'] = $userId;
        exit;
    } else {
        header('Location: ../user/login.php');
        exit;
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $post = trim(file_get_contents('php://input'));
        if ($post) {
            $post = json_decode($post, null, 512, JSON_THROW_ON_ERROR);
        }

        $clientDataJSON = base64_decode($post->clientDataJSON);
        $authenticatorData = base64_decode($post->authenticatorData);
        $signature = base64_decode($post->signature);
        $id = $post->id;
        $challenge = $_SESSION['challenge'] ?? null;
        $stmt = "SELECT publicKey, sign_count FROM ykfido WHERE credential_id = ?;";
        $credential = $db->execute_query($stmt, [$id])->fetch_assoc();

        $WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credential['publicKey'], $challenge, $credential['sign_count'], true, true);

        $return = new stdClass();
        $return->success = true;

        $stmt2 = "UPDATE ykfido SET sign_count=sign_count+1 WHERE credential_id=?;";
        $db->execute_query($stmt2, [$id]);
        $db->close();

        $_SESSION['CzyZalogowany'] = true;


        header('Content-Type: application/json');
        print(json_encode($return));
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
