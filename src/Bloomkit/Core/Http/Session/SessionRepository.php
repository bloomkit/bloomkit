<?php

namespace Bloomkit\Core\Http\Session;

use Bloomkit\Core\Utilities\Repository;

class SessionRepository extends Repository
{
    
    /**
     * Connects an external array as storage for the repository
     *      
     * @param array $sessionData The external array to connect
     */
    public function linkSessionData(array &$sessionData)
    {
        $this->items = &$sessionData;
    }
    
}
