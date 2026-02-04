<?php
function exportSensorData($type, $start_date = '', $end_date = '') {
    global $conn;
    
    // Set headers based on export type
    switch ($type) {
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=sensor_data_' . date('Y-m-d') . '.csv');
            
            // Create a file pointer connected to the output stream
            $output = fopen('php://output', 'w');
            
            // Output the column headings (no BOM or comments for Edge Impulse)
            fputcsv($output, ['timestamp', 'moisture', 'temperature', 'humidity']);
            
            // Build the query with date filters
            $where_conditions = [];
            if (!empty($start_date)) {
                $where_conditions[] = "DATE(reading_time) >= '" . $conn->real_escape_string($start_date) . "'";
            }
            if (!empty($end_date)) {
                $end_date_obj = new DateTime($end_date);
                $end_date_obj->modify('+1 day'); // Include the entire end date
                $where_conditions[] = "DATE(reading_time) < '" . $end_date_obj->format('Y-m-d') . "'";
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Get data and output rows
            $query = "SELECT reading_time, moisture, temperature, humidity 
                     FROM sensor_data 
                     $where_clause 
                     ORDER BY reading_time ASC";
            $result = $conn->query($query);
            
            // Output data rows
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['reading_time'],
                    number_format($row['moisture'], 2, '.', ''),
                    number_format($row['temperature'], 2, '.', ''),
                    number_format($row['humidity'], 2, '.', '')
                ]);
            }
            
            fclose($output);
            exit();
            
        case 'xls':
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename=sensor_data_' . date('Y-m-d') . '.xls');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo "<table border='1'>";
            echo "<tr>
                    <th>Timestamp</th>
                    <th>Moisture (%)</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                  </tr>";
            break;
            
        case 'pdf':
            require_once('TCPDF-main/TCPDF-main/tcpdf.php');
            
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('IoT Dashboard');
            $pdf->SetAuthor('IoT Dashboard');
            $pdf->SetTitle('SMART IRRIGATION SYSTEM (INDOOR)');
            $pdf->SetSubject('SOIL & HUMIDITY AND TEMPERATURE LIVE DATA');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, 'SMART IRRIGATION SYSTEM (INDOOR)', 'Generated on ' . date('Y-m-d H:i:s'));
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('helvetica', '', 10);
            
            // Add title
            $pdf->Cell(0, 10, 'SOIL & HUMIDITY AND TEMPERATURE LIVE DATA', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Add date range if specified
            if (!empty($start_date) || !empty($end_date)) {
                $date_range = 'Date Range: ';
                $date_range .= !empty($start_date) ? 'From ' . $start_date : '';
                $date_range .= !empty($end_date) ? ' To ' . $end_date : '';
                $pdf->Cell(0, 10, $date_range, 0, 1);
                $pdf->Ln(5);
            }
            
            // Create table header
            $html = '<table border="1" cellpadding="3">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Moisture (%)</th>
                                <th>Temperature (°C)</th>
                                <th>Humidity (%)</th>
                            </tr>
                        </thead>
                        <tbody>';
            break;
            
        default:
            die('Invalid export type');
    }
    
    // Build the query with date filters
    $where_conditions = [];
    if (!empty($start_date)) {
        $where_conditions[] = "DATE(reading_time) >= '" . $conn->real_escape_string($start_date) . "'";
    }
    if (!empty($end_date)) {
        $where_conditions[] = "DATE(reading_time) <= '" . $conn->real_escape_string($end_date) . "'";
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get data
    $query = "SELECT * FROM sensor_data $where_clause ORDER BY reading_time DESC";
    $result = $conn->query($query);
    
    // Output data rows
    while ($row = $result->fetch_assoc()) {
        $timestamp = $row['reading_time'];
        $moisture = number_format($row['moisture'], 2);
        $temperature = number_format($row['temperature'], 2);
        $humidity = number_format($row['humidity'], 2);
        //$light = $row['light'] ? 'On' : 'Off';
        //$water_level = $row['water_level'] ? 'Full' : 'Low';
        
        switch ($type) {
            case 'csv':
                fputcsv($output, [
                    $timestamp,
                    $moisture,
                    $temperature,
                    $humidity
                ]);
                break;
                
            case 'xls':
                echo "<tr>";
                echo "<td>" . htmlspecialchars($timestamp) . "</td>";
                echo "<td>" . $moisture . "</td>";
                echo "<td>" . $temperature . "</td>";
                echo "<td>" . $humidity . "</td>";
                echo "</tr>";
                break;
                
            case 'pdf':
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($timestamp) . '</td>';
                $html .= '<td>' . $moisture . '</td>';
                $html .= '<td>' . $temperature . '</td>';
                $html .= '<td>' . $humidity . '</td>';
                $html .= '</tr>';
                break;
        }
    }
    
    // Close the output based on type
    switch ($type) {
        case 'csv':
            fclose($output);
            break;
            
        case 'xls':
            echo "</table>";
            break;
            
        case 'pdf':
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->Output('sensor_data_' . date('Y-m-d') . '.pdf', 'D');
            break;
    }
    
    exit();
}
?>
