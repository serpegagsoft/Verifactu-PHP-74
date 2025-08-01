<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Count extends Constraint {
    private ?int $min;
    private ?int $max;

    public function __construct(array $options) {
        $this->min = $options['min'] ?? null;
        $this->max = $options['max'] ?? null;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if ($this->min !== null && count($value) < $this->min) {
            $violations->add($this->createViolation($propertyPath, "This collection should contain {$this->min} elements or more."));
        }
        if ($this->max !== null && count($value) > $this->max) {
            $violations->add($this->createViolation($propertyPath, "This collection should contain {$this->max} elements or less."));
        }
    }
}
