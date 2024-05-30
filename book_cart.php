<?php
session_start();

$conn = new mysqli("f2fbe0zvg9j8p9ng.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "d0d2pweoaui1aloc", "miqd2lotp3n7o7c6", "vij8oxb41a7lpjg6");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name_id'])) {
        $name_id = $_POST['name_id'];

        // Check if carts are available
        $inventory_result = $conn->query("SELECT total_carts FROM cart_inventory");
        $inventory_row = $inventory_result->fetch_assoc();
        $total_carts = $inventory_row['total_carts'];

        $booked_carts_result = $conn->query("SELECT COUNT(*) as booked_carts FROM cart_bookings WHERE return_date IS NULL");
        $booked_carts_row = $booked_carts_result->fetch_assoc();
        $booked_carts = $booked_carts_row['booked_carts'];

        if ($booked_carts < $total_carts) {
            $sql = "INSERT INTO cart_bookings (name_id, book_date) VALUES ('$name_id', NOW())";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>Cart booked successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>No carts available.</div>";
        }
    } elseif (isset($_POST['return_cart_id'])) {
        $return_cart_id = $_POST['return_cart_id'];
        $sql = "UPDATE cart_bookings SET return_date = NOW() WHERE id = '$return_cart_id'";
        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Cart returned successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

$names_result = $conn->query("SELECT id, name FROM names");
$carts_result = $conn->query("SELECT cb.id, n.name, cb.book_date, cb.return_date FROM cart_bookings cb JOIN names n ON cb.name_id = n.id ORDER BY cb.book_date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Cart</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Book a Cart</h1>
    </header>
    <main class="container my-5">
        <form method="post" action="book_cart.php" class="w-100 mb-4">
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
            <button type="submit" class="btn btn-primary btn-block">Book Cart</button>
        </form>

        <h2>Booked Carts</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Book Date</th>
                    <th>Return Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($carts_result->num_rows > 0) {
                    while ($row = $carts_result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['book_date'] . "</td>
                                <td>" . ($row['return_date'] ? $row['return_date'] : 'Not Returned') . "</td>
                                <td>
                                    <form method='post' action='book_cart.php' style='display:inline;'>
                                        <input type='hidden' name='return_cart_id' value='" . $row['id'] . "'>
                                        <button type='submit' class='btn btn-success btn-sm' " . ($row['return_date'] ? 'disabled' : '') . ">Return</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No carts booked</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
    <script>
        $(document).ready(function() {
            $('#name_id').select2(); // Enhance the name dropdown with select2 for better UX
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>
</html>
