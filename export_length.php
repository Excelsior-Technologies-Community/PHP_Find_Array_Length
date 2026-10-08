<?php
// Export Handler for PHP Array Length & Analytics Studio

$format = $_REQUEST['format'] ?? 'json';
$exportDataJson = $_POST['export_data'] ?? '[]';

$data = json_decode($exportDataJson, true);
if (!is_array($data)) {
    $data = [];
}

$filename = "array_length_report_" . date('Ymd_His');

if ($format === 'json') {
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=\"{$filename}.json\"");
    echo json_encode([
        'report_title' => 'PHP Array Length & Structural Analytics Report',
        'generated_at' => date('Y-m-d H:i:s'),
        'total_items' => count($data),
        'items' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Index', 'Value', 'Length (Chars)', 'Byte Size']);

    foreach ($data as $i => $item) {
        $val = is_array($item) ? json_encode($item) : (string)$item;
        fputcsv($output, [
            $i + 1,
            $val,
            mb_strlen($val),
            strlen($val) . ' bytes'
        ]);
    }
    fclose($output);
    exit;
}

if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.xml\"");
    $xml = new SimpleXMLElement('<array_length_report/>');
    $xml->addChild('generated_at', date('Y-m-d H:i:s'));
    $xml->addChild('total_items', (string)count($data));
    $itemsNode = $xml->addChild('items');

    foreach ($data as $i => $item) {
        $val = is_array($item) ? json_encode($item) : (string)$item;
        $node = $itemsNode->addChild('item');
        $node->addChild('index', (string)($i + 1));
        $node->addChild('value', htmlspecialchars($val));
        $node->addChild('length', (string)mb_strlen($val));
    }
    echo $xml->asXML();
    exit;
}

if ($format === 'pdf_print') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Array Length & Structural Analytics Report</title>
        <style>
            body { font-family: system-ui, sans-serif; padding: 30px; background: #fff; color: #0f172a; }
            h1 { color: #2563eb; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
            th { background: #f1f5f9; }
            .badge { background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
        </style>
    </head>
    <body onload="window.print()">
        <h1>📊 Array Length & Structural Analytics Report</h1>
        <p>Report Timestamp: <?php echo date('Y-m-d H:i:s'); ?> | Total Array Items: <?php echo count($data); ?></p>
        <table>
            <thead>
                <tr>
                    <th># Index</th>
                    <th>Value</th>
                    <th>Char Length</th>
                    <th>Byte Size</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $val): 
                    $strVal = is_array($val) ? json_encode($val) : (string)$val;
                ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><code><?php echo htmlspecialchars($strVal); ?></code></td>
                    <td><span class="badge"><?php echo mb_strlen($strVal); ?> chars</span></td>
                    <td><?php echo strlen($strVal); ?> bytes</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
