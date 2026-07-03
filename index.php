<?php

// ===========================================
// PHP Array Utility Dashboard
// ===========================================

// Sample Array
$array = ['One', 'Two', 'Three', 'Four', 'Five', 'One', 'Two', 'Six', 'Seven', 'Three', 'Five'];


// Search Value
$search = "";

if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
}
// Search
$searchFound = false;
$searchResults = [];

if (!empty($search)) {

    foreach ($array as $index => $value) {

        if (strcasecmp($value, $search) == 0) {

            $searchResults[] = [
                'index' => $index + 1,
                'value' => $value
            ];
        }
    }

    $searchFound = count($searchResults) > 0;
}
// Statistics
$totalElements = count($array);

$uniqueArray = array_unique($array);
$uniqueCount = count($uniqueArray);

$duplicateCount = $totalElements - $uniqueCount;

// Unique Ratio
$uniqueRatio = round(($uniqueCount / $totalElements) * 100, 2);

// Search
$searchFound = false;

if ($search != "") {
    $searchFound = in_array($search, $array);
}

// Duplicate Values
$duplicates = [];

foreach (array_count_values($array) as $value => $count) {
    if ($count > 1) {
        $duplicates[] = $value;
    }
}

// Sorted Arrays
$ascending = $uniqueArray;
sort($ascending);

$descending = $uniqueArray;
rsort($descending);

// Reverse Array
$reverseArray = array_reverse($array);

// First & Last
$firstElement = reset($array);
$lastElement = end($array);

// ===========================================
// Array Frequency Analyzer
// ===========================================

$frequencyData = array_count_values($array);

arsort($frequencyData); // Highest occurrence first

$mostRepeatedValue = array_key_first($frequencyData);
$mostRepeatedCount = reset($frequencyData);

$leastRepeatedData = $frequencyData;
asort($leastRepeatedData);

$leastRepeatedValue = array_key_first($leastRepeatedData);
$leastRepeatedCount = reset($leastRepeatedData);

// ===========================================
// CSV Export
// ===========================================

if (isset($_GET['export']) && $_GET['export'] == 'csv') {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="array_frequency_report.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Value',
        'Occurrences',
        'Percentage'
    ]);

    foreach ($frequencyData as $value => $count) {

        $percentage = round(($count / $totalElements) * 100, 2) . '%';

        fputcsv($output, [
            $value,
            $count,
            $percentage
        ]);
    }

    fclose($output);
    exit;
}

// ===========================================
// Character Statistics
// ===========================================

$totalCharacters = 0;

$longestValue = "";
$shortestValue = $array[0];

foreach ($array as $value) {

    $length = strlen($value);

    $totalCharacters += $length;

    if ($length > strlen($longestValue)) {
        $longestValue = $value;
    }

    if ($length < strlen($shortestValue)) {
        $shortestValue = $value;
    }
}

$averageLength = round($totalCharacters / count($array), 2);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PHP Array Utility Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef2f7;
            font-family: Arial, Helvetica, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            padding: 35px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
        }

        .card-stat {
            border: none;
            border-radius: 15px;
            transition: .3s;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
        }

        .card-stat:hover {
            transform: translateY(-5px);
        }

        .card-stat h2 {
            font-size: 38px;
            font-weight: bold;
        }

        .table {
            background: white;
        }

        .badge-custom {
            font-size: 14px;
            padding: 8px 14px;
        }

        .progress {
            height: 22px;
            border-radius: 20px;
        }

        .progress-bar {
            font-weight: bold;
        }

        .export-btn {
            float: right;
        }

        .analytics-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
        }

        .analytics-small-card {
            border: none;
            border-radius: 15px;
            transition: .3s;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
        }

        .analytics-small-card:hover {
            transform: translateY(-5px);
        }
    </style>

</head>

<body>

    <div class="container py-5">

        <div class="header text-center position-relative">

            <a href="?export=csv"
                class="btn btn-light export-btn fw-bold">
                📥 Download CSV Report
            </a>

            <h1 class="fw-bold">
                📦 PHP Array Utility Dashboard
            </h1>

            <p class="mb-0">
                Professional Array Operations using Core PHP
            </p>

        </div>

        <!-- Dashboard Cards -->

        <div class="row g-4 mb-4">

            <div class="col-md-3">

                <div class="card card-stat bg-primary text-white">

                    <div class="card-body text-center">

                        <h2><?= $totalElements ?></h2>

                        <h5>Total Elements</h5>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card card-stat bg-success text-white">

                    <div class="card-body text-center">

                        <h2><?= $uniqueCount ?></h2>

                        <h5>Unique Values</h5>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card card-stat bg-danger text-white">

                    <div class="card-body text-center">

                        <h2><?= $duplicateCount ?></h2>

                        <h5>Duplicate Values</h5>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card card-stat bg-warning text-dark">

                    <div class="card-body text-center">

                        <h2><?= $uniqueRatio ?>%</h2>

                        <h5>Unique Ratio</h5>

                    </div>

                </div>

            </div>

        </div>

        <!-- Array Analytics -->

        <div class="row mb-4">

            <div class="col-md-6">

                <div class="card analytics-card bg-success text-white">

                    <div class="card-body text-center">

                        <h5 class="mb-3">
                            📈 Most Repeated Value
                        </h5>

                        <h2>
                            <?= htmlspecialchars($mostRepeatedValue) ?>
                        </h2>

                        <span class="badge bg-light text-dark fs-6">
                            <?= $mostRepeatedCount ?> occurrence(s)
                        </span>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card analytics-card bg-warning">

                    <div class="card-body text-center">

                        <h5 class="mb-3">
                            📉 Least Repeated Value
                        </h5>

                        <h2>
                            <?= htmlspecialchars($leastRepeatedValue) ?>
                        </h2>

                        <span class="badge bg-dark fs-6">
                            <?= $leastRepeatedCount ?> occurrence(s)
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <!-- Search -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-dark text-white">

                <h5 class="mb-0">
                    🔍 Search Value
                </h5>

            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="row">

                        <div class="col-md-10">

                            <input type="text" name="search" class="form-control" placeholder="Enter value..."
                                value="<?= htmlspecialchars($search) ?>">

                        </div>

                        <div class="col-md-2 d-grid">

                            <button class="btn btn-primary">

                                Search

                            </button>

                        </div>

                    </div>

                </form>

                <?php if ($search != ""): ?>

                    <hr>

                    <?php if ($searchFound): ?>

                        <div class="alert alert-success mt-3">

                            ✅
                            <strong><?= htmlspecialchars($search) ?></strong>
                            exists in the array.

                        </div>

                    <?php else: ?>

                        <div class="alert alert-danger mt-3">

                            ❌
                            <strong><?= htmlspecialchars($search) ?></strong>
                            does not exist.

                        </div>

                    <?php endif; ?>

                <?php endif; ?>


                <?php if (!empty($search)): ?>

                    <hr>

                    <?php if ($searchFound): ?>

                        <div class="alert alert-success mt-3">
                            ✅ <strong><?= htmlspecialchars($search) ?></strong>
                            found <strong><?= count($searchResults) ?></strong> time(s).
                        </div>

                        <table class="table table-bordered table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>#</th>
                                    <th>Position</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $key => $row): ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= $row['index'] ?></td>
                                        <td><?= $row['value'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php else: ?>

                        <div class="alert alert-danger mt-3">
                            ❌ <strong><?= htmlspecialchars($search) ?></strong> was not found.
                        </div>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

        <!-- Original Array -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    📋 Original Array

                </h5>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th width="80">#</th>

                            <th>Value</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($array as $key => $value): ?>

                            <tr>

                                <td><?= $key + 1 ?></td>

                                <td>

                                    <?php if (in_array($value, $duplicates)): ?>

                                        <span class="badge bg-danger">

                                            <?= htmlspecialchars($value) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">

                                            <?= htmlspecialchars($value) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Unique Array -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-success text-white">
                <h5 class="mb-0">⭐ Unique Values</h5>
            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover">

                    <thead class="table-success">

                        <tr>
                            <th width="80">#</th>
                            <th>Value</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($uniqueArray as $key => $value): ?>

                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= $value ?></td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Duplicate Values -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">🔁 Duplicate Values</h5>
            </div>

            <div class="card-body">

                <?php if (count($duplicates) > 0): ?>

                    <table class="table table-bordered table-hover">

                        <thead class="table-danger">

                            <tr>
                                <th width="80">#</th>
                                <th>Value</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($duplicates as $key => $value): ?>

                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>

                                        <span class="badge bg-danger">

                                            <?= htmlspecialchars($value) ?>

                                        </span>

                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="alert alert-success">
                        No duplicate values found.
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <!-- Array Frequency Analyzer -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    📊 Array Frequency Analyzer
                </h5>

                <span class="badge bg-light text-dark">
                    <?= count($frequencyData) ?> Unique Values
                </span>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-info">

                        <tr>

                            <th width="70">#</th>

                            <th>Value</th>

                            <th width="140">Occurrences</th>

                            <th width="140">Percentage</th>

                            <th width="300">Visualization</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $i = 1;

                        foreach ($frequencyData as $value => $count):

                            $percentage = round(($count / $totalElements) * 100, 2);
                        ?>

                            <tr>

                                <td><?= $i++ ?></td>

                                <td>

                                    <strong><?= htmlspecialchars($value) ?></strong>

                                </td>

                                <td>

                                    <span class="badge bg-primary">

                                        <?= $count ?>

                                    </span>

                                </td>

                                <td>

                                    <?= $percentage ?>%

                                </td>

                                <td>

                                    <div class="progress">

                                        <div class="progress-bar"

                                            role="progressbar"

                                            style="width: <?= $percentage ?>%;">

                                            <?= $percentage ?>%

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Character Statistics -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">
                    🔤 Character Statistics
                </h5>

            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-3">

                        <div class="card analytics-small-card bg-primary text-white">

                            <div class="card-body text-center">

                                <h2><?= $totalCharacters ?></h2>

                                <h6>Total Characters</h6>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="card analytics-small-card bg-success text-white">

                            <div class="card-body text-center">

                                <h2><?= $averageLength ?></h2>

                                <h6>Average Length</h6>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="card analytics-small-card bg-warning text-dark">

                            <div class="card-body text-center">

                                <h2><?= htmlspecialchars($longestValue) ?></h2>

                                <small>

                                    <?= strlen($longestValue) ?>

                                    Characters

                                </small>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="card analytics-small-card bg-danger text-white">

                            <div class="card-body text-center">

                                <h2><?= htmlspecialchars($shortestValue) ?></h2>

                                <small>

                                    <?= strlen($shortestValue) ?>

                                    Characters

                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Character Length Details -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-dark text-white">

                <h5 class="mb-0">

                    📋 Character Length Analysis

                </h5>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Value</th>

                            <th>Characters</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($array as $index => $value): ?>

                            <tr>

                                <td><?= $index + 1 ?></td>

                                <td><?= htmlspecialchars($value) ?></td>

                                <td>

                                    <span class="badge bg-info">

                                        <?= strlen($value) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <div class="row">

            <!-- Ascending -->

            <div class="col-lg-6 mb-4">

                <div class="card shadow border-0 h-100">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">⬆ Ascending Order</h5>

                    </div>

                    <div class="card-body">

                        <table class="table table-striped">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Value</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($ascending as $key => $value): ?>

                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= $value ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- Descending -->

            <div class="col-lg-6 mb-4">

                <div class="card shadow border-0 h-100">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">⬇ Descending Order</h5>

                    </div>

                    <div class="card-body">

                        <table class="table table-striped">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Value</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($descending as $key => $value): ?>

                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= $value ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        <!-- Reverse Array -->

        <div class="card shadow border-0 mb-4">

            <div class="card-header bg-warning">

                <h5 class="mb-0">🔄 Reverse Array</h5>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead class="table-warning">

                        <tr>
                            <th>#</th>
                            <th>Value</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($reverseArray as $key => $value): ?>

                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= $value ?></td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- First & Last Element -->

        <div class="row mb-4">

            <div class="col-md-6">

                <div class="card bg-info text-white shadow border-0">

                    <div class="card-body text-center">

                        <h2><?= $firstElement ?></h2>

                        <h5>🎯 First Element</h5>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card bg-secondary text-white shadow border-0">

                    <div class="card-body text-center">

                        <h2><?= $lastElement ?></h2>

                        <h5>🏁 Last Element</h5>

                    </div>

                </div>

            </div>

        </div>

        <footer class="text-center mt-5">

            <hr>

            <h5 class="fw-bold">

                🚀 PHP Array Utility Dashboard

            </h5>

            <p class="text-muted mb-1">

                Built using Core PHP & Bootstrap 5

            </p>

            <small class="text-secondary">

                Includes Search, Duplicate Detection, Sorting,
                Reverse Array, Frequency Analysis,
                Statistics Dashboard and CSV Export.

            </small>

        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>