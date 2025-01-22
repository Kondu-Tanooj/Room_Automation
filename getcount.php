<?php
$hostname = "localhost";
$username = "root";
$password = "Ktanooj@2004";
$database = "slog";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data is available in the request and if sensor1 is "count"
if (isset($_REQUEST['sensor1']) && $_REQUEST['sensor1'] === "count") {
    // Prepare and execute the query
    $stmt_count = $conn->prepare("SELECT COUNT(*) AS id_count FROM ENTRY_Table WHERE OUT_Time IS NULL");
    $stmt_count->execute();
    
    // Get the result
    $result_count = $stmt_count->get_result();
    
    if ($result_count->num_rows > 0) {
        $row_count = $result_count->fetch_assoc();
        $id_count = $row_count["id_count"];
        echo $id_count;
    } else {
        echo 0;
    }

    // Close the statement
    $stmt_count->close();
} else {
    echo "Invalid request or sensor data.";
}

// Close the connection
$conn->close();
?>

