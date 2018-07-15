<?php
namespace Bloomkit\Core\EventManager;

/**
 * Describes the functions a EventSubscriber must provide
 */
interface EventSubscriberInterface
{
    /**
     * Return the events this subscriber wants to handle
     *
     * @return array Associative array in the form 'EventName' => ['callback', prio]
     */
    public static function getSubscribedEvents();
}
