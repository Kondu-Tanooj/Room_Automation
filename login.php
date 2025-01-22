<?php
session_start();

// Establish database connection
$hostname = "localhost";
$username = "root";
$password = "Ktanooj@2004";
$database = "home_automation";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve username and password from the form
if(isset($_POST['adminusername']) && isset($_POST['adminpass'])) {
    $username = $_POST['adminusername'];
    $password = $_POST['adminpass'];

    // SQL query to check if username and password exist in the database
    $sql = "SELECT * FROM credentials WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Authentication successful
        // Set session variable to indicate user is logged in
        $_SESSION['admin_logged_in'] = true;

        // Redirect to admin page
        header("Location: room.php");
        exit();
    } else {
        // Authentication failed
        echo "<script>alert('Invalid username or password');</script>";
    }
}

$conn->close();
?>
