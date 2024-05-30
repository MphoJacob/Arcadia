<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Booking Limit</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Set Booking Limit</h1>
    </header>
    <main class="container my-5">
        <form method="post" action="process_set_booking_limit.php">
            <div class="form-group">
                <label for="booking_limit">Booking Limit:</label>
                <input type="number" id="booking_limit" name="booking_limit" class="form-control" required>
            </div>
                      <button type="submit" class="btn btn-primary">Set Limit</button>
        </form>
        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_panel.php'">Back to Admin Panel</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>

