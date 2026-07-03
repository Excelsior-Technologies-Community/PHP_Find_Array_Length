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
    </style>

</head>

<body>

    <div class="container py-5">

        <div class="header text-center">

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

                        <h2><?= count($ascending) ?></h2>

                        <h5>Sorted Values</h5>

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

                                <td><?= $value ?></td>

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
                                    <td><?= $value ?></td>
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

        <footer class="text-center text-muted mt-5 mb-3">

            <hr>

            <p class="mb-1">
                🚀 PHP Array Utility Dashboard
            </p>

            <small>
                Built with Core PHP & Bootstrap 5
            </small>

        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>