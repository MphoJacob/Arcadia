<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

function log_error($message) {
    error_log($message);
    echo "<div class='alert alert-danger'>$message</div>";
}

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1aloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    log_error("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Fetch all groups
$groups_result = $conn->query("SELECT id, name FROM groups");
if ($groups_result === false) {
    log_error("Fetch groups failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch groups failed: " . $conn->error);
}

// Handle addition of a new name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_name'])) {
    $group_id = $_POST['group_id'];
    $name = $_POST['name'];
    $pin = '0000'; // Default pin

    $add_sql = "INSERT INTO names (group_id, name, pin) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($add_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the add statement.";
    } else {
        $stmt->bind_param('iss', $group_id, $name, $pin);
        if ($stmt->execute()) {
            $message = "Successfully added the name: $name.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error adding the name: $name.";
        }
        $stmt->close();
    }
}

// Handle deletion of a name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_name'])) {
    $name_id = $_POST['name_id'];
    if (!empty($name_id)) {
        $delete_sql = "DELETE FROM names WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the delete statement.";
        } else {
            $stmt->bind_param('i', $name_id);
            if ($stmt->execute()) {
                $message = "Successfully deleted the name.";
            } else {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error deleting the name.";
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid name ID.";
    }
}

// Fetch all names
$names_result = $conn->query("SELECT id, group_id, name FROM names");
if ($names_result === false) {
    log_error("Fetch names failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch names failed: " . $conn->error);
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
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" required>
                    <option value="">Select Group</option>
                    <?php while($group = $groups_result->fetch_assoc()): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <button type="submit" name="add_name" class="btn btn-primary">Add Name</button>
        </form>

        <h2 class="mt-5">Existing Names</h2>
        <form method="post" action="add_names.php">
            <div class="form-group">
                <label for="search_name">Search Name to Delete:</label>
                <input type="text" id="search_name" name="search_name" class="form-control">
            </div>
            <button type="submit" name="search_name_btn" class="btn btn-primary">Search</button>
        </form>

        <?php if ($names_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($name = $names_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($name['group_id']); ?></td>
                            <td><?php echo htmlspecialchars($name['name']); ?></td>
                            <td>
                                <form method="post" action="add_names.php" style="display:inline;">
                                    <input type="hidden" name="name_id" value="<?php echo $name['id']; ?>">
                                    <button type="submit" name="delete_name" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No names found.</p>
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
