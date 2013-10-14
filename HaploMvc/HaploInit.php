<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 **/

namespace HaploMvc;

require APP_BASE.'/HaploMvc/Exception/HaploExceptions.php';
require APP_BASE.'/HaploMvc/HaploLoader.php';

// this can be disabled after successful setup
HaploSetup::validate(APP_BASE);