<?php
require 'vendor/autoload.php'; // Composer autoload

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");

// Select the database and collection
$collection = $client->b12db->B12_Variant_DB9;

$query = $_GET['query'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$resultsPerPage = isset($_GET['resultsPerPage']) ? (int)$_GET['resultsPerPage'] : 5;
$phenotypeFilter = $_GET['Phenotype/Trait'] ?? '';

// Debugging: Check if query is received
if (empty($query)) {
    echo json_encode(['error' => 'Query parameter missing']);
    exit;
}

// List of valid column names to search
$validColumns = [
    'DBSNP id', 'Variant', 'Phenotype/Trait', 'Phenotype1', 'Gene'
];

// Build a search query that checks all specified columns
$searchConditions = [];
foreach ($validColumns as $column) {
    $searchConditions[] = [$column => new MongoDB\BSON\Regex("^$query$", 'i')];
}

// Use MongoDB's $or operator to combine search conditions
$searchQuery = ['$or' => $searchConditions];

// Find documents matching the search query
$results = $collection->find($searchQuery);

// Filter results by phenotype if phenotype filter is provided
if (!empty($phenotypeFilter)) {
    $filteredResults = [];
    foreach ($results as $result) {
        if (stripos($result['Phenotype/Trait'] ?? '', $phenotypeFilter) !== false) {
            $filteredResults[] = $result;
        }
    }
    $results = $filteredResults;
}

// Aggregate results by DBSNP id
$aggregatedResults = [];
foreach ($results as $result) {
    $dbsnp = $result['DBSNP id'];
    if (!isset($aggregatedResults[$dbsnp])) {
        $aggregatedResults[$dbsnp] = [];
    }
    foreach ($result as $key => $value) {
        if ($key != 'DBSNP id') {
            if (!isset($aggregatedResults[$dbsnp][$key])) {
                $aggregatedResults[$dbsnp][$key] = [];
            }
            if (!in_array($value, $aggregatedResults[$dbsnp][$key])) {
                $aggregatedResults[$dbsnp][$key][] = $value;
            }
        }
    }
}

// Convert aggregated results to a format suitable for JSON output
$output = [];
foreach ($aggregatedResults as $dbsnp => $data) {
    $record = ['DBSNP id' => $dbsnp];
    foreach ($data as $key => $values) {
        $record[$key] = implode(', ', $values);
    }
    $output[] = $record;
}

// Pagination logic
$totalResults = count($output);
$totalPages = ceil($totalResults / $resultsPerPage);
$startIndex = ($page - 1) * $resultsPerPage;
$paginatedResults = array_slice($output, $startIndex, $resultsPerPage);

// Include the number of results and pagination info in the JSON response
$response = [
    'count' => $totalResults,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'resultsPerPage' => $resultsPerPage,
    'results' => $paginatedResults
];

// Return the aggregated search results as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
