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

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1alaloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    log_error("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Fetch booking details
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$booking_details = [];
if ($booking_id > 0) {
    $stmt = $conn->prepare("SELECT group_id, name_id, timeslot_id, location_id FROM bookings WHERE id = ?");
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        die("Error preparing the statement.");
    }
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking_details = $result->fetch_assoc();
    $stmt->close();
}

// Fetch all timeslots
$timeslots_result = $conn->query("SELECT id, CONCAT(start_time, ' - ', end_time) AS timeslot FROM timeslots");
if ($timeslots_result === false) {
    log_error("Fetch timeslots failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch timeslots failed: " . $conn->error);
}

// Fetch all locations
$locations_result = $conn->query("SELECT id, location_name FROM locations");
if ($locations_result === false) {
    log_error("Fetch locations failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch locations failed: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify_booking'])) {
    $new_timeslot_id = $_POST['timeslot_id'];
    $new_location_id = $_POST['location_id'];

    $update_sql = "UPDATE bookings SET timeslot_id = ?, location_id = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the update statement.";
    } else {
        $stmt->bind_param('iii', $new_timeslot_id, $new_location_id, $booking_id);
        if ($stmt->execute()) {
            $message = "Successfully updated the booking.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error updating the booking.";
        }
        $stmt->close();
    }
    header("Location: admin_manage_bookings.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modify Booking</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Modify Booking</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($booking_details)): ?>
            <form method="post" action="modify_booking.php?booking_id=<?php echo $booking_id; ?>">
                <div class="form-group">
                    <label for="timeslot_id">Time Slot:</label>
                    <select id="timeslot_id" name="timeslot_id" class="form-control" required>
                        <?php while($timeslot = $timeslots_result->fetch_assoc()): ?>
                            <option value="<?php echo $timeslot['id']; ?>" <?php if ($timeslot['id'] == $booking_details['timeslot_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($timeslot['timeslot']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="location_id">Location:</label>
                    <select id="location_id" name="location_id" class="form-control" required>
                        <?php while($location = $locations_result->fetch_assoc()): ?>
                            <option value="<?php echo $location['id']; ?>" <?php if ($location['id'] == $booking_details['location_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($location['location_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="modify_booking" class="btn btn-primary">Modify Booking</button>
            </form>
        <?php else: ?>
            <p>Booking details not found.</p>
        <?php endif; ?>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_manage_bookings.php'">Back to Manage Bookings</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
