<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available time slots with their locations
$sql = "SELECT timeslots.start_time, timeslots.end_time, locations.location_name,
        COUNT(bookings.id) as booking_count,
        (global_settings.value - COUNT(bookings.id)) as available_slots
        FROM timeslots
        JOIN locations ON timeslots.location_id = locations.id
        LEFT JOIN bookings ON timeslots.id = bookings.timeslot_id AND locations.id = bookings.location_id,
        global_settings
        WHERE global_settings.setting = 'booking_limit'
        GROUP BY timeslots.start_time, timeslots.end_time, locations.location_name";

$result = $conn->query($sql);
if ($result === false) {
    die("Error: " . $conn->error);
}

$available_slots = [];
while ($row = $result->fetch_assoc()) {
    $available_slots[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fully Booked</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Fully Booked</h1>
    </header>
    <main class="container my-5">
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Sorry!</h4>
            <p>All time slots are fully booked.</p>
            <hr>
            <p class="mb-0">Available Slots:</p>
            <ul>
                <?php
                if (count($available_slots) > 0) {
                    foreach ($available_slots as $slot) {
                        echo "<li>" . substr($slot['start_time'], 0, 5) . " to " . substr($slot['end_time'], 0, 5) . " at " . $slot['location_name'] . " - Available Slots: " . $slot['available_slots'] . "</li>";
                    }
                } else {
                    echo "<li>No available slots.</li>";
                }
                ?>
            </ul>
        </div>
        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-primary" onclick="location.href='booking.php'">Back to Booking</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
