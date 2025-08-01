<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class NotBlank extends Constraint {
    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if (empty($value)) {
            $violations->add($this->createViolation($propertyPath, 'This value should not be blank.'));
        }
    }
}
