<?php
define('APP_BASE', dirname(__DIR__));
require APP_BASE.'/HaploMvc/HaploInit.php';

use HaploMvc\HaploApp;

$app = HaploApp::get_instance(APP_BASE);
$app->router->add_route('/', 'Home');
$app->run();