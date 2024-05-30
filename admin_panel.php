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
    <title>Admin Panel</title>
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
        .button-container {
            margin-top: 20px;
            text-align: center;
        }
        button, a.btn {
            background: #333;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            display: block;
            text-align: center;
            width: 100%;
            margin-bottom: 10px;
        }
        button:hover, a.btn:hover {
            background: #555;
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
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Admin Panel</h1>
    </header>
    <main class="container my-5">
        <div class="button-container">
            <h2>Manage Bookings</h2>
            <a href="admin_manage_bookings.php" class="btn">Manage Trolly Bookings</a>
            <a href="add_location.php" class="btn">Add Locations</a>
            <a href="add_timeslot.php" class="btn">Add Time Slots</a>
                        <h2>Manage Territories</h2>
            <a href="manage_territory.php" class="btn">Manage Territory</a>
            
            <h2>Manage Names and Roles</h2>
            <a href="add_names.php" class="btn">Add Names</a>
            <a href="assign_roles.php" class="btn">Assign Roles</a>
            
            <h2>Reports</h2>
            <a href="view_reports.php" class="btn">View Reports</a>
            <a href="export_reports.php" class="btn">Export Reports</a>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>

