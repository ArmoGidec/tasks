<?php
ini_set('display_errors', 1);
require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();
$router->setNamespace('\App\Controllers');

$router->get('', 'HomeController@index');
$router->post('', 'HomeController@createTask');

$router->all('/task/{task_id}/complete', 'TaskController@completeTask');
$router->get('/task/{task_id}/change', 'TaskController@viewChangePage');
$router->post('/task/{task_id}/change', 'TaskController@changeTask');

$router->get('/login', 'AuthController@getPage');
$router->post('/login', 'AuthController@login');
$router->all('/logout', 'AuthController@logout');

$router->run();