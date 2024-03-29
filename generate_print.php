<?php
include "config.php";
if (isset($_GET['id']) && isset($_GET['filename'])) {
    $id = $_GET['id'];
    $filename = $_GET['filename'];
    $sql = "SELECT * FROM website_update WHERE website_update_id = $id";
    $result = $db->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Error: No data found for the specified ID";
    }
} else {
    echo "Error: ID or filename not provided";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A4 Image Printing</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 1240px;
            height: 1754px;
        }

        img {
            top: 0px;
            left: 0px;
            width: 1240px;
            height: 1754px;
            height: auto;
            display: block;
        }

        #date_recieved {
            position: absolute;
            top: 210px;
            left: 210px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #title {
            position: absolute;
            top: 255px;
            left: 530px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;

        }

        #source {
            position: absolute;
            top: 295px;
            left: 370px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #type_of_file {
            position: absolute;
            top: 380px;
            left: 260px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #type_of_file1 {
            position: absolute;
            top: 380px;
            left: 400px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #type_of_change {
            position: absolute;
            top: 535px;
            left: 105px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #content {
            position: absolute;
            top: 560px;
            left: 450px;
            width: 680px;
            height: 300px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: medium;
            font-weight: bold;
            padding: 10px;
            word-wrap: break-word;
            overflow: hidden;
            /* Hide content that exceeds the height */
        }


        #type_of_change1 {
            position: absolute;
            top: 560px;
            left: 105px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #type_of_change2 {
            position: absolute;
            top: 585px;
            left: 105px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #status {
            position: absolute;
            top: 1070px;
            left: 650px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }

        #status2 {
            position: absolute;
            top: 1070px;
            left: 773px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }
        #user {
            position: absolute;
            top: 1020px;
            left: 200px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }
        #created_at {
            position: absolute;
            top: 1350px;
            left: 260px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: x-large;
            font-weight: bold;
            padding: 10px;
        }
    </style>
</head>

<body width="1240px" height="1754px">
    <img src="./assets/template/website_update_img.jpg" alt="A4 Image">
    <p id="date_recieved"><?= date('d-m-Y', strtotime($row['date_requested'])) ?></p>
    <p id="title"><?= $row['title'] ?></p>
    <p id="source"><?= $row['source'] ?></p>
    <?php
    if ($row['type_of_file'] == "soft_copy") {
        echo '<p id="type_of_file">✓</p>';
    }
    if ($row['type_of_file'] == "hard_copy") {
        echo '<p id="type_of_file1">✓</p>';
    }
    if ($row['type_of_change'] == "change_to_existing") {
        echo '<p id="type_of_change">✓</p>';
    }

    echo '<p id="content">' . $row['content'] . '</p>';

    if ($row['type_of_change'] == "add_new_content") {
        echo '<p id="type_of_change1">✓</p>';
    }
    if ($row['type_of_change'] == "add_new_webpage") {
        echo '<p id="type_of_change2">✓</p>';
    }
    if ($row['status'] == 1) {
        echo '<p id="status">✓</p>';
    }
    if ($row['status2'] == 2) {
        echo '<p id="status1">✓</p>';
    }
    $requester_id = $row['requested_by'];
    $sql_user_list = "SELECT * FROM users WHERE userid = '$requester_id'";
    $result_user_list = $db->query($sql_user_list);

    if ($result_user_list) {
        if ($result_user_list->num_rows > 0) {
            $user_row = $result_user_list->fetch_assoc();
            echo "<p id='user'>" . $user_row['fname'] . " " . $user_row['lname'] . "</p>";
        } else {
            echo "<p id='user'>User Not Found</p>";
        }
    } else {
        echo "<p id='user'>Error: " . $db->error . "</p>";
    }
    $update_id = $db->real_escape_string($row['website_update_id']);

    $sql_logs = "SELECT wul.*, u.fname, u.lname FROM website_update_logs AS wul LEFT JOIN users AS u ON wul.requested_by = u.userid WHERE wul.website_update_id = '$update_id' ORDER BY wul.created_at DESC LIMIT 1";
    $result_logs = $db->query($sql_logs);

    if ($result_logs->num_rows > 0) {

        while ($row = $result_logs->fetch_assoc()) {
            echo "<p id='created_at'>" . date('Y-m-d', strtotime($row['created_at'])) . "</p>";
        }
    } else {

    }
    ?>
</body>

</html>
