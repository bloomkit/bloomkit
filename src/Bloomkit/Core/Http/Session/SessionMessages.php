<?php

namespace Bloomkit\Core\Http\Session;

class SessionMessages implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * Adds a message for message-type.
     *
     * @param string $type    The type of the message (e.g. alert, info)
     * @param string $message The message to add
     */
    public function add($type, $message)
    {
        $this->messages[$type][] = $message;
    }

    /**
     * Resets the messages array.
     */
    public function clear()
    {
        $this->messages = [];
    }

    /**
     * Gets (and optinally clears) messages for a type.
     *
     * @param string $type    The type of the message (e.g. alert, info)
     * @param array  $default Default value if type does not exist
     * @param bool   $clear   Clears the messages for the type if set
     *
     * @return array
     */
    public function get($type, array $default = [], $clear = true)
    {
        if (!$this->has($type)) {
            return $default;
        }

        $return = $this->messages[$type];
        if ($clear) {
            unset($this->messages[$type]);
        }

        return $return;
    }

    /**
     * Gets (and optinally clears) clears messages from the stack.
     *
     * @param bool $clear Clears the messages for the type if set
     *
     * @return array
     */
    public function getAll($clear = true)
    {
        $return = $this->messages;
        if ($clear) {
            $this->messages = [];
        }

        return $return;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Returns a list of all defined types.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->messages);
    }

    /**
     * Has messages for a given type?
     *
     * @param string $type
     *
     * @return bool
     */
    public function has($type)
    {
        return array_key_exists($type, $this->messages) && $this->messages[$type];
    }

    /**
     * Connects an external array as storage for the repository.
     *
     * @param array $sessionData The external array to connect
     */
    public function linkSessionData(array &$sessionData)
    {
        $this->items = &$sessionData;
    }

    /**
     * Set message/s for a given type.
     *
     * @param string       $type
     * @param string|array $message
     */
    public function set($type, $messages)
    {
        $this->messages[$type] = (array) $messages;
    }

    /**
     * Sets all messages.
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }
}
