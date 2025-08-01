<?php
namespace josemmo\Verifactu\Models;

use josemmo\Verifactu\Exceptions\InvalidModelException;
use josemmo\Verifactu\Validation\ConstraintViolationList;
use josemmo\Verifactu\Validation\Validator;

abstract class Model {
    /**
     * Get constraints
     *
     * @return array<string,mixed> Constraints
     */
    public function getConstraints(): array {
        return [];
    }

    /**
     * Validate this instance
     *
     * @throws InvalidModelException if failed to pass validation
     */
    final public function validate(): void {
        $validator = new Validator();
        $errors = $validator->validate($this);
        if (count($errors) > 0) {
            throw new InvalidModelException($errors);
        }
    }
}
