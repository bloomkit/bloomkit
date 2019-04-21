<?php

namespace Bloomkit\Core\Entities\Services;

class ListOutputParameters
{
    /**
     * @var int
     */
    public $limit = 0;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var string|null
     */
    public $orderBy = null;

    /**
     * @var bool
     */
    public $orderAsc = true;

    /**
     * @var bool
     */
    public $determineTotalCount = false;
}