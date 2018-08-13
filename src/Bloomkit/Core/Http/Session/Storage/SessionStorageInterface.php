<?php

namespace Bloomkit\Core\Http\Session\Storage;

use Bloomkit\Core\Utilities\Repository;

interface SessionStorageInterface
{
    /**
     * Clears all session values.
     */
    public function clear();

    /**
     * Returns the session ID.
     *
     * @return string the session ID
     */
    public function getId();

    /**
     * Checks if the session is started.
     *
     * @return bool True if started, false if not
     */
    public function getIsStarted();

    /**
     * Returns the session name.
     *
     * @return string the session name
     */
    public function getName();

    /**
     * Returns the SessionMessages object.
     *
     * @return Repository the SessionMessages object
     */
    public function getSessionMessages();

    /**
     * Save and close the session (normally not required as sessions
     * will be automatically saved.
     */
    public function save();

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
     * @throws \RuntimeException If session failed to start
     *
     * @return bool true if started
     */
    public function start();
}
