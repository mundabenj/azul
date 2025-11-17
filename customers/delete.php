<?php
// API to read customers from the database using PDO and return JSON response

// JSON headers
header('Content-Type: application/json'); // application/json
header('Access-Control-Allow-Origin: *'); // allow all origins
header('Access-Control-Allow-Methods: DELETE'); // only DELETE requests allowed NOT , GET, POST, PUT, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // allowed headers

require_once '../inc/pdo.php';

// verify method is DELETE
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod !== 'DELETE') {
    $data = [
        'status' => http_response_code(405), // Method Not Allowed
        'message' => 'Method Not Allowed. Only DELETE requests are allowed.'
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
    'userId' => trim($_POST['userId'] ?? '')
];

if (!empty($JSONData)) {
    $customerData = $JSONData;
} else {
    $customerData = $FORMData;
}

// Validate the input
if (empty($customerData['userId']) ) {
    $data = [
        'status' => http_response_code(400), // Bad Request
        'message' => 'Invalid input. UserID is required.'
    ];
    echo json_encode($data);
    exit;
}

// Verify customer exists (optional, but good practice)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE userId = :userId");
$stmt->bindParam(':userId', $customerData['userId']);
$stmt->execute();
$customerExists = $stmt->fetchColumn();

if (!$customerExists) {
    $data = [
        'status' => http_response_code(404), // Not Found
        'message' => 'Customer not found.'
    ];
    echo json_encode($data);
    exit;
}

// Delete the customer in the database
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE userId = :userId");
    $stmt->bindParam(':userId', $customerData['userId'], PDO::PARAM_INT);
    $stmt->execute();

    $data = [
        'status' => http_response_code(200), // OK
        'message' => 'Customer deleted successfully'
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
