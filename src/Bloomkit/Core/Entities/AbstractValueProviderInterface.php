<?php

namespace Bloomkit\Core\Entities;

interface AbstractValueProviderInterface
{
    public function setAbstractFieldValues(Entity $entity): void;
}