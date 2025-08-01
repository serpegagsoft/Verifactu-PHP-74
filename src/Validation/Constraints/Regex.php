<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Regex extends Constraint {
    private string $pattern;

    public function __construct(array $options) {
        $this->pattern = $options['pattern'];
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if (!preg_match($this->pattern, $value)) {
            $violations->add($this->createViolation($propertyPath, 'This value is not valid.'));
        }
    }
}
