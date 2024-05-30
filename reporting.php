<?php
session_start();
$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_id = $_POST['name_id'];
    $pin = $_POST['pin'];

    $sql = "SELECT id, role_id FROM names WHERE id = '$name_id' AND pin = '$pin'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['name_id'] = $name_id;
        $_SESSION['role_id'] = $row['role_id'];
        header("Location: reporting_page.php");
        exit();
    } else {
        $error_message = "Invalid PIN. Please try again.";
    }
}

$names_result = $conn->query("SELECT id, name FROM names");
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reporting</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Reporting</h1>
    </header>
    <main class="container my-5">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" action="reporting.php" class="w-100 mb-4">
            <div class="form-group">
                <label for="name_id">Name:</label>
                <select id="name_id" name="name_id" class="form-control" required>
                    <option value="">Select Name</option>
                    <?php
                    if ($names_result->num_rows > 0) {
                        while ($row = $names_result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="pin">PIN:</label>
                <input type="password" id="pin" name="pin" class="form-control" maxlength="4" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#name_id').select2();
        });
    </script>
</body>
</html>