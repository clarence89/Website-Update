<?php
session_start();
ob_start();
include("config.php");
include("auth.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$_SESSION['iuid']) {
    header("location: index.php");
    ob_end_flush();;
}

if (!isset($_GET['id'])) {
    echo "Website title ID not provided.";
    ob_end_flush();;
}

$id = $db->real_escape_string($_GET['id']);

    $sql = "UPDATE website_title SET status=1 WHERE id='$id'";
    if ($db->query($sql) === TRUE) {
    echo "Record updated successfully";
    header('location: website_titles.php');
    ob_end_flush();;
    } else {
        echo "Error updating record: " . $db->error;
    }
