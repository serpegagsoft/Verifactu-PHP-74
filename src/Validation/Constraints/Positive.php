<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Positive extends Constraint {
    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if ($value <= 0) {
            $violations->add($this->createViolation($propertyPath, 'This value should be positive.'));
        }
    }
}
