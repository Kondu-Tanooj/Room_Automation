<?php
$hostname = "localhost";
$username = "root";
$password = "Ktanooj@2004";
$database = "home_automation";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Kolkata'); 
// Function to update the status of ESP8266
function updateStatus($conn, $device_name, $status) {
    $stmt = $conn->prepare("
        UPDATE esp_status 
        SET status = ?, last_update = NOW() 
        WHERE device_name = ?
    ");
    $stmt->bind_param("ss", $status, $device_name);
    $stmt->execute();
    $stmt->close();
}

// Check if 'status' parameter is set and equals 'online'
if (isset($_GET['status']) && $_GET['status'] === 'online') {
    // Update status to online for the ESP8266
    updateStatus($conn, 'ESP8266', 'online');

    // Fetch the status of fans and lights
    $result = $conn->query("SELECT device_name, state FROM devices WHERE device_name LIKE 'fan%' OR device_name LIKE 'light%'");

    $devices = array();

    // Fetch the results and format them as JSON
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $devices[] = array(
                "device_name" => $row["device_name"],
                "state" => $row["state"]
            );
        }
    }

    // Return JSON response with fans and lights status
    header('Content-Type: application/json');
    echo json_encode(array(
        'devices' => $devices
    ));
} else {
    // Return a message if status is not 'online'
    echo "Invalid request or status not online.";
}

$conn->close();
?>

