<?php
namespace HaploMvc\Action;

use HaploMvc\HaploApp;

/**
 * Class HaploSlimAction
 * @package HaploMvc
 */
abstract class HaploSlimAction
{
    /** @var HaploApp */
    protected $app;

    /**
     * @param \HaploMvc\HaploApp $app
     * @return \HaploMvc\Action\HaploSlimAction
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
    }
}
