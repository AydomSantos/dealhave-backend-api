<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\UserController;
use App\Controllers\ProductController;
use App\Controllers\PaymentController;

// Removed duplicate ProductController import

// User routes
$app->get('/api/users', [UserController::class, 'getAll']);
$app->get('/api/users/{id}', [UserController::class, 'getOne']);
$app->post('/api/users', [UserController::class, 'create']);
$app->put('/api/users/{id}', [UserController::class, 'update']);

// Product routes
$app->get('/api/products', [ProductController::class, 'getAll']);
$app->get('/api/products/{id}', [ProductController::class, 'getOne']);
$app->post('/api/products', [ProductController::class, 'create']);
$app->put('/api/products/{id}', [ProductController::class, 'update']);
$app->delete('/api/products/{id}', [ProductController::class, 'delete']);

$app->post('/api/payments', [PaymentController::class, 'processPayment']);
$app->post('/api/payments/qrcode', [PaymentController::class, 'generateQrCode']);