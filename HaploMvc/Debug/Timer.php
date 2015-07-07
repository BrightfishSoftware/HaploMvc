<?php
namespace HaploMvc\Debug;

class Timer
{
    protected $startTime;
    
    /**
     * Start timing script
     **/
    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function get()
    {
        return (microtime(true) - $this->startTime);
    }
}
