<?php
namespace HaploMvc\Template;

use HaploMvc\App;

/**
 * Class TemplateFactory
 * @package HaploMvc
 */
class TemplateFactory
{
    /** @var App */
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param $template
     * @return Template
     */
    public function create($template)
    {
        return new Template($this->app, $template);
    }
}
