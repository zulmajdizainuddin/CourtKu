<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule said no, e.g. "this venue is already active" or, later, SLOT_TAKEN.
 * bootstrap/app.php turns it into the standard error shape:
 * { "error": { "code": "...", "message": "...", "details": {...} } }
 */
class ApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 409,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }
}
