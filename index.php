<?php
// Suppress deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

use App\Models\User;
use App\Models\Product; 

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($request, PHP_URL_PATH);

$userModel = new User();
$productModel = new Product(); 

// Extract ID from URL if present
$id = null;
if (preg_match('/\/api\/users\/(\d+)/', $path, $matches) || preg_match('/\/api\/products\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
}

if (strpos($path, '/api/users') === 0) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            if ($id) {
                $user = $userModel->getById($id);
                echo json_encode(['status' => 'success', 'data' => $user]);
            } else {
                $users = $userModel->getAll();
                echo json_encode(['status' => 'success', 'data' => $users]);
            }
            break;

        case 'POST':
            $newId = $userModel->create($data);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'User created', 'id' => $newId]);
            break;

        case 'PUT':
            if ($id) {
                $success = $userModel->update($id, $data);
                echo json_encode(['status' => 'success', 'message' => 'User updated']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No user ID provided']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $success = $userModel->delete($id);
                echo json_encode(['status' => 'success', 'message' => 'User deleted']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No user ID provided']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} elseif (strpos($path, '/api/products') === 0) { // Add handling for products
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            if ($id) {
                $product = $productModel->getById($id);
                echo json_encode(['status' => 'success', 'data' => $product]);
            } else {
                $products = $productModel->getAll();
                echo json_encode(['status' => 'success', 'data' => $products]);
            }
            break;

        case 'POST':
            $newId = $productModel->create($data);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Product created', 'id' => $newId]);
            break;

        case 'PUT':
            if ($id) {
                $success = $productModel->update($id, $data);
                echo json_encode(['status' => 'success', 'message' => 'Product updated']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No product ID provided']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $success = $productModel->delete($id);
                echo json_encode(['status' => 'success', 'message' => 'Product deleted']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No product ID provided']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} else {
    $apiInfo = [
        'name' => 'DealHave API',
        'version' => '1.0.0',
        'description' => 'Welcome to DealHave API - Your Deal Management Solution',
        'endpoints' => [
            '/api/deals' => [
                'GET' => 'List all deals',
                'POST' => 'Create a new deal'
            ],
            '/api/deals/{id}' => [
                'GET' => 'Get deal details',
                'PUT' => 'Update a deal',
                'DELETE' => 'Delete a deal'
            ],
            '/api/users' => [
                'GET' => 'List all users',
                'POST' => 'Create a new user'
            ],
            '/api/products' => [ // Add product endpoints to the API info
                'GET' => 'List all products',
                'POST' => 'Create a new product'
            ],
            '/api/products/{id}' => [
                'GET' => 'Get product details',
                'PUT' => 'Update a product',
                'DELETE' => 'Delete a product'
            ]
        ],
        'documentation' => 'For detailed documentation, visit: /docs',
        'contact' => [
            'email' => 'support@dealhave.com',
            'website' => 'https://dealhave.com'
        ]
    ];
    echo json_encode($apiInfo, JSON_PRETTY_PRINT);
}