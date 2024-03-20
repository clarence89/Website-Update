<?php
session_start();
ob_start();
$_SESSION = array();
session_destroy();
setcookie('hrem', '', time() -1, '/');
setcookie('hrei', '', time() -1, '/');
setcookie('hrep', '', time() -1, '/');
header("location: index.php");
ob_end_flush();;
?>
