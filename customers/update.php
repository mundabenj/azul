<?php
// API to read customers from the database using PDO and return JSON response

// JSON headers
header('Content-Type: application/json'); // application/json
header('Access-Control-Allow-Origin: *'); // allow all origins
header('Access-Control-Allow-Methods: PUT'); // only PUT requests allowed NOT , GET, POST, DELETE, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // allowed headers

require_once '../inc/pdo.php';

// verify method is PUT
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod !== 'PUT') {
    $data = [
        'status' => http_response_code(405), // Method Not Allowed
        'message' => 'Method Not Allowed. Only PUT requests are allowed.'
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
    'userId' => trim($_POST['userId'] ?? ''),
    'fullname' => trim($_POST['fullname'] ?? ''),
    'email' => trim($_POST['email'] ?? '')
];

if (!empty($JSONData)) {
    $customerData = $JSONData;
} else {
    $customerData = $FORMData;
}

// Validate the input
if (empty($customerData['userId']) || empty($customerData['fullname']) || empty($customerData['email'])) {
    $data = [
        'status' => http_response_code(400), // Bad Request
        'message' => 'Invalid input. User ID, fullname, and email are required.'
    ];
    echo json_encode($data);
    exit;
}

// Update the customer in the database
try {
    $stmt = $pdo->prepare("UPDATE users SET fullname = :fullname, email = :email WHERE userId = :userId");
    $stmt->bindParam(':fullname', $customerData['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(':email', $customerData['email'], PDO::PARAM_STR);
    $stmt->bindParam(':userId', $customerData['userId'], PDO::PARAM_INT);
    $stmt->execute();

    $data = [
        'status' => http_response_code(200), // OK
        'message' => 'Customer updated successfully',
        'data' => [
            'userId' => $customerData['userId'],
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