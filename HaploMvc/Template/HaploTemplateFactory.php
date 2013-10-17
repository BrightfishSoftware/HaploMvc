<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploTemplateFactory
 **/

namespace HaploMvc\Template;

use \HaploMvc\Pattern\HaploSingleton,
    \HaploMvc\HaploApp;

/**
 * Class HaploTemplateFactory
 * @package HaploMvc
 */
class HaploTemplateFactory extends HaploSingleton {
    /** @var HaploApp */
    protected $app;

    /**
     * @param HaploApp $app
     * @return mixed
     */
    public static function get_instance(HaploApp $app = null) {
        $class = get_called_class();

        if (!isset(self::$instances[$class]) && !is_null($app)) {
            self::$instances[$class] = new $class($app);
        }
        return self::$instances[$class];
    }

    /**
     * @param HaploApp $app
     */
    protected function __construct(HaploApp $app) {
        $this->app = $app;
    }

    /**
     * @param $template
     * @return HaploTemplate
     */
    public function create($template) {
        return new HaploTemplate($this->app, $template);
    }
}