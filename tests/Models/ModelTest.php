<?php
namespace josemmo\Verifactu\Tests\Models;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Exceptions\InvalidModelException;
use josemmo\Verifactu\Models\Model;

class SampleModel extends Model {
    public string $name;
    public int $quantity;

    public function getConstraints(): array {
        return [
            'name' => [new Assert\NotBlank(), new Assert\Length(['exactly' => 4])],
            'quantity' => [new Assert\NotBlank(), new Assert\Positive()],
        ];
    }
}

final class ModelTest extends TestCase {
    public function testNotThrowsOnValidModel(): void {
        $model = new SampleModel();
        $model->name = 'abcd';
        $model->quantity = 2;
        $model->validate();
        $this->assertTrue(true);
    }

    public function testThrowsOnInvalidModel(): void {
        $this->expectException(InvalidModelException::class);
        $model = new SampleModel();
        $model->name = 'This is not a valid name';
        $model->quantity = 0;
        $model->validate();
    }
}
