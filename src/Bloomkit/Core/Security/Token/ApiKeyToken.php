<?php

namespace Bloomkit\Core\Security\Token;

/**
 * Representation of an ApiKey Token
 */
class ApiKeyToken extends Token
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * Constructor.
     *
     * @param string $apiKey    The apiKey to set
     * @param array  $roles     The roles to set
     */
    public function __construct($apiKey, array $roles = [])
    {
        parent::__construct($roles);
        $this->apiKey = $apiKey;
        $this->setUser('anonymous');
        $this->setAuthenticated(true);
    }

    /**
     * Returns the apiKey
     *
     * @return string The apiKey
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }
}
