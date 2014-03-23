<?php
namespace HaploMvc;

require APP_BASE.'/HaploMvc/Exception/HaploExceptions.php';
require APP_BASE.'/vendor/autoload.php';

// this can be disabled after successful setup
HaploSetup::validate(APP_BASE);