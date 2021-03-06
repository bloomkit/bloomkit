<?php

namespace Bloomkit\Core\Utilities;

class Repository implements \ArrayAccess, \Iterator
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Constructor.
     *
     * @param array $items List of items to initialize
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
        $this->position = 0;
    }

    /**
     * Check if the given value is either an array or implements ArrayAccess.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Expand/Replace the item list with the given one.
     *
     * @param array $items Items to add/replace
     */
    public function addItems(array $items = array())
    {
        $this->items = array_replace($this->items, $items);
    }

    /**
     * Resets the item array.
     */
    public function clear()
    {
        $this->items = array();
    }

    /**
     * Iterator function: Get the item at the current position.
     */
    public function current()
    {
        return array_values($this->items)[$this->position];
    }

    /**
     * Get the an repository item by its key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return $default;
        }

        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        $array = $this->items;

        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if ($this->accessible($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Return all items as an array.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Check if a repository item can be found by the given keys.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (is_null($key)) {
            return false;
        }

        $array = $this->items;
        if (array_key_exists($key, $array)) {
            return true;
        }

        $keys = explode('.', $key);
        foreach ($keys as $segment) {
            if ($this->accessible($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Iterator function: Return the current positon.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator function: Increase the current position.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * ArrayAccess function: Check if repository item exists - Mapper for "has" function.
     *
     * @param string $key
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * ArrayAccess function: Returns an item - Mapper for "get" function.
     *
     * @param string $key
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * ArrayAccess function: Set an item - Mapper for "set" function.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * ArrayAccess function: Unset an item - Mapper for "set" function.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }

    /**
     * Insert a value as the first child into an existing or new array configuration value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);
        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * Insert a value as the last child into an existing or new array configuration value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Remove an item from the repository.
     *
     * @param string $key The key of the item to remove
     *
     * @return bool true if deleted, false if not
     */
    public function remove($key)
    {
        if (is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $this->items)) {
            unset($this->items[$key]);

            return true;
        }

        $array = &$this->items;
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if ($this->accessible($array) && array_key_exists($segment, $array)) {
                $parent = &$array;
                $key = $segment;
                $array = &$array[$segment];
            } else {
                return false;
            }
        }

        unset($parent[$key]);

        return true;
    }

    /**
     * Iterator function: Set position to 0.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Set a repository item with key -> value.
     * A key can also be an array or an delimeted value ('foo.bar').
     *
     * @param array|string $key
     * @param mixed        $value
     */
    public function set($key, $value = null)
    {
        if (is_null($key)) {
            return;
        }

        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->set($innerKey, $innerValue);
            }
        } else {
            $keys = explode('.', $key);

            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (!isset($this->items[$key]) || !is_array($this->items[$key])) {
                    $this->items[$key] = array();
                }
            }

            $this->items[array_shift($keys)] = $value;
        }
    }

    /**
     * Iterator function: Check if there is an item at the current position.
     */
    public function valid()
    {
        return isset(array_values($this->items)[$this->position]);
    }
}
