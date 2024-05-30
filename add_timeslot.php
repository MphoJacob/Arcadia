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

// Handle addition of a new time slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timeslot'])) {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $max_limit = $_POST['max_limit'];
    
    // Fetch all locations
    $locations_result = $conn->query("SELECT id FROM locations");
    if ($locations_result === false) {
        log_error("Fetch locations failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error fetching locations.";
    } else {
        while ($location = $locations_result->fetch_assoc()) {
            $location_id = $location['id'];
            $add_sql = "INSERT INTO timeslots (start_time, end_time, max_limit, location_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($add_sql);
            if ($stmt === false) {
                log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                $message = "Error preparing the add statement.";
                break;
            } else {
                $stmt->bind_param('ssii', $start_time, $end_time, $max_limit, $location_id);
                if ($stmt->execute()) {
                    $message = "Successfully added the time slot for all locations.";
                } else {
                    log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                    $message = "Error adding the time slot.";
                    break;
                }
                $stmt->close();
            }
        }
    }
}

// Fetch all time slots
$timeslots_sql = "SELECT t.id, t.start_time, t.end_time, t.max_limit, l.location_name 
                  FROM timeslots t
                  JOIN locations l ON t.location_id = l.id
                  ORDER BY l.location_name, t.start_time";
$timeslots_result = $conn->query($timeslots_sql);
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
                <label for="max_limit">Max Limit:</label>
                <input type="number" id="max_limit" name="max_limit" class="form-control" required>
            </div>
            <button type="submit" name="add_timeslot" class="btn btn-primary">Add Time Slot to All Locations</button>
        </form>

        <h2 class="mt-5">Existing Time Slots</h2>
        <?php if ($timeslots_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Max Limit</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($timeslot = $timeslots_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($timeslot['start_time']); ?></td>
                            <td><?php echo htmlspecialchars($timeslot['end_time']); ?></td>
                            <td><?php echo htmlspecialchars($timeslot['max_limit']); ?></td>
                            <td><?php echo htmlspecialchars($timeslot['location_name']); ?></td>
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
