<?php
require 'vendor/autoload.php'; // Composer autoload

// Enable error reporting (optional, for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->b12db->B12_Variant_DB9;

// Aggregate phenotypes and count unique DBSNP IDs for each phenotype
$pipeline = [
    [
        '$group' => [
            '_id' => '$Phenotype/Trait', // Group by Phenotype/Trait
            'uniqueDBSNPids' => ['$addToSet' => '$DBSNP id'] // Collect unique DBSNP ids into a set
        ]
    ],
    [
        '$project' => [
            'count' => ['$size' => '$uniqueDBSNPids'] // Count the number of unique DBSNP ids
        ]
    ],
    [
        '$sort' => ['count' => -1] // Sort by the count of unique DBSNP ids in descending order
    ]
];

$results = $collection->aggregate($pipeline);

$phenotypesData = [];
foreach ($results as $result) {
    $phenotypesData[] = ['phenotype' => $result->_id, 'count' => $result->count];
}

// Return the aggregated data as JSON
header('Content-Type: application/json');
echo json_encode($phenotypesData);
?>
