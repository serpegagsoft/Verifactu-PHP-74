<?php
namespace josemmo\Verifactu\Validation;

class ConstraintViolation {
    private string $message;
    private string $propertyPath;

    public function __construct(string $message, string $propertyPath) {
        $this->message = $message;
        $this->propertyPath = $propertyPath;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getPropertyPath(): string {
        return $this->propertyPath;
    }

    public function __toString(): string {
        return $this->propertyPath . ': ' . $this->message;
    }
}
