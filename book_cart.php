<?php
session_start();

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

// Fetch all names
$names_result = $conn->query("SELECT id, name FROM names");
if ($names_result === false) {
    log_error("Fetch names failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch names failed: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_cart'])) {
    $name_id = $_POST['name_id'];
    $booking_date = date('Y-m-d');

    // Check if the cart is available
    $check_sql = "SELECT COUNT(*) as count FROM cart_bookings WHERE return_date IS NULL";
    $result = $conn->query($check_sql);
    if ($result === false) {
        log_error("Fetch cart count failed: (" . $conn->errno . ") " . $conn->error);
        die("Fetch cart count failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $booked_count = $row['count'];

    $max_carts = 10; // Total available carts
    if ($booked_count >= $max_carts) {
        $message = "All carts are currently booked.";
    } else {
        $insert_sql = "INSERT INTO cart_bookings (name_id, booking_date) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt === false) {
            log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $message = "Error preparing the insert statement.";
        } else {
            $stmt->bind_param('is', $name_id, $booking_date);
            if ($stmt->execute()) {
                $message = "Successfully booked a cart.";
            } else {
                log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $message = "Error booking the cart.";
            }
            $stmt->close();
        }
    }
}

// Handle return cart submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_cart'])) {
    $booking_id = $_POST['booking_id'];
    $return_date = date('Y-m-d');

    $update_sql = "UPDATE cart_bookings SET return_date = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    if ($stmt === false) {
        log_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $message = "Error preparing the update statement.";
    } else {
        $stmt->bind_param('si', $return_date, $booking_id);
        if ($stmt->execute()) {
            $message = "Successfully returned the cart.";
        } else {
            log_error("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $message = "Error returning the cart.";
        }
        $stmt->close();
    }
}

// Fetch all cart bookings
$cart_bookings_sql = "SELECT cb.id, n.name, cb.booking_date, cb.return_date
                      FROM cart_bookings cb
                      JOIN names n ON cb.name_id = n.id
                      ORDER BY cb.booking_date DESC";
$cart_bookings_result = $conn->query($cart_bookings_sql);
if ($cart_bookings_result === false) {
    log_error("Fetch cart bookings failed: (" . $conn->errno . ") " . $conn->error);
    die("Fetch cart bookings failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book a Cart</title>
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
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Book a Cart</h1>
    </header>
    <main class="container my-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>Book a Cart</h2>
        <form method="post" action="book_cart.php">
            <div class="form-group">
                <label for="name_id">Name:</label>
                <select id="name_id" name="name_id" class="form-control" required>
                    <option value="">Select Name</option>
                    <?php while($name = $names_result->fetch_assoc()): ?>
                        <option value="<?php echo $name['id']; ?>"><?php echo $name['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="book_cart" class="btn btn-primary">Book</button>
        </form>

        <h2 class="mt-5">Booked Carts</h2>
        <?php if ($cart_bookings_result->num_rows > 0): ?>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Booking Date</th>
                        <th>Return Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $cart_bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['return_date'] ? $booking['return_date'] : 'Not Returned'); ?></td>
                            <td>
                                <?php if (!$booking['return_date']): ?>
                                    <form method="post" action="book_cart.php" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="return_cart" class="btn btn-secondary btn-sm">Return</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No carts are currently booked.</p>
        <?php endif; ?>

        <div class="button-container d-flex justify-content-around mt-3">
            <button class="btn btn-secondary" onclick="location.href='index.php'">Back to Home</button>
        </div>
    </main>
    <footer class="bg-primary text-white text-center py-3">
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
