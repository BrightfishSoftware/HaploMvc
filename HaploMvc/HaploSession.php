<?php
namespace HaploMvc;

/**
 * Class HaploSession
 * @package HaploMvc
 */
class HaploSession {
    /** @var HaploApp */
    protected $app;

    /**
     * @param HaploApp $app
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
    }

    public function start()
    {
        session_name($this->app->config->getKey('sessions', 'name', 'HaploMvc'));
        session_start();
    }

    public function refresh()
    {
        session_regenerate_id();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }
}
