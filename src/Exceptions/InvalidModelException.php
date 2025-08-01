<?php
namespace josemmo\Verifactu\Exceptions;

use RuntimeException;
use josemmo\Verifactu\Validation\ConstraintViolationList;

/**
 * Exception thrown when a model class does not pass validation
 */
class InvalidModelException extends RuntimeException {
    public ConstraintViolationList $violations;

    /**
     * Class constructor
     *
     * @param ConstraintViolationList $violations Constraint violations
     */
    public function __construct(ConstraintViolationList $violations) {
        $this->violations = $violations;
        parent::__construct("Invalid instance of model class:\n" . $this->getHumanRepresentation());
    }

    /**
     * Get human representation of constraint violations
     *
     * @return string Human-readable constraint violations
     */
    private function getHumanRepresentation(): string {
        $res = [];
        foreach ($this->violations as $violation) {
            $res[] = "- {$violation}";
        }
        return implode("\n", $res);
    }
}
