<?php
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['group_id'])) {
    $group_id = $_POST['group_id'];
    $result = $conn->query("SELECT id, name FROM names WHERE group_id = '$group_id'");

    echo '<option value="">Select Name</option>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }
    }
}

$conn->close();
?>
