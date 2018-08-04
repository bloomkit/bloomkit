<?php

namespace Bloomkit\Core\Http\Session;

interface SessionInterface
{
    /**
     * Clears all session values.
     */
    public function clear();

    /**
     * Returns a session value.
     *
     * @param string $name    The name of the value
     * @param mixed  $default the default value if not found
     *
     * @return mixed The requested session value
     */
    public function get($name, $default = null);

    /**
     * Returns the session ID.
     *
     * @return string the session ID
     */
    public function getId();

    /**
     * Returns the session name.
     *
     * @return string the session name
     */
    public function getName();

    /**
     * Save and close the session (normally not required as sessions
     * will be automatically saved.
     */
    public function save();

    /**
     * Sets a session value.
     *
     * @param string $name  The name of the value
     * @param mixed  $value The value to set
     */
    public function set($name, $value);

    /**
     * Sets the session ID.
     *
     * @param string $id The id to set
     */
    public function setId($id);

    /**
     * Sets the session name.
     *
     * @param string $name The name to set
     */
    public function setName($name);

    /**
     * Starts the session.
     *
     * @return bool true if session started
     */
    public function start();
}
