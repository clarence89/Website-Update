<?php
session_start();
include("config.php");
include("auth.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['iuid'])) {
    header("location: index.php");
    exit();
}

// Insertion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title_name = $db->real_escape_string($_POST['title_name']);
    $sql = "INSERT INTO website_title (title_name) VALUES ('$title_name')";
    if ($db->query($sql) === TRUE) {
        echo "New record created successfully";
        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $db->error;
    }
}

// Fetching website titles
$sql = "SELECT * FROM website_title";
$result = $db->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Website Update - Data Entry & List</title>
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
                    <?php if ($_SESSION['iupriv'] != 3) { ?>
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
        <div class="row">
            <div class="col-md-6">
                <!-- Data entry form -->
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label" for="title_name">Title Name</label>
                        <input class="form-control item" type="text" id="title_name" name="title_name" required>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit">Insert</button>
                </form>
            </div>
            <div class="col-md-6">
                <!-- Website titles list -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['title_name'] . "</td>";
                                echo "<td>";
                                echo "<a href='edit_website_title.php?id=" . $row['id'] . "'>Update</a> | ";
                                echo "<a href='delete_website_title.php?id=" . $row['id'] . "'>Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>
