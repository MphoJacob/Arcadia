<?php
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['location_id'])) {
    $location_id = $_POST['location_id'];
    $query = "SELECT id, start_time, end_time FROM timeslots WHERE location_id = '$location_id'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        echo '<option value="">Select Time Slot</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5) . '</option>';
        }
    } else {
        echo '<option value="">No Time Slots Available</option>';
    }
} else {
    echo '<option value="">Select Location First</option>';
}
$conn->close();
?>