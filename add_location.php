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

// Handle addition of a new location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_location'])) {
    $location_name = $_POST['location_name'];
    $add_sql = "INSERT INTO locations (location_name) VALUES (?)";
    $stmt = $conn->prepare($add_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the add statement.";
    } else {
        $stmt->bind_param('s', $location_name);
        if ($stmt->execute()) {
            $message = "Successfully added the location: $location_name.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error adding the location: $location_name.";
        }
        $stmt->close();
    }
}

// Handle deletion of a location and its associated time slots
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_location'])) {
    $location_id = $_POST['location_id'];
    if (!empty($location_id)) {
        // Delete associated time slots first
        $delete_timeslots_sql = "DELETE FROM timeslots WHERE location_id = ?";
        $stmt = $conn->prepare($delete_timeslots_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the delete timeslots statement.";
        } else {
            $stmt->bind_param('i', $location_id);
            if (!$stmt->execute()) {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error deleting associated time slots.";
            }
            $stmt->close();
        }

        // Delete the location
        $delete_location_sql = "DELETE FROM locations WHERE id = ?";
        $stmt = $conn->prepare($delete_location_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the delete location statement.";
        } else {
            $stmt->bind_param('i', $location_id);
            if ($stmt->execute()) {
                $message = "Successfully deleted the location.";
            } else {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error deleting the location.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid location ID.";
    }
}

// Fetch all locations
$locations_result = $conn->query("SELECT id, location_name FROM locations");
if ($locations_result === false) {
    log_error("Fetch locations failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch locations failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Locations</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Manage Locations</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h2>Add New Location</h2>
        <form method="post" action="add_location.php">
                       <div class="form-group">
                <label for="location_name">Location Name:</label>
                <input type="text" id="location_name" name="location_name" class="form-control" required>
            </div>
            <button type="submit" name="add_location" class="btn btn-primary">Add Location</button>
        </form>

        <h2 class="mt-5">Existing Locations</h2>
        <?php if ($locations_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Location Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($location = $locations_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($location['location_name']); ?></td>
                            <td>
                                <form method="post" action="add_location.php" style="display:inline;">
                                    <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                                    <button type="submit" name="delete_location" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No locations found.</p>
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

