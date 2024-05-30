<?php
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$groups = $conn->query("SELECT id, name FROM groups");
$locations = $conn->query("SELECT id, location_name FROM locations");
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Booking System</h1>
    </header>
    <main class="container my-5">
        <div class="button-container d-flex justify-content-around mb-4">
            <button class="btn btn-secondary" onclick="location.href='index.php'">Home</button>
        </div>
        <form action="process_booking.php" method="post" class="w-100 mb-4">
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" required>
                    <option value="">Select Group</option>
                    <?php
                    if ($groups->num_rows > 0) {
                        while ($row = $groups->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name_id">Name:</label>
                <select id="name_id" name="name_id" class="form-control" required>
                    <option value="">Select Name</option>
                </select>
            </div>
            <div class="form-group">
                <label for="location_id">Location:</label>
                <select id="location_id" name="location_id" class="form-control" required>
                    <option value="">Select Location</option>
                    <?php
                    if ($locations->num_rows > 0) {
                        while ($row = $locations->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['location_name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="timeslot_id">Time Slot:</label>
                <select id="timeslot_id" name="timeslot_id" class="form-control" required>
                    <option value="">Select Time Slot</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Book</button>
        </form>
        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_login.php'">Admin Login</button>
            <button class="btn btn-secondary" onclick="location.href='view_bookings.php'">View All Bookings</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
    <script>
        $(document).ready(function() {
            $('#group_id').change(function() {
                var group_id = $(this).val();
                if (group_id) {
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_names.php',
                        data: 'group_id=' + group_id,
                        success: function(html) {
                            $('#name_id').html(html);
                        }
                    });
                } else {
                    $('#name_id').html('<option value="">Select Group First</option>');
                }
            });

            $('#location_id').change(function() {
                var location_id = $(this).val();
                if (location_id) {
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_timeslots.php',
                        data: 'location_id=' + location_id,
                        success: function(html) {
                            $('#timeslot_id').html(html);
                        }
                    });
                } else {
                    $('#timeslot_id').html('<option value="">Select Location First</option>');
                }
            });
        });
    </script>
</body>
</html>
