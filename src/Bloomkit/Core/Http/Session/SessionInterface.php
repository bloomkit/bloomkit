<?php

namespace Bloomkit\Core\Http\Session;

interface SessionInterface
{
//    public function all();

//    public function clear();

    public function get($name, $default = null);

//    public function getBag($name);

//    public function getId();

//    public function getMetadataBag();

//    public function getName();

//    public function has($name);

//    public function invalidate($lifetime = null);

//    public function isStarted();

//    public function migrate($destroy = false, $lifetime = null);

//    public function registerBag(SessionBagInterface $bag);

//    public function remove($name);

//    public function replace(array $attributes);

//    public function save();

//    public function set($name, $value);

//    public function setId($id);

//    public function setName($name);

    public function start();
}
