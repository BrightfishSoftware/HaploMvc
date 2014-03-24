<?php
define('APP_BASE', dirname(__DIR__));
require APP_BASE.'/HaploMvc/HaploInit.php';

use HaploMvc\HaploApp;

$app = HaploApp::getInstance(APP_BASE);
$app->initServices();
$app->router->addRoute('/', 'Home');
$app->run();
