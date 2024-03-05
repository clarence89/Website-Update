<?php
// Start the session if not already started
session_start();

// Include necessary files
include("config.php");
include("auth.php");

// Redirect if user is already logged in
if (!$_SESSION['iuid']) {
        header("location: index.php");
}

// Allowed file types
$allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt');

// Process form submission
if ($_POST['date_requested'] && $_POST['title'] && $_POST['source'] && $_POST['type_of_file'] && $_POST['type_of_change']) {
    // Retrieve form data
    $date_requested = $db->real_escape_string($_POST['date_requested']);
    $title = $db->real_escape_string($_POST['title']);
    $source = $db->real_escape_string($_POST['source']);
    $type_of_file = $db->real_escape_string($_POST['type_of_file']);
    $type_of_change = $db->real_escape_string($_POST['type_of_change']);
    $requested_by = $db->real_escape_string($_SESSION['iuid']);

    // Handle file uploads
    $file_upload_dir = "uploads/"; // Adjusted file upload directory path
    $file_paths = array();
    $valid_upload = true; // Flag to track valid file uploads
    if (isset($_FILES['file_upload'])) {
        $file_array = reArrayFiles($_FILES['file_upload']);
        foreach ($file_array as $file) {
            // Check if the file was uploaded successfully
            if (!empty($file['tmp_name']) && $file['error'] == UPLOAD_ERR_OK) {
                $filename = $file['name'];
                $file_tmpname = $file['tmp_name'];
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // Append a unique identifier (e.g., timestamp) to the filename
                $unique_filename = uniqid() . '_' . $filename; // You can use other methods for uniqueness

                // Check if file type is allowed
                if (in_array($file_ext, $allowed_types)) {
                    $destination = $file_upload_dir . $unique_filename;

                    // Move the uploaded file to the destination
                    if (move_uploaded_file($file_tmpname, $destination)) {
                        $file_paths[] = $destination;
                    } else {
                        echo "Error: Failed to move uploaded file.";
                        $valid_upload = false; // Set the flag to false if file move fails
                    }
                } else {
                    echo "Error: Only JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, CSV, TXT files are allowed.";
                    $valid_upload = false; // Set the flag to false if file type is not allowed
                }
            } else {
                echo "Error: File upload error.";
                $valid_upload = false; // Set the flag to false if file upload error occurs
            }
        }
    }

    // If all file uploads are valid, proceed to insert data into the database
    if ($valid_upload) {
        // Convert file paths array to JSON string
        $file_paths_json = json_encode($file_paths);

        // Insert form data into database
        $sql = "INSERT INTO website_update (date_requested, title, source, type_of_file, type_of_change, requested_by, file_paths) VALUES ('$date_requested', '$title', '$source', '$type_of_file', '$type_of_change', '$requested_by', '$file_paths_json')";
        if ($db->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $db->error;
        }
    }
}

// Function to re-arrange the files array
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
                    <li class="nav-item"></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="page contact-page">
        <section class="portfolio-block contact">
            <div class="container">
                <div class="heading" style="margin-bottom: 50px;">
                    <h2>Data Entry Form</h2>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label" for="date_requested">Date Requested</label>
                        <input class="form-control item" type="date" id="date_requested" name="date_requested" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control item" type="text" id="title" name="title" required>
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
                        <label class="form-label" for="file_upload">File Upload</label>
                        <input class="form-control item" type="file" id="file_upload" name="file_upload[]" multiple>
                    </div>
                    <div class="mb-3" style="padding-top: 15px;">
                        <button class="btn btn-primary btn-lg d-block w-100" type="submit">Submit</button>
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
