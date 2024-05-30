<?php
session_start();

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

// Fetch all groups
$groups_result = $conn->query("SELECT id, name FROM `groups`");
if ($groups_result === false) {
    log_error("Fetch groups failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch groups failed: " . $conn->error);
}

// Fetch all locations
$locations_result = $conn->query("SELECT id, location_name FROM locations");
if ($locations_result === false) {
    log_error("Fetch locations failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch locations failed: " . $conn->error);
}

// Fetch all timeslots
$timeslots_result = $conn->query("SELECT id, CONCAT(start_time, ' - ', end_time) as timeslot FROM timeslots");
if ($timeslots_result === false) {
    log_error("Fetch timeslots failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch timeslots failed: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $group_id = $_POST['group_id'];
    $name_id = $_POST['name_id'];
    $timeslot_id = $_POST['timeslot_id'];
    $location_id = $_POST['location_id'];
    $booking_date = date('Y-m-d');

    // Check if the user already booked the same timeslot and location
    $check_sql = "SELECT COUNT(*) as count FROM bookings WHERE name_id = ? AND timeslot_id = ? AND location_id = ?";
    $stmt = $conn->prepare($check_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the check statement.";
    } else {
        $stmt->bind_param('iii', $name_id, $timeslot_id, $location_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $message = "You have already booked this timeslot and location.";
        } else {
            // Check the booking limit
            $limit_check_sql = "SELECT COUNT(*) as count FROM bookings WHERE timeslot_id = ? AND location_id = ?";
            $stmt = $conn->prepare($limit_check_sql);
            if ($stmt === false) {
                log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                $message = "Error preparing the limit check statement.";
            } else {
                $stmt->bind_param('ii', $timeslot_id, $location_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $booked_count = $row['count'];

                $limit_sql = "SELECT max_limit FROM timeslots WHERE id = ?";
                $stmt = $conn->prepare($limit_sql);
                if ($stmt === false) {
                    log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                    $message = "Error preparing the limit fetch statement.";
                } else {
                    $stmt->bind_param('i', $timeslot_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $max_limit = $row['max_limit'];

                    if ($booked_count >= $max_limit) {
                        $message = "The selected timeslot and location are fully booked.";
                    } else {
                        $insert_sql = "INSERT INTO bookings (group_id, name_id, timeslot_id, location_id, booking_date) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_sql);
                        if ($stmt === false) {
                            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                            $message = "Error preparing the insert statement.";
                        } else {
                            $stmt->bind_param('iiiii', $group_id, $name_id, $timeslot_id, $location_id, $booking_date);
                            if ($stmt->execute()) {
                                header("Location: booking_success.php");
                                exit();
                            } else {
                                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                                $message = "Error booking the slot.";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book a Slot</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        main {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .alert {
            margin-top: 15px;
        }
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
    <script>
        function fetchNames(groupId) {
            if (groupId === "") {
                document.getElementById("name_id").innerHTML = "<option value=''>Select Name</option>";
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_names.php?group_id=" + groupId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("name_id").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Book a Slot</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post" action="booking_page.php">
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" onchange="fetchNames(this.value)" required>
                    <option value="">Select Group</option>
                    <?php while($group = $groups_result->fetch_assoc()): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name_id">Name:</label>
                <select id="name_id" name="name_id" class="form-control" required>
                    <option value="">Select Name</option>
                    <!-- Options will be populated by fetch_names.php -->
                </select>
            </div>
            <div class="form-group">
                <label for="timeslot_id">Time Slot:</label>
                <select id="timeslot_id" name="timeslot_id" class="form-control" required>
                    <option value="">Select Time Slot</option>
                    <?php while($timeslot = $timeslots_result->fetch_assoc()): ?>
                        <option value="<?php echo $timeslot['id']; ?>"><?php echo $timeslot['timeslot']; ?></option>
                    <?php endwhile; ?>
                </select>
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
            <button type="submit" name="book" class="btn btn-primary">Book</button>
        </form>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='index.php'">Back to Home</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>

