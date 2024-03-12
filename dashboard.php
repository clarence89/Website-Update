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

$allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt');



function reArrayFiles(&$file_post)
{
    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>IMIS Website Update - Data Entry</title>
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
        <section class="portfolio-block contact">
            <div class="container">
                <div class="heading" style="margin-bottom: 50px;">
                    <h2>Website Update Data Entry Form</h2>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label" for="title">Title</label>
                        <select class="form-control item" id="title" name="title" required>
                            <option value="">Select Title</option>
                            <?php
                            $sql_titles = "SELECT * FROM website_title";
                            $result_titles = $db->query($sql_titles);
                            if ($result_titles->num_rows > 0) {
                                while ($row_title = $result_titles->fetch_assoc()) {
                                    echo "<option value='" . $row_title['title_name'] . "'>" . $row_title['title_name'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="source">Source</label>
                        <input class="form-control item" type="text" id="source" name="source" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type_of_file">Type of File</label>
                        <select class="form-control item" id="type_of_file" name="type_of_file" required>
                            <option value="soft_copy">Soft Copy</option>
                            <option value="hard_copy">Hard Copy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type_of_change">Type of Change</label>
                        <select class="form-control item" id="type_of_change" name="type_of_change" required>
                            <option value="change_to_existing">Change to Existing Content</option>
                            <option value="add_new_content">Add New Content</option>
                            <option value="add_new_webpage">Add New Webpage</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="content">Content</label>
                        <textarea class="form-control item" id="content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="file_upload">File Upload</label>
                        <input class="form-control item" type="file" id="file_upload" name="file_upload[]" multiple>
                    </div>
                    <div class="mb-3" style="padding-top: 15px;">
                        <button class="btn btn-primary btn-lg d-block w-100" type="submit">Submit</button>
                        <?php
                        if (isset($_POST['title']) && isset($_POST['source']) && isset($_POST['type_of_file']) && isset($_POST['type_of_change'])) {
                            $date_requested = (new DateTime())->format('Y-m-d H:i:s');
                            $title = $db->real_escape_string($_POST['title']);
                            $source = $db->real_escape_string($_POST['source']);
                            $type_of_file = $db->real_escape_string($_POST['type_of_file']);
                            $type_of_change = $db->real_escape_string($_POST['type_of_change']);
                            $content = $db->real_escape_string($_POST['content']);
                            $requested_by = $db->real_escape_string($_SESSION['iuid']);

                            $file_upload_dir = "uploads/";
                            $file_paths = array();
                            $valid_upload = true;
                            $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt');
                            if (!empty($_FILES['file_upload']['name'][0])) {
                                $file_array = reArrayFiles($_FILES['file_upload']);
                                foreach ($file_array as $file) {
                                    if (!empty($file['tmp_name']) && $file['error'] == UPLOAD_ERR_OK) {
                                        $filename = $file['name'];
                                        $file_tmpname = $file['tmp_name'];
                                        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                                        $unique_filename = uniqid() . '_' . $filename;

                                        if (in_array($file_ext, $allowed_types)) {
                                            $destination = $file_upload_dir . $unique_filename;

                                            if (move_uploaded_file($file_tmpname, $destination)) {
                                                $file_paths[] = $destination;
                                            } else {
                                                echo "Error: Failed to move uploaded file.";
                                                $valid_upload = false;
                                            }
                                        } else {
                                            echo "Error: Only JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, CSV, TXT files are allowed.";
                                            $valid_upload = false;
                                        }
                                    } else {
                                        echo "Error: File upload error.";
                                        $valid_upload = false;
                                    }
                                }
                            }

                            if ($valid_upload) {
                                $file_paths_json = json_encode($file_paths);

                                $sql = "INSERT INTO website_update (date_requested, title, source, type_of_file, type_of_change, requested_by, content, file_paths) VALUES ('$date_requested', '$title', '$source', '$type_of_file', '$type_of_change', '$requested_by', '$content', '$file_paths_json')";
                                if ($db->query($sql) === TRUE) {
                                    echo "New record created successfully";
                                    $update_id = $db->insert_id;
                                    $status = "pending";
                                    $reason = "";
                                    $sql_log = "INSERT INTO website_update_logs (website_update_id, date_requested, title, source, type_of_file, type_of_change, requested_by, status, reason, log_type) VALUES ('$update_id', '$date_requested', '$title', '$source', '$type_of_file', '$type_of_change', '$requested_by', '$status', '$reason', 'Insert')";
                                    if ($db->query($sql_log) === TRUE) {
                                        echo "Log entry added successfully";
                                        header("location: website-lists.php");
                                    } else {
                                        echo "Error adding log entry: " . $db->error;
                                    }
                                } else {
                                    echo "Error: " . $sql . "<br>" . $db->error;
                                }
                            }
                        }

                        ?>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.6.1/pikaday.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>
