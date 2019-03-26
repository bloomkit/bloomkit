<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Utilities\Repository;

class ListResult extends Repository
{
    /**
     * @var int|null
     */
    private $totalCount;

    /**
     * Constructor.
     *
     * @param array $items List of items to initialize
     * @param int|null $items Total count available
     */
    public function __construct(array $items = [], ?int $totalCount)
    {
        parent::__construct($items);
        $this->totalCount = $totalCount;
    }

    /**
     * Get the value of totalCount
     *
     * @return  int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }
}