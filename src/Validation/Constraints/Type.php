<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Type extends Constraint {
    private string $type;
    private bool $canBeNull;

    public function __construct(string $type, bool $canBeNull = false) {
        $this->type = $type;
        $this->canBeNull = $canBeNull;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        // If value is null and canBeNull is true, validation passes
        if ($value === null && $this->canBeNull) {
            return;
        }
        
        if (gettype($value) !== $this->type) {
            $violations->add($this->createViolation($propertyPath, "This value should be of type {$this->type}."));
        }
    }
}
