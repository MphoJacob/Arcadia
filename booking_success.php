<?php
session_start();
if (!isset($_SESSION['booking_id'])) {
    header("Location: booking.php");
    exit();
}

$booking_id = $_SESSION['booking_id'];
unset($_SESSION['booking_id']);

// Connect to the database to retrieve booking details
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT b.id, n.name, g.name as group_name, t.start_time, t.end_time, l.location_name
        FROM bookings b
        JOIN names n ON b.name_id = n.id
        JOIN groups g ON b.group_id = g.id
        JOIN timeslots t ON b.timeslot_id = t.id
        JOIN locations l ON b.location_id = l.id
        WHERE b.id = '$booking_id'";
$result = $conn->query($sql);
$booking = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Success</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Booking Successful</h1>
    </header>
    <main class="container my-5">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Booking Confirmed!</h4>
            <p>Your booking has been successfully confirmed. Here are the details:</p>
            <hr>
            <p><strong>Name:</strong> <?php echo $booking['name']; ?></p>
            <p><strong>Group:</strong> <?php echo $booking['group_name']; ?></p>
            <p><strong>Time Slot:</strong> <?php echo substr($booking['start_time'], 0, 5) . " - " . substr($booking['end_time'], 0, 5); ?></p>
            <p><strong>Location:</strong> <?php echo $booking['location_name']; ?></p>
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
