<?php
require_once(__DIR__."/../../db/connection.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $post = trim(file_get_contents("php://input"));

    $post = json_decode($post);
    $credentialId = $post->credentialId;

    $stmt = "DELETE FROM ykfido WHERE credential_id=?";
    $success = $db ->execute_query($stmt, [$credentialId]);

    $stmt = "UPDATE users SET tylkoKlucz = 0 WHERE id=?";
    $db ->execute_query($stmt, [$_SESSION['userId']]);

    $db->close();

    $return = new stdClass();
    if( $success ) {
        $return->success = true;
    } 
    header("Content-Type: application/json");
    print(json_encode($return));
    exit;
}