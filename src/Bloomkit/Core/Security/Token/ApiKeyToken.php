<?php

namespace Bloomkit\Core\Security\Token;

class ApiKeyToken extends Token
{
    private $apiKey;

    public function __construct($apiKey, array $roles = [])
    {
        parent::__construct($roles);
        $this->apiKey = $apiKey;
        $this->setUser('anonymous');
        $this->setAuthenticated(true);
    }

    public function getApiKey()
    {
        return $this->_apiKey;
    }
}
