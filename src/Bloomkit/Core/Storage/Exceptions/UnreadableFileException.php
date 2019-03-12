<?php

namespace Bloomkit\Core\Storage\Exceptions;

use SplFileInfo;

/**
 * Definition of the UnreadableFileException.
 */
class UnreadableFileException extends \Exception
{
    public static function forFileInfo(SplFileInfo $fileInfo)
    {
        return new static(sprintf('Unreadable file encountered: %s', $fileInfo->getRealPath()));
    }
}
