<?php

namespace Bloomkit\Core\Security\EntryPoint;

use Bloomkit\Core\Security\Exceptions\AuthException;
use Bloomkit\Core\Http\HttpRequest;

interface AuthEntryPointInterface
{
    public function start(HttpRequest $request, AuthException $e = null);
}
