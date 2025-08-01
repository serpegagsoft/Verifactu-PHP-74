<?php
namespace josemmo\Verifactu\Validation\Constraints;

use josemmo\Verifactu\Validation\Constraint;
use josemmo\Verifactu\Validation\ConstraintViolationList;

class Callback extends Constraint {
    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function validate($value, string $propertyPath, ConstraintViolationList $violations): void {
        call_user_func($this->callback, $violations);
    }
}
