<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1aloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
if ($group_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM names WHERE group_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
        }
        $stmt->close();
    }
}

$conn->close();
?>
