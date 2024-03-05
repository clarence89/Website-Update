<?php
error_reporting(0);
session_start();
date_default_timezone_set("Asia/Manila");
$dbhost = "localhost";
$dbname = "ihoms_inventory";
$dbuser = "root";
$dbpass = "";

$db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

?>
