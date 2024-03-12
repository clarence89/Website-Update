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
    $status = 2;
} elseif (isset($_POST['submit_for_process'])) {
    $status = 1;
} elseif (isset($_POST['submit_for_revision'])) {
    $status = 3;
} elseif (isset($_POST['submit_cancel'])) {
    $status = 4;
}

if (isset($status)) {
    $update_id = $_POST['update_id'];
    $reason = $db->real_escape_string($_POST['reason']);

    // Fetch existing data from website_update table
    $sql_select_update = "SELECT * FROM website_update WHERE website_update_id = '$update_id'";
    $result_update = $db->query($sql_select_update);

    if ($result_update && $result_update->num_rows > 0) {
        $row_update = $result_update->fetch_assoc();
        $previous_status = $row_update['status'];

        // Update status and reason in website_update table
        $sql_update_status = "UPDATE website_update SET status='$status', reason='$reason' WHERE website_update_id='$update_id'";
        $db->query($sql_update_status);

        // Insert log into website_update_logs
        $date_requested = $row_update['date_requested'];
        $title = $row_update['title'];
        $source = $row_update['source'];
        $type_of_file = $row_update['type_of_file'];
        $type_of_change = getStatusName($status); // Function to get the status name based on status code
        $requested_by = $row_update['requested_by'];
        $file_paths = $row_update['file_paths'];
        $content = $row_update['content']; // New content field
        $created_at = date("Y-m-d H:i:s");
        $log_type = $type_of_change;

        $sql_insert_log = "INSERT INTO website_update_logs (website_update_id, date_requested, title, source, type_of_file, type_of_change, requested_by, file_paths, content, status, reason, created_at, log_type)
                           VALUES ('$update_id', '$date_requested', '$title', '$source', '$type_of_file', '$type_of_change', '$requested_by', '$file_paths', '$content', '$status', '$reason', '$created_at', '$log_type')";
        $db->query($sql_insert_log);
    }
}

// Function to get status name based on status code
function getStatusName($status_code)
{
    switch ($status_code) {
        case 1:
            return "Processing";
        case 2:
            return "Approved";
        case 3:
            return "For Revision";
        case 4:
            return "Declined";
        default:
            return "N/A";
    }
}

$sql = "SELECT * FROM website_update";

if ($_SESSION['iupriv'] == 3) {
    $id = $_SESSION['iuid'];
    $sql .= " WHERE requested_by=$id";
} else {
    $sql .= " ORDER BY CASE status
                    WHEN 1 THEN 0
                    WHEN 0 THEN 1
                    WHEN 3 THEN 2
                    WHEN 2 THEN 3
                    WHEN 4 THEN 4
                    ELSE 5
                    END";
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
    <title>IMIS Website Update - Website Update Data Entry</title>
    <meta name="description" content="IMIS Website Update">
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

                            <th>Content</th> <!-- New column added -->
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
                                echo "<td>" . $row['content'] . "</td>";
                                echo "<td><a class='btn btn-success' target='_blank' href='generate_print.php?id=" . $row['website_update_id'] . "&filename=" . urlencode($row['title']) . ".docx'>Print</a></td>";
                                // New column 'content' added

                                if ($row['status'] == 0 || $row['status'] == 3) {
                                    echo "<td><a href='/website_update/update_website_update.php?id=" . $row['website_update_id'] . "' class='btn btn-success m-1'>Edit</a></td>";
                                } else {
                                    echo "<td><button class='btn btn-success m-1' disabled>Edit</button></td>";
                                }
                                echo "<td>";
                                if ($row['status'] == 0) {
                                    echo '<form method="post">';
                                    echo '<input type="hidden" name="update_id" value="' . $row['website_update_id'] . '">';
                                    echo '<textarea name="reason" class="form-control" placeholder="Remarks"></textarea><br>';
                                    if ($_SESSION['iupriv'] != 3) {
                                        echo '<button type="submit" name="submit_for_revision" class="btn btn-warning m-1">For Revision</button>&nbsp;';
                                        echo '<button type="submit" name="submit_for_process" class="btn btn-info m-1">For Processing</button>&nbsp;';
                                        echo '<button type="submit" name="submit_cancel" class="btn btn-danger m-1">Decline</button>';
                                    } else {
                                        echo '<button type="submit" name="submit_cancel" class="btn btn-danger m-1">Cancel</button>';
                                    }
                                    echo '</form>';

                                    echo '</div>';
                                } elseif ($row['status'] == 2) {
                                    echo '<div class="alert alert-success" role="alert">Approved</div>';
                                    if (!empty($row['reason'])) {
                                        echo '<p class="font-weight-bold">Remarks:</p>';
                                        echo '<p>' . $row['reason'] . '</p>';
                                    }
                                } elseif ($row['status'] == 3) {
                                    echo '<div class="alert alert-warning" role="alert">For Revision</div>';
                                    if (!empty($row['reason'])) {
                                        echo '<p class="font-weight-bold">Remarks:</p>';
                                        echo '<p>' . $row['reason'] . '</p>';
                                    }
                                } elseif ($row['status'] == 1) {
                                    echo '<div class="alert alert-info" role="alert">Processing</div>';
                                    if (!empty($row['reason'])) {
                                        echo '<p class="font-weight-bold">Remarks:</p>';
                                        echo '<p>' . $row['reason'] . '</p>';
                                    }
                                    echo '<form method="post">';
                                    echo '<input type="hidden" name="update_id" value="' . $row['website_update_id'] . '">';
                                    echo '<div class="form-group">';
                                    echo '<label for="reasonTextarea">Remarks:</label>';
                                    echo '<textarea name="reason" id="reasonTextarea" class="form-control" rows="3"></textarea>';
                                    echo '</div>';
                                    echo '<div class="text-right">';
                                    if ($_SESSION['iupriv'] != 3) {
                                        echo '<button type="submit" name="submit_approval" class="btn btn-success m-1">Done</button>';
                                        echo '<button type="submit" name="submit_cancel" class="btn btn-danger m-1">Decline</button>';
                                    } else {
                                        echo '<button type="submit" name="submit_cancel" class="btn btn-danger m-1">Cancel</button>';
                                    }

                                    echo '</div>';
                                    echo '</form>';
                                } elseif ($row['status'] == 4) {
                                    echo '<div class="alert alert-danger" role="alert">Declined</div>';
                                    if (!empty($row['reason'])) {
                                        echo '<p class="font-weight-bold">Remarks:</p>';
                                        echo '<p>' . $row['reason'] . '</p>';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                echo "</td>";
                                if ($_SESSION['iupriv'] != 3) {
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
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11'>No data found</td></tr>";
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
