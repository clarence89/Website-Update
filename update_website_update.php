<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("config.php");
include("auth.php");
ob_start();
if (!$_SESSION['iuid']) {
    header("location: index.php");
    ob_end_flush();;
}

if (isset($_GET['id'])) {
    $update_id = $_GET['id'];

    $sql = "SELECT * FROM website_update WHERE website_update_id='$update_id'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $date_requested = $row['date_requested'];
        $title = $row['title'];
        $source = $row['source'];
        $type_of_file = $row['type_of_file'];
        $type_of_change = $row['type_of_change'];
        $content = $row['content'];
        $file_paths = json_decode($row['file_paths']);
        // $reason = $row['reason'];
        $reason = 'Request Update';
    } else {
        echo "Update not found";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $db->real_escape_string($_POST['title']);
    $source = $db->real_escape_string($_POST['source']);
    $type_of_file = $db->real_escape_string($_POST['type_of_file']);
    $type_of_change = $db->real_escape_string($_POST['type_of_change']);
    $content = $db->real_escape_string($_POST['content']);
    $user_id = $_SESSION['iuid'];
    // $old_files = array();
    if (!empty($_FILES['file_upload']['name'][0])) {
        foreach ($file_paths as $file) {
            $destination_directory = "failed/uploads/";
            $filename = basename($file);
            $new_file_path = $destination_directory . $filename;

            if (rename($file, $new_file_path)) {
                // $old_files[] = $new_file_path;
                echo "File moved successfully.";
            } else {
                echo "Error: Failed to move the file.";
            }
        }

        $file_upload_dir = "uploads/";
        $valid_upload = true;
        $new_file_paths = array();

        $file_array = reArrayFiles($_FILES['file_upload']);
        foreach ($file_array as $file) {
            if (!empty($file['tmp_name']) && $file['error'] == UPLOAD_ERR_OK) {
                $filename = $file['name'];
                $file_tmpname = $file['tmp_name'];
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $unique_filename = uniqid() . '_' . $filename;

                if (move_uploaded_file($file_tmpname, $file_upload_dir . $unique_filename)) {
                    $new_file_paths[] = $file_upload_dir . $unique_filename;
                } else {
                    echo "Error: Failed to move uploaded file.";
                    $valid_upload = false;
                }
            } else {
                echo "Error: File upload error.";
                $valid_upload = false;
            }
        }

        if ($valid_upload) {
            $all_file_paths = $new_file_paths;
            $file_paths_json = json_encode($all_file_paths);

            $sql = "UPDATE website_update SET title='$title', source='$source', type_of_file='$type_of_file', type_of_change='$type_of_change', content='$content', file_paths='$file_paths_json', status = 0 WHERE website_update_id='$update_id'";
            if ($db->query($sql) === TRUE) {
                echo "Record updated successfully";
                // $files = json_encode($old_files);
                $sql_log = "INSERT INTO website_update_logs (website_update_id,  title, source, type_of_file, type_of_change, requested_by, file_paths, status, reason, log_type, date_requested, content) VALUES ('$update_id',  '$title', '$source', '$type_of_file', '$type_of_change', '$user_id' , '$file_paths_json', '$status', '$reason', 'Update With File', '$date_requested', '$content')";
                if ($db->query($sql_log) === TRUE) {
                    echo "Log entry added successfully";
                } else {
                    echo "Error adding log entry: " . $db->error;
                }
            } else {
                echo "Error updating record: " . $db->error;
            }
        }
    } else {
        $sql = "UPDATE website_update SET title='$title', source='$source', type_of_file='$type_of_file', type_of_change='$type_of_change', content='$content', status = 0 WHERE website_update_id='$update_id'";
        if ($db->query($sql) === TRUE) {
            echo "Record updated successfully";
            $sql_log = "INSERT INTO website_update_logs (website_update_id, title, source, type_of_file, type_of_change, requested_by, status, reason, log_type, date_requested, content) VALUES ('$update_id', '$title', '$source', '$type_of_file', '$type_of_change', '$user_id',  '$status', '$reason' , 'Update Without File', '$date_requested', '$content')";
            if ($db->query($sql_log) === TRUE) {
                echo "Log entry added successfully";
            } else {
                echo "Error adding log entry: " . $db->error;
            }
        } else {
            echo "Error updating record: " . $db->error;
        }
    }
    header("location: website-lists.php");
    ob_end_flush();;
}

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
    <title>Edit Website Update</title>
    <meta name="description" content="Edit Website Update">
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
                    <?php if ($_SESSION['iupriv'] != 1) { ?>
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
                    <h2>Edit Website Update</h2>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="update_id" value="<?php echo $update_id; ?>">
                    <div class="mb-3">
                        <label class="form-label" for="title">Title</label>
                        <select class="form-control item" id="title" name="title" required>
                            <option value="">Select Title</option>
                            <?php
                            $sql_titles = "SELECT * FROM website_title WHERE status = 0";
                            $result_titles = $db->query($sql_titles);
                            if ($result_titles->num_rows > 0) {
                                while ($row_title = $result_titles->fetch_assoc()) {
                                    $selected = ($title == $row_title['title_name']) ? 'selected' : '';
                                    echo "<option value='" . $row_title['title_name'] . "' $selected>" . $row_title['title_name'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="source">Source</label>
                        <input class="form-control item" type="text" id="source" name="source" value="<?php echo $source; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type_of_file">Type of File</label>
                        <select class="form-control item" id="type_of_file" name="type_of_file" required>
                            <option value="soft_copy" <?php if ($type_of_file == 'soft_copy') echo 'selected'; ?>>Soft Copy</option>
                            <option value="hard_copy" <?php if ($type_of_file == 'hard_copy') echo 'selected'; ?>>Hard Copy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="type_of_change">Type of Change</label>
                        <select class="form-control item" id="type_of_change" name="type_of_change" required>
                            <option value="change_to_existing" <?php if ($type_of_change == 'change_to_existing') echo 'selected'; ?>>Change to Existing Content</option>
                            <option value="add_new_content" <?php if ($type_of_change == 'add_new_content') echo 'selected'; ?>>Add New Content</option>
                            <option value="add_new_webpage" <?php if ($type_of_change == 'add_new_webpage') echo 'selected'; ?>>Add New Webpage</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="content">Content</label>
                        <textarea class="form-control item" id="content" name="content" rows="5" required><?php echo isset($content) ? $content : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="file_upload">File Upload</label>
                        <input class="form-control item" type="file" id="file_upload" name="file_upload[]" multiple>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="website-lists.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!-- Include any additional JavaScript files here -->
</body>

</html>
