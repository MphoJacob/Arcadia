<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle addition of a new name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_name'])) {
    $name = $_POST['name'];
    $group_id = $_POST['group_id'];
    $pin = $_POST['pin'];
    $role_id = $_POST['role_id'];
    $add_sql = "INSERT INTO names (name, group_id, pin, role_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($add_sql);
    if ($stmt === false) {
        $message = "Error preparing the add statement.";
    } else {
        $stmt->bind_param('sisi', $name, $group_id, $pin, $role_id);
        if ($stmt->execute()) {
            $message = "Successfully added the name: $name.";
        } else {
            $message = "Error adding the name: $name.";
        }
        $stmt->close();
    }
}

// Handle deletion of a name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $name_id = $_POST['name_id'];
    if (!empty($name_id)) {
        $delete_sql = "DELETE FROM names WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt === false) {
            $message = "Error preparing the delete statement.";
        } else {
            $stmt->bind_param('i', $name_id);
            if ($stmt->execute()) {
                $message = "Successfully deleted the name.";
            } else {
                $message = "Error deleting the name.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid name ID.";
    }
}

// Handle modification of a name's group and role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify'])) {
    $name_id = $_POST['name_id'];
    $new_group_id = $_POST['new_group_id'];
    $new_role_id = $_POST['new_role_id'];
    if (!empty($name_id) && !empty($new_group_id) && !empty($new_role_id)) {
        $modify_sql = "UPDATE names SET group_id = ?, role_id = ? WHERE id = ?";
        $stmt = $conn->prepare($modify_sql);
        if ($stmt === false) {
            $message = "Error preparing the statement.";
        } else {
            $stmt->bind_param('iii', $new_group_id, $new_role_id, $name_id);
            if ($stmt->execute()) {
                $message = "Successfully changed the group and role.";
            } else {
                $message = "Error changing the group and role.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid name ID, group ID, or role ID.";
    }
}

// Fetch all groups
$groups_result = $conn->query("SELECT id, name FROM groups");

// Fetch all roles
$roles_result = $conn->query("SELECT id, role_name FROM roles");

// Fetch names based on search criteria
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';
$search_result = [];
if (!empty($search_name)) {
    $search_query = "SELECT n.id, n.name, n.pin, n.role_id, g.name as group_name, r.role_name
                     FROM names n 
                     JOIN groups g ON n.group_id = g.id
                     JOIN roles r ON n.role_id = r.id
                     WHERE n.name LIKE ?";
    $stmt = $conn->prepare($search_query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $like_search_name = "%" . $search_name . "%";
    $stmt->bind_param('s', $like_search_name);
    $stmt->execute();
    $search_result = $stmt->get_result();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
       <title>Manage Names</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Manage Names</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h2>Add New Name</h2>
        <form method="post" action="add_names.php">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" required>
                    <option value="">Select Group</option>
                    <?php while($group = $groups_result->fetch_assoc()): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="pin">PIN:</label>
                <input type="text" id="pin" name="pin" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role_id">Role:</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <option value="">Select Role</option>
                    <?php while($role = $roles_result->fetch_assoc()): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['role_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="add_name" class="btn btn-primary">Add Name</button>
        </form>

        <h2 class="mt-5">Search Names</h2>
        <form method="get" action="add_names.php">
            <div class="form-group">
                <label for="search_name">Search Name:</label>
                <input type="text" id="search_name" name="search_name" class="form-control" placeholder="Enter name to search" value="<?php echo htmlspecialchars($search_name); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (!empty($search_name) && $search_result && $search_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Group</th>
                        <th>PIN</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($name = $search_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($name['name']); ?></td>
                            <td><?php echo htmlspecialchars($name['group_name']); ?></td>
                            <td><?php echo htmlspecialchars($name['pin']); ?></td>
                            <td><?php echo htmlspecialchars($name['role_name']); ?></td>
                            <td>
                                <form method="post" action="add_names.php" style="display:inline;">
                                    <input type="hidden" name="name_id" value="<?php echo $name['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <form method="post" action="add_names.php" style="display:inline;">
                                    <input type="hidden" name="name_id" value="<?php echo $name['id']; ?>">
                                    <select name="new_group_id" class="form-control d-inline-block w-auto">
                                        <?php
                                        $groups_result->data_seek(0); // Reset the result pointer to the beginning
                                        while($group = $groups_result->fetch_assoc()): ?>
                                            <option value="<?php echo $group['id']; ?>" <?php if ($group['id'] == $name['group_id']) echo 'selected'; ?>><?php echo $group['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <select name="new_role_id" class="form-control d-inline-block w-auto">
                                        <?php
                                        $roles_result->data_seek(0); // Reset the result pointer to the beginning
                                        while($role = $roles_result->fetch_assoc()): ?>
                                            <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $name['role_id']) echo 'selected'; ?>><?php echo $role['role_name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="modify" class="btn btn-secondary btn-sm">Change Group & Role</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif (!empty($search_name)): ?>
            <p>No names found matching "<?php echo htmlspecialchars($search_name); ?>"</p>
        <?php endif; ?>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='admin_panel.php'">Back to Admin Panel</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>

