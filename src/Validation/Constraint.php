<?php
namespace josemmo\Verifactu\Validation;

abstract class Constraint {
    abstract public function validate($value, string $propertyPath, ConstraintViolationList $violations): void;

    protected function createViolation(string $propertyPath, string $message): ConstraintViolation {
        return new ConstraintViolation($message, $propertyPath);
    }
}
