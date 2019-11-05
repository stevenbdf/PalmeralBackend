<?php

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}

require './controllers/users.php';
require './controllers/suppliers.php';
require './controllers/categories.php';
require './controllers/products.php';
require './controllers/transactions.php';

$app = new \Slim\App();

$app->group('/users', function () {
    $this->get('', UsersController::class . ':get');
    $this->post('/create', UsersController::class . ':create');
    $this->post('/update', UsersController::class . ':update');
    $this->post('/password', UsersController::class . ':password');
    $this->post('/delete', UsersController::class . ':delete');
    $this->post('/login', UsersController::class . ':login');
    $this->post('/find', UsersController::class . ':find');
    $this->post('/resetPassword', UsersController::class . ':resetPassword');
    $this->get('/validateToken', UsersController::class . ':validateToken');
});

$app->group('/suppliers', function () {
    $this->get('', SuppliersController::class . ':get');
    $this->post('/create', SuppliersController::class . ':create');
    $this->post('/update', SuppliersController::class . ':update');
    $this->post('/delete', SuppliersController::class . ':delete');
    $this->post('/find', SuppliersController::class . ':find');
});

$app->group('/categories', function () {
    $this->get('', CategoriesController::class . ':get');
    $this->post('/create', CategoriesController::class . ':create');
    $this->post('/update', CategoriesController::class . ':update');
    $this->post('/delete', CategoriesController::class . ':delete');
    $this->post('/find', CategoriesController::class . ':find');
});

$app->group('/products', function () {
    $this->get('', ProductsController::class . ':get');
    $this->post('/create', ProductsController::class . ':create');
    $this->post('/update', ProductsController::class . ':update');
    $this->post('/delete', ProductsController::class . ':delete');
    $this->post('/find', ProductsController::class . ':find');
});

$app->group('/transactions', function () {
    $this->get('', TransactionsController::class . ':get');
    $this->post('/create', TransactionsController::class . ':create');
    $this->post('/delete', TransactionsController::class . ':delete');
});

$app->run();
