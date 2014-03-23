<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploHttpOptions
 **/

namespace HaploMvc\Http;


/**
 * Class HaploHttpOptions
 * @package Http
 */
class HaploHttpOptions
{
    /** @var int */
    public $connectTimeout = 5;
    /** @var int */
    public $requestTimeout = 10;
    /** @var string */
    public $userAgent = 'HaploMvc';
}
