.name as name,
                   CONCAT(timeslots.start_time, ' to ', timeslots.end_time) as timeslot,
                   locations.location_name as location,
                   bookings.booking_date as date
            FROM bookings
            JOIN groups ON bookings.group_id = groups.id
            JOIN names ON bookings.name_id = names.id
            JOIN timeslots ON bookings.timeslot_id = timeslots.id
            JOIN locations ON bookings.location_id = locations.id
            ORDER BY bookings.booking_date DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($format == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=bookings.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Group', 'Name', 'Time Slot', 'Location', 'Date'));
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit();
        } elseif ($format == 'pdf') {
            require('tcpdf/tcpdf.php');
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Bookings', 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 10, 'Group', 1);
            $pdf->Cell(40, 10, 'Name', 1);
            $pdf->Cell(50, 10, 'Time Slot', 1);
            $pdf->Cell(40, 10, 'Location', 1);
            $pdf->Cell(20, 10, 'Date', 1);
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 10);
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(40, 10, $row['group_name'], 1);
                $pdf->Cell(40, 10, $row['name'], 1);
                $pdf->Cell(50, 10, $row['timeslot'], 1);
                $pdf->Cell(40, 10, $row['location'], 1);
                $pdf->Cell(20, 10, $row['date'], 1);
                $pdf->Ln();
            }
            $pdf->Output('D', 'bookings.pdf');
            exit();
        }
    } else {
        echo "No bookings found.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Export Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Export Reports</h1>
    </header>
    <main>
        <form method="post" action="export_reports.php">
            <label for="format">Select Format:</label>
            <select id="format" name="format">
                <option value="csv">CSV</option>
                <option value="pdf">PDF</option>
            </select>
            <button type="submit" name="export">Export</button>
        </form>
        <div class="button-container">
            <button onclick="location.href='admin_panel.php'">Back to Admin Panel</button>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Booking System. All rights reserved.</p>
    </footer>
</body>
</html>
