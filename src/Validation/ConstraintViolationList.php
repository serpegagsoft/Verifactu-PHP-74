<?php
namespace josemmo\Verifactu\Validation;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class ConstraintViolationList implements Countable, IteratorAggregate {
    private array $violations = [];

    public function add(ConstraintViolation $violation): void {
        $this->violations[] = $violation;
    }

    public function count(): int {
        return count($this->violations);
    }

    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->violations);
    }
}
