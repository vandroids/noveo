<?php

namespace App\API\Exception;

class ValidatorException extends \Exception
{
    private $violations = [];

    /**
     * @param string    $message
     * @param int       $code
     * @param Throwable $previous
     * @param type      $violations
     */
    public function __construct(string $message, int $code = 0, \Throwable $previous = null, array $violations = [])
    {
        parent::__construct($message, $code, $previous);

        $this->violations = $violations;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
