<?php
namespace HaploMvc;

/**
 * Class Session
 * @package HaploMvc
 */
class Session {
    /** @var App */
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->start();
    }

    protected function start()
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
