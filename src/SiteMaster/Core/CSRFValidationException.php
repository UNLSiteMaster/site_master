<?php
namespace SiteMaster\Core;

use Throwable;

class CSRFValidationException extends \RuntimeException implements Exception {
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     * @since 5.1.0
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        if (empty($message)) {
            $message = 'Invalid security token provided. If you think this was an error, please retry the request.';
        }
        if (!$code) {
            $code = 403;
        }
        
        parent::__construct($message, $code, $previous);
    }
}