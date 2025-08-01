<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Models\Model;
use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;
use josemmo\Verifactu\Validation\Validator;

class Valid extends Constraint {
    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if ($value instanceof Model) {
            $validator = new Validator();
            $innerViolations = $validator->validate($value);
            foreach ($innerViolations as $violation) {
                $violations->add($violation);
            }
        }
    }
}
