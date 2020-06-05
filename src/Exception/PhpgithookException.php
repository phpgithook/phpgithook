<?php

declare(strict_types=1);

namespace PHPGithook\Runner\Exception;

use Exception;
use Throwable;

class PhpgithookException extends Exception
{
    /**
     * @param string|array $message
     */
    public function __construct($message = '', int $code = 1, Throwable $previous = null)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        parent::__construct($message, $code, $previous);
    }
}
