<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("config.php");
include("auth.php");
if (!$_SESSION['iuid']) {
    header("location: index.php");
}

if (isset($_POST['submit_approval'])) {
    $update_id = $_POST['update_id'];
    $reason = $db->real_escape_string($_POST['reason']);
    $sql = "UPDATE website_update SET status=2, reason='$reason' WHERE website_update_id='$update_id'";
    $db->query($sql);
} else if (isset($_POST['submit_for_process'])) {
    $update_id = $_POST['update_id'];
    $reason = $db->real_escape_string($_POST['reason']);
    $sql = "UPDATE website_update SET status=1, reason='$reason' WHERE website_update_id='$update_id'";
    $db->query($sql);
} else if (isset($_POST['submit_for_revision'])) {
    $update_id = $_POST['update_id'];
    $sql = "UPDATE website_update SET status=3 WHERE website_update_id='$update_id'";
    $db->query($sql);
} else if (isset($_POST['submit_cancel'])) {
    $update_id = $_POST['update_id'];
    $reason = $db->real_escape_string($_POST['reason']);
    $sql = "UPDATE website_update SET status=4, reason='$reason' WHERE website_update_id='$update_id'";
    $db->query($sql);
}

$sql = "SELECT * FROM website_update";
if ($_SESSION['iupriv'] != 0) {
    $id = $_SESSION['iuid'];
    $sql .= " WHERE requested_by=$id";
}
if (!$_SESSION['iuid']) {
    header("location: index.php");
}
$result = $db->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>IHOMS Website Update - Data Entry</title>
    <meta name="description" content="IHOMS Website Update">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Lato.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/pikaday.min.css">
    <script src="assets/js/bootstrap-theme.js"></script>
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
    <main class="page contact-page">
        <section class="">
            <div class="mx-5">
                <h2 class="my-4">Website Update List</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date Requested</th>
                            <th>Title</th>
                            <th>Source</th>
                            <th>Type of File</th>
                            <th>Type of Change</th>
                            <th>Requested By</th>
                            <th>Uploaded Files</th>
                            <th>Print Word Document</th>
                            <th>Edit</th>
                            <th>Action</th>
                            <?php
                            if ($_SESSION['iupriv'] != 3) {
                            ?>
                                <th>Logs</th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['date_requested'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['source'] . "</td>";
                                $type_of_file = $row['type_of_file'];
                                $parts = explode('_', $type_of_file);
                                $formatted_type_of_file = ucwords(implode(' ', $parts));

                                echo "<td>" . $formatted_type_of_file . "</td>";
                                $type_of_change = $row['type_of_change'];
                                $parts = explode('_', $type_of_change);
                                $formatted_type_of_change = ucwords(implode(' ', $parts));

                                echo "<td>" . $formatted_type_of_change . "</td>";
                                $requester_id = $row['requested_by'];

                                $sql_user_list = "SELECT * FROM users WHERE userid = '$requester_id'";
                                $result_user_list = $db->query($sql_user_list);

                                if ($result_user_list) {
                                    if ($result_user_list->num_rows > 0) {
                                        $user_row = $result_user_list->fetch_assoc();
                                        echo "<td>" . $user_row['fname'] . " " . $user_row['lname'] . "</td>";
                                    } else {
                                        echo "<td>User Not Found</td>";
                                    }
                                } else {
                                    echo "<td>Error: " . $db->error . "</td>";
                                }

                                echo "<td>";
                                $file_paths = json_decode($row['file_paths']);
                                echo "<ol>";
                                foreach ($file_paths as $path) {
                                    $filename = basename($path);
                                    echo "<li><a class='my-3 py-3' href='$path' download>$filename</a></li>";
                                }
                                echo "</ol>";

                                echo "</td>";
                                echo "<td><a class='btn btn-success' target='_blank' href='generate_print.php?id=" . $row['website_update_id'] . "&filename=" . urlencode($row['title']) . ".docx'>Print</a></td>";
                                if ($row['status'] == 0 || $row['status'] == 3) {
                                    echo "<td><a href='/website_update/update_website_update.php?id=" . $row['website_update_id'] . "' class='btn btn-success'>Edit</a></td>";
                                } else {
                                    echo "<td><button class='btn btn-success' disabled>Edit</button></td>";
                                }
                                echo "<td>";
                                if ($row['status'] == 0) {
                                    echo "<form method='post'>";
                                    echo "<input type='hidden' name='update_id' value='" . $row['website_update_id'] . "'>";
                                    echo "<textarea name='reason' class='form-control' placeholder='Reason for Approval/Cancellation' required></textarea><br>";
                                    if ($_SESSION['iupriv'] != 3) {
                                        echo "<button type='submit' name='submit_approval' class='btn btn-success m-2'>Approve</button>&nbsp;";
                                        echo "<button type='submit' name='submit_for_revision' class='btn btn-success m-2'>For Revision</button>&nbsp;";
                                        echo "<button type='submit' name='submit_for_process' class='btn btn-success m-2'>For Processing</button>&nbsp;";
                                    }
                                    echo "<button type='submit' name='submit_cancel' class='btn btn-danger m-2'>Decline</button>";
                                    echo "</form>";
                                } else if ($row['status'] == 2) {
                                    echo 'Approved<br>';
                                    echo $row['reason'];
                                } else if ($row['status'] == 3) {
                                    echo 'For Revision<br>';
                                    echo $row['reason'];
                                } else if ($row['status'] == 1) {
                                    echo 'Processing<br>';
                                    echo $row['reason'];
                                } else if ($row['status'] == 4) {
                                    echo 'Declined<br>';
                                    echo $row['reason'];
                                } else {
                                    echo "N/A";
                                }
                                echo "</td>";
                                echo "<td>";
                                $sql_logs = "SELECT COUNT(*) AS log_count FROM website_update_logs WHERE website_update_id='" . $row['website_update_id'] . "'";
                                $result_logs = $db->query($sql_logs);
                                if ($result_logs) {
                                    $row_logs = $result_logs->fetch_assoc();
                                    if ($row_logs['log_count'] > 0) {
                                        echo "<a class='btn btn-info' href='website_update_logs.php?update_id=" . $row['website_update_id'] . "'>Logs</a>";
                                    }
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No data found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>
