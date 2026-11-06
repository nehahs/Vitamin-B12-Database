<?php
require 'vendor/autoload.php'; // Composer autoload

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");

// Select the database and collection
$collection = $client->b12db->B12_Variant_DB9;

// Retrieve query parameters
$query = $_GET['query'];
$column = $_GET['column'];

// Debug: Print query parameters to error log
error_log("Query: " . $query);
error_log("Column: " . $column);

// Construct MongoDB query based on the column
$filter = [$column => $query];

// Example MongoDB query (adjust as per your MongoDB setup)
$cursor = $collection->find($filter);

// Prepare response array
$response = [];
foreach ($cursor as $document) {
    $response[] = $document;
}

// Debug: Print number of documents found
error_log("Number of documents found: " . count($response));

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
