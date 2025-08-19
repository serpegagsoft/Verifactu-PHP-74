<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Regex extends Constraint {
    private string $pattern;
    private bool $canBeNull;

    public function __construct(array $options, bool $canBeNull = false) {
        $this->pattern = $options['pattern'];
        $this->canBeNull = $canBeNull;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        // If value is null and canBeNull is true, validation passes
        if ($value === null && $this->canBeNull) {
            return;
        }
        
        if (!preg_match($this->pattern, $value)) {
            $violations->add($this->createViolation($propertyPath, 'This value is not valid.'));
        }
    }
}
