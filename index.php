<?php
include("config.php");
ob_start();
include("auth.php");
if ($_SESSION['iuid']) {
    if ($_SESSION['iupriv'] == 1)
        header("location: dashboard.php");
    elseif ($_SESSION['iupriv'] == 0)
        header("location: dashboard.php");
    else
        header("location: dashboard.php");
    ob_end_flush();;
}
if ($_POST['username'] && $_POST['password']) {
    if ($_SESSION['logctr'] > 10)
        $errmsg = "Maximum login attempts exceeded";
    else {
        $username = $db->real_escape_string($_POST['username']);
        $password = $db->real_escape_string($_POST['password']);

        $result = $db->query("SELECT * FROM users WHERE username='$username'");
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['userpass'])) {
                $_SESSION['iuid'] = $row['userid'];
                $_SESSION['ifname'] = $row['fname'];
                $_SESSION['iupriv'] = $row['userprivilege'];
                if ($_POST['chkremember'] == '1') {
                    $_SESSION['iremember'] = 1;
                    setcookie('hrem', $_SESSION['iuid'], time() + (3600 * 60), '/');
                    setcookie('hrei', base64_encode($_SESSION['ifname']), time() + (3600 * 60), '/');
                    setcookie('hrep', base64_encode($_SESSION['iupriv']), time() + (3600 * 60), '/');
                }
                header("location: index.php");
                ob_end_flush();;
            } else {
                $errmsg = "Incorrect password";
            }
        } elseif ($row['userstatus'] != 0) {
            $errmsg = "User is not allowed to log in";
        } else {
            if (!$_SESSION['logctr'])
                $_SESSION['logctr'] = 0;
            $_SESSION['logctr'] = $_SESSION['logctr'] + 1;
            if ($_SESSION['logctr'] > 10)
                $errmsg = "Maximum login attempts exceeded";
            $errmsg = "Incorrect username";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>IMIS Website Update - Login</title>
    <meta name="description" content="IMIS Website Update">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Lato.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/pikaday.min.css">
    <script src="assets/js/bootstrap-theme.js"></script>

</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-white portfolio-navbar gradient">
        <div class="container"><img src="assets/img/mmwghlogo.png" width="50px" style="margin-right: 10px;"><a class="navbar-brand logo" href="#">IMIS Website Update</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="page contact-page">
        <section class="portfolio-block contact">
            <div class="container">
                <div class="heading" style="margin-bottom: 50px;">
                    <h2>IMIS Website Update LOGIN</h2>
                    <?php
                    if ($errmsg)
                        echo '<p style="color: rgb(255,0,0);margin-top: 20px;font-size: 18px;font-weight: bold;">' . $errmsg . '</p>';
                    ?>
                </div>
                <form method="post">
                    <div class="mb-3"><label class="form-label" for="name">Username</label><input class="form-control item" type="text" id="name" autofocus="" name="username"></div>
                    <div class="mb-3"><label class="form-label" for="subject">Password</label><input class="form-control item" type="password" id="subject" name="password"></div>
                    <div class="mb-3">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="formCheck-1" name="chkremember" value="1" checked><label class="form-check-label" for="formCheck-1">Remember Me</label></div>
                    </div>
                    <div class="mb-3" style="padding-top: 15px;"><button class="btn btn-primary btn-lg d-block w-100" type="submit">Login</button></div>
                    <div class="text-center mb-3" style="padding-top: 15px;"><a href="forgotpassword">Forgot Password</a></div>
                </form>
            </div>
        </section>
    </main>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="assets/js/theme.js"></script>

</body>

</html>
