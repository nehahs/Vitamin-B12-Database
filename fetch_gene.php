<?php
require 'vendor/autoload.php'; // Composer autoload

// Enable error reporting (optional, for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->b12db->B12_Variant_DB9;

// Retrieve phenotype from query parameter
$phenotype = $_GET['phenotype'];

// Aggregate genes for the selected phenotype, excluding empty genes
$pipeline = [
    [
        '$match' => [
            'Phenotype/Trait' => $phenotype,
            'Gene' => ['$ne' => ''] // Exclude empty gene fields
        ]
    ],
    [
        '$group' => [
            '_id' => '$Gene',
            'count' => ['$sum' => 1]
        ]
    ],
    [
        '$sort' => ['count' => -1]
    ]
];

$results = $collection->aggregate($pipeline);

$genesData = [];
foreach ($results as $result) {
    if (!empty($result->_id)) { // Further ensure no empty genes are included
        $genesData[] = ['gene' => $result->_id, 'count' => $result->count];
    }
}

// Return the aggregated data as JSON
header('Content-Type: application/json');
echo json_encode($genesData);
?>
