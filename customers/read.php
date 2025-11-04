<?php
// API to read customers from the database using PDO and return JSON response

// JSON headers
header('Content-Type: application/json'); // application/json
header('Access-Control-Allow-Origin: *'); // allow all origins
header('Access-Control-Allow-Methods: GET'); // only GET requests allowed NOT , POST, PUT, DELETE, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // allowed headers

require_once '../inc/pdo.php';

// verify method is GET

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod !== 'GET') {

    $data = [
        'status' => http_response_code(405), // Method Not Allowed
        'message' => 'Method Not Allowed. Only GET requests are allowed.'
    ];
    echo json_encode($data);
    exit;
}else{
    try {
        $stmt = $pdo->query("SELECT userId, fullname, email FROM users");

        // count rows before fetching
        $rowCount = $stmt->rowCount();
        if($rowCount > 0){
            // proceed to fetch
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [
                'status' => http_response_code(200), // OK
                'message' => 'Customers retrieved successfully',
                'data' => $customers
            ];
            echo json_encode($data);
            exit;
        }else{
            $data = [
                'status' => http_response_code(404), // Not Found
                'message' => 'No customers found'
            ];
            echo json_encode($data);
            exit;
        }
    } catch (PDOException $e) {
        $data = [
            'status' => http_response_code(500), // Internal Server Error
            'message' => 'Database query failed: ' . $e->getMessage()
        ];
        echo json_encode($data);
        exit;
    }
}