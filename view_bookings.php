<?php
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the global booking limit
$max_limit_sql = "SELECT value FROM global_settings WHERE setting = 'booking_limit'";
$max_limit_result = $conn->query($max_limit_sql);
$max_limit_row = $max_limit_result->fetch_assoc();
$max_limit = $max_limit_row ? $max_limit_row['value'] : 0;

$sql = "SELECT timeslots.start_time, timeslots.end_time, locations.location_name, COUNT(bookings.id) as booked_count, 
        ($max_limit - COUNT(bookings.id)) as available_slots 
        FROM bookings 
        JOIN timeslots ON bookings.timeslot_id = timeslots.id 
        JOIN locations ON bookings.location_id = locations.id 
        GROUP BY timeslots.start_time, timeslots.end_time, locations.location_name";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Bookings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>View All Bookings</h1>
    </header>
    <main class="container my-5">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Time Slot</th>
                    <th>Location</th>
                    <th>Booked Count</th>
                    <th>Available Slots</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . substr($row['start_time'], 0, 5) . " - " . substr($row['end_time'], 0, 5) . "</td>
                                <td>" . $row['location_name'] . "</td>
                                <td>" . $row['booked_count'] . "</td>
                                <td>" . $row['available_slots'] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No bookings found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='booking.php'">Back to Booking</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
