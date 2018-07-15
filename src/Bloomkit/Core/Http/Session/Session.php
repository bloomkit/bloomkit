<?php

namespace Bloomkit\Core\Http\Session;

class Session implements SessionInterface
{
    public function __construct()
    {
    }

    public function start()
    {
    }
    
    public function get($name, $default = null)
    {
        return $default;
    }
}
