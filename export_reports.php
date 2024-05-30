<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "id22185372_arcadiacong", "Arcadia123%", "id22185372_arcadiacong");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'];
    $sql = "SELECT groups.name AS group_name, names.name AS name,
                   CONCAT(timeslots.start_time, ' to ', timeslots.end_time) AS timeslot,
                   locations.location_name AS location,
                   bookings.booking_date AS date
            FROM bookings
            JOIN groups ON bookings.group_id = groups.id
            JOIN names ON bookings.name_id = names.id
            JOIN timeslots ON bookings.timeslot_id = timeslots.id
            JOIN locations ON bookings.location_id = locations.id
            ORDER BY locations.location_name, timeslots.start_time, timeslots.end_time";
   
    $result = $conn->query($sql);
   
    if ($result->num_rows > 0) {
        if ($format == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=bookings.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Group', 'Name', 'Time Slot', 'Location', 'Date'));
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, array($row['group_name'], $row['name'], $row['timeslot'], $row['location'], $row['date']));
            }
            fclose($output);
            exit();
        } elseif ($format == 'pdf') {
            require_once('tcpdf/tcpdf.php');
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Booking System');
            $pdf->SetTitle('Booked Data');
            $pdf->SetSubject('Booked Data Report');
            $pdf->SetKeywords('TCPDF, PDF, report, booking');

            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->AddPage();

            $current_location = '';
            $current_timeslot = '';

            $html = '<h1>Booked Data Report</h1>';

            while ($row = $result->fetch_assoc()) {
                if ($current_location != $row['location']) {
                    if ($current_location != '') {
                        $html .= '</tbody></table>';
                    }
                    $current_location = $row['location'];
                    $html .= '<h2>' . $current_location . '</h2>';
                    $current_timeslot = ''; // Reset timeslot when location changes
                }
                
                if ($current_timeslot != $row['timeslot']) {
                    if ($current_timeslot != '') {
                        $html .= '</tbody></table>';
                    }
                    $current_timeslot = $row['timeslot'];
                    $html .= '<h3>' . $current_timeslot . '</h3>';
                    $html .= '<table border="1" cellpadding="5">
                                <thead>
                                    <tr>
                                        <th>Group</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>';
                }

                $html .= '<tr>
                            <td>' . $row['group_name'] . '</td>
                            <td>' . $row['name'] . '</td>
                            <td>' . $row['date'] . '</td>
                          </tr>';
            }

            if ($current_location != '') {
                $html .= '</tbody></table>';
            }

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('bookings.pdf', 'I');
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
    <link rel="stylesheet" type="text/css" href="styles.css">
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
        button {
            background: #333;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        button:hover {
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
