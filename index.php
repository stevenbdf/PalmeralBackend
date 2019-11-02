<?php

require './controllers/users.php';
require './controllers/suppliers.php';
require './controllers/categories.php';
require './controllers/products.php';

$app = new \Slim\App();

$app->group('/users', function () {
    $this->get('', UsersController::class . ':get');
    $this->post('/create', UsersController::class . ':create');
    $this->post('/update', UsersController::class . ':update');
    $this->post('/password', UsersController::class . ':password');
    $this->delete('/delete', UsersController::class . ':delete');
    $this->post('/login', UsersController::class . ':login');
    $this->post('/find', UsersController::class . ':find');
});

$app->group('/suppliers', function () {
    $this->get('', SuppliersController::class . ':get');
    $this->post('/create', SuppliersController::class . ':create');
    $this->post('/update', SuppliersController::class . ':update');
    $this->delete('/delete', SuppliersController::class . ':delete');
    $this->post('/find', SuppliersController::class . ':find');
});

$app->group('/categories', function () {
    $this->get('', CategoriesController::class . ':get');
    $this->post('/create', CategoriesController::class . ':create');
    $this->post('/update', CategoriesController::class . ':update');
    $this->delete('/delete', CategoriesController::class . ':delete');
    $this->post('/find', CategoriesController::class . ':find');
});

$app->group('/products', function () {
    $this->get('', ProductsController::class . ':get');
    $this->post('/create', ProductsController::class . ':create');
    $this->post('/update', ProductsController::class . ':update');
    $this->delete('/delete', ProductsController::class . ':delete');
    $this->post('/find', ProductsController::class . ':find');
});

$app->run();
