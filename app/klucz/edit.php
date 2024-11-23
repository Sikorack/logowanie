<?php
require_once(__DIR__."/../../db/connection.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $post = trim(file_get_contents("php://input"));

    $post = json_decode($post);

    $kluczName = $post->nowaNazwa;
    $kluczName = htmlspecialchars($kluczName);
    $credentialId = $post->credentialId;

    $stmt = "UPDATE ykfido SET nazwa=? WHERE credential_id = ?";
    $success = $db ->execute_query($stmt, [$kluczName, $credentialId]);
    $db->close();

    $return = new stdClass();
    if( $success ) {
        $return->success = true;
    } 
    header("Content-Type: application/json");
    print(json_encode($return));
    exit;
}