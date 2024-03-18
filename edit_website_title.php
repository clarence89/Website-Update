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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title_name = $db->real_escape_string($_POST['title_name']);
    $sql = "UPDATE website_title SET title_name='$title_name' WHERE id='$id'";
    if ($db->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $db->error;
    }
}

$sql = "SELECT * FROM website_title WHERE id='$id'";
$result = $db->query($sql);
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Edit Website Title</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Lato.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/pikaday.min.css">
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-white portfolio-navbar gradient">
        <div class="container">
            <img src="assets/img/mmwghlogo.png" width="50px" style="margin-right: 10px;">
            <a class="navbar-brand logo" href="#">IMIS Website Update</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="website-lists.php">Lists</a>
                    </li>
<?php if($_SESSION['iupriv'] != 1){ ?>
<li class="nav-item">
                        <a class="nav-link" href="website_titles.php">Titles</a>
                    </li><?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="Logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mt-5 pt-5">
        <h2>Edit Website Title</h2>
        <form method="post">
            <div class="mb-3">
                <label class="form-label" for="title_name">Title Name</label>
                <input class="form-control item" type="text" id="title_name" name="title_name" value="<?php echo $row['title_name']; ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </main>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>
