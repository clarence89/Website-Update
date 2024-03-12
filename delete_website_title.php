<?php
session_start();
include("config.php");
include("auth.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$_SESSION['iuid']) {
    header("location: index.php");
}

if (!isset($_GET['id'])) {
    echo "Website title ID not provided.";
    exit();
}

$id = $db->real_escape_string($_GET['id']);

$sql = "DELETE FROM website_title WHERE id='$id'";

if ($db->query($sql) === TRUE) {
    echo "Record deleted successfully";
    header('location: website_titles.php');
} else {
    echo "Error deleting record: " . $db->error;
}