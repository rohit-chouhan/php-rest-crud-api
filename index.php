<?php
error_reporting(0);

$config = parse_ini_file('config.env');

$servername = $config['SERVER'];
$username = $config['USERNAME'];
$password = $config['PASSWORD'];
$dbname = $config['DATABASE'];

$basic_auth = $config['BASIC_AUTH'];
$basic_auth_username = $config['BASIC_AUTH_USERNAME'];
$basic_auth_password = $config['BASIC_AUTH_PASSWORD'];

$supported_files = $config['SUPPORTED_FILES'];
$file_max_size_in_kb = $config['FILE_MAX_SIZE_IN_KB'];
$file_min_size_in_kb = $config['FILE_MIN_SIZE_IN_KB'];

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array(
        "status" => false,
        "message" => "Error: " .  mysqli_connect_error()
    ));
    die();
}



// POST method - Insert data into the specified table
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $path);
    $table = end($segments);
    if (isset($_GET['xform']) && $_GET['xform'] === 'true') {
        $data = $_POST;
    } else {
        $data = json_decode(file_get_contents("php://input"), true);
    }
    unset($_POST['xform']);

    // Handle image uploads
    $imageColumns = [];
    $filefields = isset($_GET['filefield']) ? explode(",", $_GET['filefield']) : [];
    foreach ($filefields as $filefield) {
        if (isset($_FILES[$filefield])) {
            $target_dir = "uploads/";
            $target_file_name = date('Ymd-His')."-".mt_rand(100000000000, 999999999999)."-".basename($_FILES[$filefield]["name"]);
            $target_file = $target_dir . $target_file_name;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            if (isset($_POST["submit"])) {
                $check = getimagesize($_FILES[$filefield]["tmp_name"]);
                if ($check !== false) {
                    $uploadOk = 1;
                } else {
                    $uploadOk = 0;
                }
            }
            // Check if file already exists
            /*if (file_exists($target_file)) {
                $uploadOk = 0;
                $image_upload_msg = "Filename already exist.";
            }*/
            // Check file size
            if ($_FILES[$filefield]["size"] > $file_max_size_in_kb*1000) {
                $uploadOk = 0;
                $image_upload_msg = "file size should not be more than ".$file_max_size_in_kb."kb";
            }

            if ($_FILES[$filefield]["size"] < $file_min_size_in_kb*1000) {
                $uploadOk = 0;
                $image_upload_msg = "file size should be more than ".$file_min_size_in_kb."kb";
            }
            // Allow certain file formats
            $allowed_extensions = explode(',', $supported_files);

            if (!in_array($imageFileType, $allowed_extensions)) {
                $uploadOk = 0;
                $image_upload_msg = $imageFileType . " file is not allowed";
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                $response = array("status" => false, "message" => (!empty($image_upload_msg)?$image_upload_msg:"Error uploading files."));
                http_response_code(400);
                echo json_encode($response);
                exit();
            // If everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES[$filefield]["tmp_name"], $target_file)) {
                    $imageColumns[$filefield] = $target_file_name;
                } else {
                    $response = array("status" => false, "message" => "Error uploading file.");
                    http_response_code(400);
                    echo json_encode($response);
                    exit();
                }
            }
        }
    }

    $columns = implode(", ", array_keys($data + $imageColumns));
    $values = "'" . implode("', '", $data + $imageColumns) . "'";
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";

    if (mysqli_query($conn, $sql)) {
        http_response_code(200);
        $response = array("status" => true, "message" => "New record created successfully");
    } else {
        http_response_code(400);
        $response = array("status" => false, "message" => "Error: " . mysqli_error($conn));
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}


// GET method - Get filtered table data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    Auth();
    // Get table name from URL path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $path);
    $table = end($segments);


    if (empty($table)) {
        http_response_code(200);
        echo json_encode(array(
            "status" => true,
            "message" => "Welcome, Your application is live now.",
            "documentation" => "http://github.com/rohit-chouhan/php-rest-crud-api"
        ));
        exit;
    }

    $filter = array();
    $sql = "SELECT * FROM $table";

    // Build WHERE clause based on filter parameters
    if (!empty($_GET)) {
        $sql .= " WHERE ";
        foreach ($_GET as $key => $value) {
            $filter[] = "$key='$value'";
        }

        $sql .= implode(" AND ", $filter);
    }

    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        http_response_code(400);
        echo json_encode(array(
            "status" => false,
            "message" => "Error: " . mysqli_error($conn)
        ));
    } else {
        http_response_code(200);
        $rows = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($rows);
    }
}

// PUT method - Update row in table
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // Get table name from URL path
    Auth();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $path);
    $table = end($segments);

    // Get column and value from URL parameter
    if (isset($_GET['id'])) {
        $column = 'id';
        $value = $_GET['id'];
    } else {
        $column = key($_GET);
        $value = $_GET[$column];
    }

    // Get update data from request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Build SET clause for update query
    $set = array();
    foreach ($data as $key => $values) {
        $set[] = "$key='$values'";
    }

    $set_clause = implode(", ", $set);

    // Execute update query
    $sql = "UPDATE $table SET $set_clause WHERE $column='$value'";
    $result = mysqli_query($conn, $sql);

    header('Content-Type: application/json');
    if ($result === false) {
        http_response_code(400);
        echo json_encode(array(
            "status" => false,
            "message" => "Error: " . mysqli_error($conn)
        ));
    } else {
        http_response_code(200);
        echo json_encode(array(
            "status" => true,
            "message" => "Row updated successfully."
        ));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    Auth();
    $input = json_decode(file_get_contents('php://input'), true);

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $path);
    $table = end($segments);

    // initialize the SQL query
    $sql = "DELETE FROM $table WHERE ";

    // determine the filter for the DELETE query based on the JSON input
    if (isset($input['id'])) {
        $sql .= "id = " . $input['id'];
    } else {
        foreach ($input as $column => $value) {
            $sql .= "$column = '$value' AND ";
        }
        $sql = rtrim($sql, ' AND ');
    }

    // execute the DELETE query
    $result = mysqli_query($conn, $sql);

    // check if the query was successful
    if ($result) {
        http_response_code(200);
        $response = array('status' => true, 'message' => 'Data deleted successfully');
    } else {
        http_response_code(400);
        $response = array('status' => false, 'message' => mysqli_error($conn));
    }

    // return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close connection
mysqli_close($conn);


function Auth()
{
    global $basic_auth;
    global $basic_auth_username;
    global $basic_auth_password;

    if ($basic_auth == true) {
        // Basic Authentication
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] !== $basic_auth_username || $_SERVER['PHP_AUTH_PW'] !== $basic_auth_password) {
            header('WWW-Authenticate: Basic realm="My API"');
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(array(
                "status" => false,
                "message" => "Unauthorized Access"
            ));
            exit;
        }
    }
}
