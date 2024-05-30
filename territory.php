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
    <title>Manage Territory</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Manage Territory</h1>
    </header>
    <main class="container my-5">
        <form method="post" action="process_manage_territory.php">
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" required>
                    <!-- Populate with groups from database -->
                </select>
            </div>
            <div class="form-group">
                <label for="territory_number">Territory Number:</label>
                <select id="territory_number" name="territory_number" class="form-control" required>
                    <option value="1">Territory 1</option>
                    <option value="2">Territory 2</option>
                    <option value="3">Territory 3</option>
                    <option value="4">Territory 4</option>
                    <option value="5">Territory 5</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Manage Territory</button>
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
