<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

function log_error($message) {
    error_log($message);
    echo "<div class='alert alert-danger'>$message</div>";
}

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    log_error("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle deletion of a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    if (!empty($booking_id)) {
        $delete_sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the delete statement.";
        } else {
            $stmt->bind_param('i', $booking_id);
            if ($stmt->execute()) {
                $message = "Successfully deleted the booking.";
            } else {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error deleting the booking.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid booking ID.";
    }
}

// Fetch all bookings
$bookings_sql = "SELECT b.id, g.name as group_name, n.name as name, 
                 CONCAT(t.start_time, ' - ', t.end_time) as timeslot, l.location_name, b.booking_date
                 FROM bookings b
                 JOIN groups g ON b.group_id = g.id
                 JOIN names n ON b.name_id = n.id
                 JOIN timeslots t ON b.timeslot_id = t.id
                 JOIN locations l ON b.location_id = l.id
                 ORDER BY b.booking_date DESC";
$bookings_result = $conn->query($bookings_sql);
if ($bookings_result === false) {
    log_error("Fetch bookings failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch bookings failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Trolly Bookings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Manage Trolly Bookings</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h2>All Bookings</h2>
        <?php if ($bookings_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Name</th>
                        <th>Time Slot</th>
                        <th>Location</th>
                        <th>Booking Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['group_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['timeslot']); ?></td>
                            <td><?php echo htmlspecialchars($booking['location_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td>
                                <form method="post" action="admin_manage_bookings.php" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="delete_booking" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <form method="get" action="modify_booking.php" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Modify</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_panel.php'">Back to Admin Panel</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
