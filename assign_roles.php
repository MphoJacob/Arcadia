<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1aloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch groups, names, and roles from database
$groups_result = $conn->query("SELECT id, name FROM groups");
$roles_result = $conn->query("SELECT id, role_name FROM roles");

// Prepare an associative array of names grouped by group ID
$names_by_group = [];
$names_result = $conn->query("SELECT id, name, group_id FROM names");
while ($row = $names_result->fetch_assoc()) {
    $names_by_group[$row['group_id']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Roles</title>
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
    <script>
        // JavaScript function to populate names based on selected group
        function updateNamesDropdown() {
            var groupDropdown = document.getElementById('group_id');
            var namesDropdown = document.getElementById('name_id');
            var selectedGroupId = groupDropdown.value;

            // Clear current names
            namesDropdown.innerHTML = '';

            // Populate names based on selected group
            if (selectedGroupId in namesByGroup) {
                namesByGroup[selectedGroupId].forEach(function(name) {
                    var option = document.createElement('option');
                    option.value = name.id;
                    option.text = name.name;
                    namesDropdown.add(option);
                });
            }
        }

        // Names grouped by group ID (populated by PHP)
        var namesByGroup = <?php echo json_encode($names_by_group); ?>;
    </script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Assign Roles</h1>
    </header>
    <main class="container my-5">
        <form method="post" action="process_assign_roles.php">
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" onchange="updateNamesDropdown()" required>
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
                    <!-- Names will be populated based on selected group -->
                </select>
            </div>
            <div class="form-group">
                <label for="role_id">Role:</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <?php while($role = $roles_result->fetch_assoc()): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['role_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign Role</button>
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
