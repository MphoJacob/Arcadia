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

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1aloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    log_error("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Fetch all locations
$locations_result = $conn->query("SELECT id, location_name FROM locations");
if ($locations_result === false) {
    log_error("Fetch locations failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch locations failed: " . $conn->error);
}

// Handle addition of a new time slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timeslot'])) {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $max_limit = $_POST['max_limit'];
    $location_id = $_POST['location_id'];

    $add_sql = "INSERT INTO timeslots (start_time, end_time, max_limit, location_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($add_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the add statement.";
    } else {
        $stmt->bind_param('ssii', $start_time, $end_time, $max_limit, $location_id);
        if ($stmt->execute()) {
            $message = "Successfully added the time slot.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error adding the time slot.";
        }
        $stmt->close();
    }
}

// Handle deletion of a time slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_timeslot'])) {
    $timeslot_id = $_POST['timeslot_id'];
    if (!empty($timeslot_id)) {
        $delete_sql = "DELETE FROM timeslots WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the delete statement.";
        } else {
            $stmt->bind_param('i', $timeslot_id);
            if ($stmt->execute()) {
                $message = "Successfully deleted the time slot.";
            } else {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error deleting the time slot.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid time slot ID.";
    }
}

// Fetch all time slots
$timeslots_result = $conn->query("SELECT t.id, CONCAT(t.start_time, ' - ', t.end_time) as timeslot, t.max_limit, l.location_name 
                                  FROM timeslots t
                                  JOIN locations l ON t.location_id = l.id");
if ($timeslots_result === false) {
    log_error("Fetch timeslots failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch timeslots failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Time Slots</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Manage Time Slots</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h2>Add New Time Slot</h2>
        <form method="post" action="add_timeslot.php">
            <div class="form-group">
                <label for="start_time">Start Time:</label>
                <input type="time" id="start_time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="max_limit">Maximum Limit:</label>
                <input type="number" id="max_limit" name="max_limit" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="location_id">Location:</label>
                <select id="location_id" name="location_id" class="form-control" required>
                    <option value="">Select Location</option>
                    <?php while($location = $locations_result->fetch_assoc()): ?>
                        <option value="<?php echo $location['id']; ?>"><?php echo $location['location_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="add_timeslot" class="btn btn-primary">Add Time Slot</button>
        </form>

        <h2 class="mt-5">Existing Time Slots</h2>
        <?php if ($timeslots_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Time Slot</th>
                        <th>Maximum Limit</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($timeslot = $timeslots_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($timeslot['timeslot']); ?></td>
                            <td><?php echo htmlspecialchars($timeslot['max_limit']); ?></td>
                            <td><?php echo htmlspecialchars($timeslot['location_name']); ?></td>
                            <td>
                                <form method="post" action="add_timeslot.php" style="display:inline;">
                                    <input type="hidden" name="timeslot_id" value="<?php echo $timeslot['id']; ?>">
                                    <button type="submit" name="delete_timeslot" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No time slots found.</p>
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
