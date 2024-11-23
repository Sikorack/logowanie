<?php
require_once(__DIR__ . "/config.php");

try {
    $db = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
} catch (Exception $e) {
    exit($e->getMessage());
}

$salt = $config['salt'];