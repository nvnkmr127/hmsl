<?php

namespace App\Exceptions;

use Exception;

class WebhookValidationException extends Exception
{
    // This exception indicates a bad payload or validation error
    // It should NOT trigger a retry.
}
