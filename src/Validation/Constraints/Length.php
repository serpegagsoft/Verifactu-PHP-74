<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Length extends Constraint {
    private ?int $max;
    private ?int $exactly;

    public function __construct(array $options) {
        $this->max = $options['max'] ?? null;
        $this->exactly = $options['exactly'] ?? null;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        if ($this->max !== null && strlen($value) > $this->max) {
            $violations->add($this->createViolation($propertyPath, "This value is too long. It should have {$this->max} characters or less."));
        }
        if ($this->exactly !== null && strlen($value) !== $this->exactly) {
            $violations->add($this->createViolation($propertyPath, "This value should have exactly {$this->exactly} characters."));
        }
    }
}
