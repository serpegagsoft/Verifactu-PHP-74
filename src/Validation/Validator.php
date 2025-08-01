<?php
namespace josemmo\Verifactu\Validation;

use josemmo\Verifactu\Models\Model;

class Validator {
    public function validate(Model $model): ConstraintViolationList {
        $violations = new ConstraintViolationList();
        $constraints = $model->getConstraints();

        foreach ($constraints as $propertyPath => $rules) {
            $value = $model->{$propertyPath};
            foreach ($rules as $rule) {
                $rule->validate($value, $propertyPath, $violations);
            }
        }

        return $violations;
    }
}
