<?php
// API to read customers from the database using PDO and return JSON response

// JSON headers
header('Content-Type: application/json'); // application/json
header('Access-Control-Allow-Origin: *'); // allow all origins
header('Access-Control-Allow-Methods: POST'); // only POST requests allowed NOT , GET, PUT, DELETE, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // allowed headers

require_once '../inc/pdo.php';

// verify method is POST
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod !== 'POST') {
    $data = [
        'status' => http_response_code(405), // Method Not Allowed
        'message' => 'Method Not Allowed. Only POST requests are allowed.'
    ];
    echo json_encode($data);
    exit;
}

// Get the request body
$requestBody = file_get_contents('php://input');

// Decode the JSON data
$JSONData = json_decode($requestBody, true);

// Receive input data from a form 
$FORMData = [
    'fullname' => trim($_POST['fullname'] ?? ''),
    'email' => trim($_POST['email'] ?? '')
];

if (!empty($JSONData)) {
    $customerData = $JSONData;
} else {
    $customerData = $FORMData;
}

// Validate the input
if (empty($customerData['fullname']) || empty($customerData['email'])) {
    $data = [
        'status' => http_response_code(400), // Bad Request
        'message' => 'Invalid input. Fullname and email are required.'
    ];
    echo json_encode($data);
    exit;
}

// Insert the customer into the database
try {
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email) VALUES (:fullname, :email)");
    $stmt->bindParam(':fullname', $customerData['fullname']);
    $stmt->bindParam(':email', $customerData['email']);
    $stmt->execute();

    $data = [
        'status' => http_response_code(201), // Created
        'message' => 'Customer created successfully',
        'data' => [
            'userId' => $pdo->lastInsertId(),
            'fullname' => $customerData['fullname'],
            'email' => $customerData['email']
        ]
    ];
    echo json_encode($data);
    exit;
} catch (PDOException $e) {
    $data = [
        'status' => http_response_code(500), // Internal Server Error
        'message' => 'Database query failed: ' . $e->getMessage()
    ];
    echo json_encode($data);
    exit;
}