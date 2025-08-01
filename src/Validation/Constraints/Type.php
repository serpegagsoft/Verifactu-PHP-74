<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Type extends Constraint {
    private string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if (gettype($value) !== $this->type) {
            $violations->add($this->createViolation($propertyPath, "This value should be of type {$this->type}."));
        }
    }
}
