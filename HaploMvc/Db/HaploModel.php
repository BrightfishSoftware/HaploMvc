<?php
namespace HaploMvc\Db;

use HaploMvc\HaploApp;

abstract class HaploModel
{
    protected $app;

    public function __construct(HaploApp $app)
    {
        $this->app = $app;
    }
}
