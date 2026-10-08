<?php
// PHP Enterprise Array Length, Depth & Structural Profiler Studio

$input = $_POST['array_input'] ?? "One, Two, Three, Four, Five, One, Two, Six, Seven, Three, Five, Supercalifragilisticexpialidocious, AI";
$inputFormat = $_POST['input_format'] ?? 'raw'; // raw, json, serialized
$delimiter = $_POST['delimiter'] ?? ',';
$searchQuery = trim($_POST['search_query'] ?? '');
$minLenFilter = isset($_POST['min_len_filter']) ? intval($_POST['min_len_filter']) : 0;
$maxLenFilter = isset($_POST['max_len_filter']) ? intval($_POST['max_len_filter']) : 999;

$array = [];
$nestedStructure = null;
$maxDepth = 1;
$leafNodeCount = 0;
$totalKeyCount = 0;
$isNested = false;

// 1. FILE OR TEXT PARSER
if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['file_upload']['tmp_name'];
    $fileName = $_FILES['file_upload']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $content = file_get_contents($tmpName);
    $input = $content;

    if ($ext === 'json') {
        $inputFormat = 'json';
    }
}

if ($inputFormat === 'json') {
    $decoded = json_decode($input, true);
    if (is_array($decoded)) {
        $nestedStructure = $decoded;
        $isNested = true;
        // Flatten array for string statistics
        $array = flattenArrayValues($decoded);
    }
} elseif ($inputFormat === 'serialized') {
    $unserialized = @unserialize($input);
    if (is_array($unserialized)) {
        $nestedStructure = $unserialized;
        $isNested = true;
        $array = flattenArrayValues($unserialized);
    }
}

if (!$isNested && $input !== '') {
    if ($delimiter === 'newline') {
        $rawItems = explode("\n", str_replace("\r", "", $input));
    } elseif ($delimiter === 'pipe') {
        $rawItems = explode('|', $input);
    } else {
        $rawItems = explode(',', $input);
    }
    $array = array_map('trim', array_filter($rawItems, fn($v) => trim($v) !== ''));
}

// Helper Functions for Recursive Depth & Leaf Node Counting
function getArrayMaxDepth(array $arr): int {
    $maxDepth = 1;
    foreach ($arr as $val) {
        if (is_array($val)) {
            $depth = getArrayMaxDepth($val) + 1;
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
        }
    }
    return $maxDepth;
}

function countLeafNodes($arr): int {
    $count = 0;
    if (!is_array($arr)) return 1;
    foreach ($arr as $val) {
        if (is_array($val)) {
            $count += countLeafNodes($val);
        } else {
            $count++;
        }
    }
    return $count;
}

function countTotalKeys($arr): int {
    $count = 0;
    if (!is_array($arr)) return 0;
    foreach ($arr as $key => $val) {
        $count++;
        if (is_array($val)) {
            $count += countTotalKeys($val);
        }
    }
    return $count;
}

function flattenArrayValues($arr): array {
    $result = [];
    if (!is_array($arr)) return [$arr];
    foreach ($arr as $val) {
        if (is_array($val)) {
            $result = array_merge($result, flattenArrayValues($val));
        } else {
            $result[] = (string)$val;
        }
    }
    return $result;
}

if ($isNested && $nestedStructure) {
    $maxDepth = getArrayMaxDepth($nestedStructure);
    $leafNodeCount = countLeafNodes($nestedStructure);
    $totalKeyCount = countTotalKeys($nestedStructure);
} else {
    $leafNodeCount = count($array);
    $totalKeyCount = count($array);
}

// Apply Length-based Filter
$filteredArray = [];
foreach ($array as $val) {
    $len = mb_strlen((string)$val);
    if ($len >= $minLenFilter && $len <= $maxLenFilter) {
        if ($searchQuery !== '' && strcasecmp((string)$val, $searchQuery) !== 0 && stripos((string)$val, $searchQuery) === false) {
            continue;
        }
        $filteredArray[] = (string)$val;
    }
}

// 2. STATISTICAL LENGTH METRICS & OUTLIER DETECTION
$lengths = array_map('mb_strlen', $filteredArray);
$totalElements = count($filteredArray);
$totalChars = array_sum($lengths);
$totalBytes = array_sum(array_map('strlen', $filteredArray));

$minLength = !empty($lengths) ? min($lengths) : 0;
$maxLength = !empty($lengths) ? max($lengths) : 0;
$avgLength = $totalElements > 0 ? round($totalChars / $totalElements, 2) : 0;

// Median Calculation
$medianLength = 0;
if (!empty($lengths)) {
    $sortedLengths = $lengths;
    sort($sortedLengths);
    $middle = floor(count($sortedLengths) / 2);
    if (count($sortedLengths) % 2 === 0) {
        $medianLength = ($sortedLengths[$middle - 1] + $sortedLengths[$middle]) / 2;
    } else {
        $medianLength = $sortedLengths[$middle];
    }
}

// Mode Length Calculation
$modeLength = 0;
if (!empty($lengths)) {
    $lengthCounts = array_count_values($lengths);
    arsort($lengthCounts);
    $modeLength = array_key_first($lengthCounts);
}

// Standard Deviation & Outlier Watchdog
$stdDev = 0;
$outliers = [];
$hiddenWhitespaceWarnings = [];

if ($totalElements > 1) {
    $varianceSum = 0;
    foreach ($lengths as $l) {
        $varianceSum += pow($l - $avgLength, 2);
    }
    $stdDev = round(sqrt($varianceSum / $totalElements), 2);

    foreach ($filteredArray as $item) {
        $l = mb_strlen($item);
        // Outlier if > 2 std deviations from mean
        if (abs($l - $avgLength) > (2 * $stdDev) && $stdDev > 0) {
            $outliers[] = ['item' => $item, 'length' => $l];
        }
        // Whitespace Warning
        if (trim($item) !== $item || preg_match('/[\x00-\x1F\x7F]/', $item)) {
            $hiddenWhitespaceWarnings[] = $item;
        }
    }
}

// Spectrum Histogram Data (1-5, 6-10, 11-20, 20+)
$spectrum = ['1-5 chars' => 0, '6-10 chars' => 0, '11-20 chars' => 0, '20+ chars' => 0];
foreach ($lengths as $l) {
    if ($l <= 5) $spectrum['1-5 chars']++;
    elseif ($l <= 10) $spectrum['6-10 chars']++;
    elseif ($l <= 20) $spectrum['11-20 chars']++;
    else $spectrum['20+ chars']++;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterprise PHP Array Length & Structural Analytics Studio</title>

    <!-- Bootstrap 5 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #2563eb;
            --bg-light: #f8fafc;
            --card-radius: 14px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }

        .studio-card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            background: #ffffff;
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            color: #ffffff;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .nav-pills .nav-link {
            border-radius: 50rem;
            padding: 10px 22px;
            font-weight: 600;
            color: #475569;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }

        .heatmap-badge-short { background-color: #dcfce7; color: #15803d; }
        .heatmap-badge-med { background-color: #dbeafe; color: #1e40af; }
        .heatmap-badge-long { background-color: #ffedd5; color: #c2410c; }
        .heatmap-badge-xl { background-color: #fee2e2; color: #b91c1c; }
    </style>
</head>

<body>

    <div class="container py-4">

        <!-- Top Header & Branding -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold m-0 text-primary">
                    <i class="fa-solid fa-ruler-combined me-2"></i>PHP Array Length & Structural Analytics Studio
                </h2>
                <p class="text-muted small m-0">Deep Nested Depth Profiling, Length Spectrum Analytics, Outlier Watchdog & Heatmap Exporter</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="fa-solid fa-microchip me-1"></i>Memory & Depth Inspector Active
            </span>
        </div>

        <!-- Studio Navigation Tabs -->
        <ul class="nav nav-pills mb-4 bg-white p-2 studio-card d-flex gap-2" id="studioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="input-tab" data-bs-toggle="pill" data-bs-target="#tab-input" type="button" role="tab">
                    <i class="fa-solid fa-code me-2"></i>Module 1: Depth & Input Profiler
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#tab-analytics" type="button" role="tab">
                    <i class="fa-solid fa-chart-column me-2"></i>Module 2: Length Histogram & Watchdog
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="heatmap-tab" data-bs-toggle="pill" data-bs-target="#tab-heatmap" type="button" role="tab">
                    <i class="fa-solid fa-table-cells me-2"></i>Module 3: Length Heatmap & Exporter
                </button>
            </li>
        </ul>

        <form method="POST" enctype="multipart/form-data">
            <div class="tab-content mb-4" id="studioTabsContent">

                <!-- TAB 1: DEPTH & INPUT PROFILER -->
                <div class="tab-pane fade show active" id="tab-input" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card studio-card p-4">
                                <h4 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-lines me-2"></i>Input Array Data</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Enter Array / JSON / Code Input</label>
                                    <textarea name="array_input" class="form-control font-monospace" rows="6" placeholder="One, Two, Three, Four, Five..."><?php echo htmlspecialchars($input); ?></textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Input Format Type</label>
                                        <select name="input_format" class="form-select">
                                            <option value="raw" <?php if ($inputFormat === 'raw') echo 'selected'; ?>>Raw Delimited Text</option>
                                            <option value="json" <?php if ($inputFormat === 'json') echo 'selected'; ?>>JSON Array / Nested Tree</option>
                                            <option value="serialized" <?php if ($inputFormat === 'serialized') echo 'selected'; ?>>Serialized PHP String</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Delimiter (For Raw Text)</label>
                                        <select name="delimiter" class="form-select">
                                            <option value="," <?php if ($delimiter === ',') echo 'selected'; ?>>Comma (,)</option>
                                            <option value="newline" <?php if ($delimiter === 'newline') echo 'selected'; ?>>New Line (\n)</option>
                                            <option value="pipe" <?php if ($delimiter === 'pipe') echo 'selected'; ?>>Pipe (|)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Upload File (JSON / CSV / TXT)</label>
                                    <input type="file" name="file_upload" class="form-control" accept=".json, .csv, .txt">
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow">
                                    <i class="fa-solid fa-calculator me-2"></i>Profile Array Length & Depth
                                </button>
                            </div>
                        </div>

                        <!-- Right Column: Structural & Memory Gauges -->
                        <div class="col-lg-5">
                            <div class="card studio-card p-4">
                                <h4 class="fw-bold text-primary mb-3"><i class="fa-solid fa-sitemap me-2"></i>Structural & Memory Footprint</h4>

                                <div class="row g-2 mb-3 text-center">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-4 border">
                                            <small class="text-muted fw-semibold">RECURSIVE DEPTH</small>
                                            <h2 class="fw-bold text-primary m-0"><?php echo $maxDepth; ?> Level</h2>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-4 border">
                                            <small class="text-muted fw-semibold">LEAF NODES</small>
                                            <h2 class="fw-bold text-success m-0"><?php echo $leafNodeCount; ?></h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 bg-light rounded-4 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-muted small"><i class="fa-solid fa-memory me-1"></i>String Payload Byte Size</span>
                                        <span class="badge bg-primary"><?php echo number_format($totalBytes); ?> Bytes</span>
                                    </div>
                                    <div class="progress" style="height: 12px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo min(100, ($totalBytes / 2048) * 100); ?>%;"></div>
                                    </div>
                                </div>

                                <div class="p-3 bg-light rounded-4 border">
                                    <label class="form-label fw-semibold text-primary"><i class="fa-solid fa-filter me-2"></i>Dynamic Length Range Filter</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small">Min Length</label>
                                            <input type="number" name="min_len_filter" class="form-control form-control-sm" value="<?php echo $minLenFilter; ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Max Length</label>
                                            <input type="number" name="max_len_filter" class="form-control form-control-sm" value="<?php echo $maxLenFilter; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: LENGTH HISTOGRAM & WATCHDOG -->
                <div class="tab-pane fade" id="tab-analytics" role="tabpanel">
                    <!-- Stat Cards -->
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-primary p-3 shadow-sm">
                                <small class="text-white-50">ELEMENTS</small>
                                <h3 class="fw-bold m-0"><?php echo $totalElements; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-info p-3 shadow-sm">
                                <small class="text-white-50">AVG LENGTH</small>
                                <h3 class="fw-bold m-0"><?php echo $avgLength; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-success p-3 shadow-sm">
                                <small class="text-white-50">MEDIAN</small>
                                <h3 class="fw-bold m-0"><?php echo $medianLength; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-warning p-3 shadow-sm">
                                <small class="text-white-50">MODE</small>
                                <h3 class="fw-bold m-0"><?php echo $modeLength; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-secondary p-3 shadow-sm">
                                <small class="text-white-50">STD DEV</small>
                                <h3 class="fw-bold m-0"><?php echo $stdDev; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="card stat-card bg-dark p-3 shadow-sm">
                                <small class="text-white-50">MAX LEN</small>
                                <h3 class="fw-bold m-0"><?php echo $maxLength; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Histogram Chart & Outlier Watchdog -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
                            <div class="card studio-card p-4">
                                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-chart-column me-2"></i>Length Spectrum Histogram</h5>
                                <div style="height: 280px;">
                                    <canvas id="spectrumChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card studio-card p-4 h-100">
                                <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Outlier & Whitespace Watchdog</h5>
                                
                                <h6 class="fw-bold text-muted small"><i class="fa-solid fa-bullseye me-1"></i>Length Outliers (> 2 Std Dev)</h6>
                                <?php if (!empty($outliers)): ?>
                                    <ul class="list-group list-group-flush mb-3">
                                        <?php foreach ($outliers as $out): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center small">
                                                <code><?php echo htmlspecialchars($out['item']); ?></code>
                                                <span class="badge bg-danger"><?php echo $out['length']; ?> chars</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-success small mb-3">No extreme length outliers detected.</div>
                                <?php endif; ?>

                                <h6 class="fw-bold text-muted small"><i class="fa-solid fa-eye-slash me-1"></i>Hidden Whitespace / Non-Printable Warnings</h6>
                                <?php if (!empty($hiddenWhitespaceWarnings)): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($hiddenWhitespaceWarnings as $warn): ?>
                                            <li class="list-group-item small text-warning fw-bold">
                                                ⚠️ <code>"<?php echo htmlspecialchars($warn); ?>"</code> contains padding/non-printable chars.
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-success small m-0">All elements have clean whitespace.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: LENGTH HEATMAP & EXPORTER -->
                <div class="tab-pane fade" id="tab-heatmap" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-border-all me-2"></i>Visual Length Heatmap & Exporter</h4>

                        <!-- Export Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" formaction="export_length.php?format=csv" class="btn btn-outline-success btn-sm rounded-pill fw-bold">
                                <i class="fa-solid fa-file-csv me-1"></i> CSV Export
                            </button>
                            <button type="submit" formaction="export_length.php?format=json" class="btn btn-outline-primary btn-sm rounded-pill fw-bold">
                                <i class="fa-solid fa-file-code me-1"></i> JSON Export
                            </button>
                            <button type="submit" formaction="export_length.php?format=xml" class="btn btn-outline-warning btn-sm rounded-pill fw-bold">
                                <i class="fa-solid fa-file-code me-1"></i> XML Export
                            </button>
                            <button type="submit" formaction="export_length.php?format=pdf_print" formtarget="_blank" class="btn btn-outline-dark btn-sm rounded-pill fw-bold">
                                <i class="fa-solid fa-print me-1"></i> PDF Summary
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Payload for Export -->
                    <input type="hidden" name="export_data" value="<?php echo htmlspecialchars(json_encode($filteredArray)); ?>">

                    <div class="card studio-card p-4">
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th># Index</th>
                                        <th>Array Element Value</th>
                                        <th>Character Length</th>
                                        <th>Byte Size</th>
                                        <th>Heatmap Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredArray as $idx => $val): 
                                        $l = mb_strlen($val);
                                        $b = strlen($val);
                                        $badgeClass = 'heatmap-badge-short';
                                        $badgeLabel = 'Short';
                                        if ($l > 20) { $badgeClass = 'heatmap-badge-xl'; $badgeLabel = 'Extra Long'; }
                                        elseif ($l > 10) { $badgeClass = 'heatmap-badge-long'; $badgeLabel = 'Long'; }
                                        elseif ($l > 5) { $badgeClass = 'heatmap-badge-med'; $badgeLabel = 'Medium'; }
                                    ?>
                                        <tr>
                                            <td class="font-monospace small">#<?php echo $idx + 1; ?></td>
                                            <td class="fw-semibold"><code><?php echo htmlspecialchars($val); ?></code></td>
                                            <td><span class="badge bg-secondary font-monospace"><?php echo $l; ?> chars</span></td>
                                            <td class="small text-muted"><?php echo $b; ?> bytes</td>
                                            <td><span class="badge <?php echo $badgeClass; ?> px-3 py-1"><?php echo $badgeLabel; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <footer class="text-center mt-5 mb-3 text-muted small">
            <hr>
            <p>PHP Enterprise Array Length & Structural Studio | Built with PHP 8 & Bootstrap 5</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('spectrumChart')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_keys($spectrum)); ?>,
                        datasets: [{
                            label: 'Element Count',
                            data: <?php echo json_encode(array_values($spectrum)); ?>,
                            backgroundColor: ['#22c55e', '#3b82f6', '#f97316', '#ef4444'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
</body>

</html>