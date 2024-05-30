<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $name_id = $_POST['name_id'];
    $report_month = $_POST['report_month'];
    $went_out = isset($_POST['went_out']) ? 1 : 0;
    $studies = $_POST['studies'];
    $hours_worked = $_POST['hours_worked'];
    $bethel_hours = $_POST['bethel_hours'];

    $insert_sql = "INSERT INTO reports (name_id, report_month, went_out, studies, hours_worked, bethel_hours) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the insert statement.";
    } else {
        $stmt->bind_param('isiiii', $name_id, $report_month, $went_out, $studies, $hours_worked, $bethel_hours);
        if ($stmt->execute()) {
            $message = "Successfully submitted the report.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error submitting the report.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Reporting</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        .alert {
            margin-top: 15px;
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
        function fetchNames(groupId) {
            if (groupId === "") {                document.getElementById("name_id").innerHTML = "<option value=''>Select Name</option>";
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_names.php?group_id=" + groupId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("name_id").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Monthly Reporting</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post" action="reporting_page.php">
            <div class="form-group">
                <label for="group_id">Group:</label>
                <select id="group_id" name="group_id" class="form-control" onchange="fetchNames(this.value)" required>
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
                    <!-- Options will be populated by fetch_names.php -->
                </select>
            </div>
            <div class="form-group">
                <label for="report_month">Report Month:</label>
                <input type="month" id="report_month" name="report_month" class="form-control" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="went_out" value="1"> Did you go out this month?
                </label>
            </div>
            <div class="form-group">
                <label for="studies">Number of Studies:</label>
                <input type="number" id="studies" name="studies" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="hours_worked">Hours Worked:</label>
                <input type="number" id="hours_worked" name="hours_worked" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="bethel_hours">Bethel Hours:</label>
                <input type="number" id="bethel_hours" name="bethel_hours" class="form-control">
            </div>
            <button type="submit" name="submit_report" class="btn btn-primary">Submit Report</button>
        </form>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='index.php'">Back to Home</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>

               
