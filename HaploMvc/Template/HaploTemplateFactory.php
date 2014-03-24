<?php
namespace HaploMvc\Template;

use HaploMvc\HaploApp;

/**
 * Class HaploTemplateFactory
 * @package HaploMvc
 */
class HaploTemplateFactory
{
    /** @var HaploApp */
    protected $app;

    /**
     * @param HaploApp $app
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
    }

    /**
     * @param $template
     * @return HaploTemplate
     */
    public function create($template)
    {
        return new HaploTemplate($this->app, $template);
    }
}
