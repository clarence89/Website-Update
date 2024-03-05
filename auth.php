<?php
session_start();
if($_COOKIE['hrem'])
    {
    $_SESSION['iuid'] = $_COOKIE['hrem'];
    $_SESSION['ifname'] = base64_decode($_COOKIE['hrei']);
    $_SESSION['iupriv'] = base64_decode($_COOKIE['hrep']);
    $_SESSION['iremember'] = '1';
    }
    
if($_SESSION['iuid'] && $_SESSION['iremember'] == '1')
    {
    setcookie('hrem', $_SESSION['iuid'], time() + (3600 * 60), '/');
    setcookie('hrei', base64_encode($_SESSION['ifname']), time() + (3600 * 60), '/');
    setcookie('hrep', base64_encode($_SESSION['iupriv']), time() + (3600 * 60), '/');
    }
?>
