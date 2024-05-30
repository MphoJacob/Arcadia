<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and retrieve POST variables
$name_id = isset($_POST['name_id']) ? $_POST['name_id'] : null;
$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : null;
$timeslot_id = isset($_POST['timeslot_id']) ? $_POST['timeslot_id'] : null;
$location_id = isset($_POST['location_id']) ? $_POST['location_id'] : null;

if (!$name_id || !$group_id || !$timeslot_id || !$location_id) {
    die("Error: Missing required parameters.");
}

// Check if the user has already booked the same time slot
$check_user_sql = "SELECT COUNT(*) as user_booking_count 
                   FROM bookings 
                   WHERE name_id = '$name_id' AND timeslot_id = '$timeslot_id'";
$user_result = $conn->query($check_user_sql);
$user_row = $user_result->fetch_assoc();
$user_booking_count = $user_row['user_booking_count'];

if ($user_booking_count > 0) {
    die("Error: You have already booked this time slot.");
}

// Check if the time slot and location are fully booked
$check_sql = "SELECT COUNT(*) as booking_count 
              FROM bookings 
              WHERE timeslot_id = '$timeslot_id' AND location_id = '$location_id'";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();
$booking_count = $row['booking_count'];

// Get the global booking limit
$max_limit_sql = "SELECT value FROM global_settings WHERE setting = 'booking_limit'";
$max_limit_result = $conn->query($max_limit_sql);
$max_limit_row = $max_limit_result->fetch_assoc();
$max_limit = $max_limit_row ? $max_limit_row['value'] : 0;

if ($booking_count >= $max_limit) {
    header("Location: fully_booked.php");
    exit();
}

// Insert new booking
$booking_date = date('Y-m-d H:i:s');
$insert_sql = "INSERT INTO bookings (name_id, group_id, timeslot_id, location_id, booking_date) 
               VALUES ('$name_id', '$group_id', '$timeslot_id', '$location_id', '$booking_date')";

if ($conn->query($insert_sql) === TRUE) {
    $_SESSION['booking_id'] = $conn->insert_id;
    header("Location: booking_success.php");
    exit();
} else {
    echo "Error: " . $insert_sql . "<br>" . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Processing Booking</title>
</head>
<body>
    <p>Processing your booking...</p>
</body>
</html>
