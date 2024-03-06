<?php
        session_start();
        ob_start();
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        include("config.php");
        include("auth.php");

        if (!isset($_SESSION['iuid'])) {
            header("location: index.php");
            exit();
        }

        if (!isset($_GET['update_id'])) {
            echo "Update ID not provided.";
            exit();
        }

        $update_id = $db->real_escape_string($_GET['update_id']);

        $sql_logs = "SELECT wul.*, u.fname, u.lname FROM website_update_logs AS wul LEFT JOIN users AS u ON wul.requested_by = u.userid WHERE wul.website_update_id = '$update_id' ORDER BY wul.created_at DESC";
        $result_logs = $db->query($sql_logs);
        ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Website Update Logs</title>
    <meta name="description" content="Website Update Logs">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Lato.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-white portfolio-navbar gradient">
        <div class="container">
            <img src="assets/img/mmwghlogo.png" width="50px" style="margin-right: 10px;">
            <a class="navbar-brand logo" href="#">IHOMS Website Update</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="website-lists.php">Lists</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-5">
        <h2 class="mt-5">Website Update Logs</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Requested By</th>
                    <th>Title</th>
                    <th>Source</th>
                    <th>Type of File</th>
                    <th>Type of Change</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Change Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_logs->num_rows > 0) {
                    while ($row = $result_logs->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "<td>" . $row['fname'] . " " . $row['lname'] . "</td>";
                        echo "<td>" . $row['title'] . "</td>";
                        echo "<td>" . $row['source'] . "</td>";
                        echo "<td>" . $row['type_of_file'] . "</td>";
                        echo "<td>" . $row['type_of_change'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['reason'] . "</td>";
                        echo "<td>" . $row['log_type'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No logs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>