<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.html');
    exit();
}

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

// Handle AJAX request to update device state
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device']) && isset($_POST['state'])) {
    $device = $_POST['device'];
    $state = $_POST['state'];

    // Update the device state in the database
    $updateQuery = "UPDATE devices SET state='$state' WHERE device_name='$device'";
    if ($conn->query($updateQuery) === TRUE) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    $conn->close();
    exit(); // Exit after handling the AJAX request
}
date_default_timezone_set('Asia/Kolkata'); 
// Check and update ESP8266 status
$current_time = time();
$esp_status = 'offline'; // Default status

// Fetch the last update time for the specific device
$result = $conn->query("SELECT last_update FROM esp_status WHERE device_name='ESP8266'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_update_time = strtotime($row['last_update']);
    $time_difference = $current_time - $last_update_time;
    
    // Debugging output
    error_log("Current Time: " . date('Y-m-d H:i:s', $current_time));
    error_log("Last Update Time: " . date('Y-m-d H:i:s', $last_update_time));
    error_log("Time Difference: " . $time_difference);

    // Update to offline if the last update was more than 15 seconds ago
    if ($time_difference > 5) {
        $updateQuery = "UPDATE esp_status SET status='offline', last_update=NOW() WHERE device_name='ESP8266'";
        if ($conn->query($updateQuery) === TRUE) {
            error_log("ESP Status updated to offline.");
        } else {
            error_log("Error updating ESP status: " . $conn->error);
        }
    }

    // Fetch the current ESP status
    $result = $conn->query("SELECT status FROM esp_status WHERE device_name='ESP8266'");
    if ($result->num_rows > 0) {
        $esp_status = $result->fetch_assoc()['status'];
    } else {
        $esp_status = 'offline'; // Default status if no records found
    }
} else {
    $esp_status = 'offline'; // Default status if no records found
}

// Fetch the current states of all devices
$result = $conn->query("SELECT * FROM devices");
$devices = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWall Board</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        h1 {
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .status {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        .indicator {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }

        .status-online .indicator {
            background-color: green;
        }

        .status-offline .indicator {
            background-color: red;
        }

        h2 {
            font-size: 30px;
            font-weight: bold;
            color: #444;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            text-align: center;
        }

        .section {
            width: 45%;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .device {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .device-name {
            font-size: 22px;
            font-weight: bold;
            color: #555;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4CAF50;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        footer {
            margin-top: 30px;
            padding: 10px;
            background-color: #333;
            color: white;
            text-align: center;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>

    <h1>Smart Room</h1>

    <!-- ESP Status Section -->
    <div class="container">
        <div class="status <?php echo $esp_status === 'online' ? 'status-online' : 'status-offline'; ?>">
            <span class="indicator"></span>
            ESP Status: <?php echo ucfirst($esp_status); ?>
        </div>
    </div>

    <div class="container">
        <!-- Section for Fans -->
        <div class="section">
            <h2>Fans</h2>
            <?php foreach ($devices as $device): ?>
                <?php if (strpos(strtolower($device['device_name']), 'fan') !== false): ?>
                    <div class="device">
                        <span class="device-name"><?php echo $device['device_name']; ?> :</span>
                        <label class="switch">
                            <input 
                                type="checkbox" 
                                id="toggle-<?php echo $device['device_name']; ?>" 
                                onclick="toggleDevice('<?php echo $device['device_name']; ?>', this.checked ? 'ON' : 'OFF')" 
                                <?php echo ($device['state'] == 'ON') ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Section for Lights -->
        <div class="section">
            <h2>Lights</h2>
            <?php foreach ($devices as $device): ?>
                <?php if (strpos(strtolower($device['device_name']), 'light') !== false): ?>
                    <div class="device">
                        <span class="device-name"><?php echo $device['device_name']; ?> :</span>
                        <label class="switch">
                            <input 
                                type="checkbox" 
                                id="toggle-<?php echo $device['device_name']; ?>" 
                                onclick="toggleDevice('<?php echo $device['device_name']; ?>', this.checked ? 'ON' : 'OFF')" 
                                <?php echo ($device['state'] == 'ON') ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        &copy; MVGR_SLC Smart Room
    </footer>

    <script>
        function toggleDevice(device, state) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.responseText === 'success') {
                    console.log(device + ' turned ' + state);
                } else {
                    console.error('Error updating ' + device);
                }
            };
            xhr.send('device=' + encodeURIComponent(device) + '&state=' + encodeURIComponent(state));
        }
    </script>
</body>
</html>

