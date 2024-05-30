<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['total_carts'])) {
    $total_carts = intval($_POST['total_carts']);
    $sql = "UPDATE cart_inventory SET total_carts = '$total_carts' WHERE id = 1";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Total carts updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating total carts: " . $conn->error . "</div>";
    }
}

// Fetch current total carts
$inventory_result = $conn->query("SELECT total_carts FROM cart_inventory WHERE id = 1");
$inventory_row = $inventory_result->fetch_assoc();
$current_total_carts = $inventory_row['total_carts'];

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adjust Total Carts</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Adjust Total Carts</h1>
    </header>
    <main class="container my-5">
        <form method="post" action="admin_adjust_carts.php" class="w-100 mb-4">
            <div class="form-group">
                <label for="total_carts">Total Carts:</label>
                <input type="number" id="total_carts" name="total_carts" class="form-control" value="<?php echo $current_total_carts; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update Total Carts</button>
        </form>
        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_panel.php'">Back to Admin Panel</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
