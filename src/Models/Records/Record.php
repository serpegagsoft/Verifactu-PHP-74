<?php
namespace josemmo\Verifactu\Models\Records;

use DateTimeImmutable;
use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Validation\ConstraintViolationList;
use josemmo\Verifactu\Models\Model;

/**
 * Base invoice record
 */
abstract class Record extends Model {
    /**
     * ID de factura
     *
     * @field IDFactura
     */
    public InvoiceIdentifier $invoiceId;

    /**
     * ID de factura del registro anterior
     *
     * @field Encadenamiento/RegistroAnterior
     */
    public ?InvoiceIdentifier $previousInvoiceId;

    /**
     * Primeros 64 caracteres de la huella o hash del registro de facturación anterior
     *
     * @field Encadenamiento/RegistroAnterior/Huella
     */
    public ?string $previousHash;

    /**
     * Huella o hash de cierto contenido de este registro de facturación
     *
     * @field Huella
     */
    public string $hash;

    /**
     * Fecha, hora y huso horario de generación del registro de facturación
     *
     * @field FechaHoraHusoGenRegistro
     */
    public DateTimeImmutable $hashedAt;

    /**
     * Calculate record hash
     *
     * @return string Expected record hash
     */
    abstract public function calculateHash(): string;

    public function getConstraints(): array {
        return [
            'invoiceId' => [new Assert\NotBlank(), new Assert\Valid()],
            'previousInvoiceId' => [new Assert\Valid()],
            'previousHash' => [new Assert\Callback([$this, 'validatePreviousHash'])],
            'hash' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^[0-9A-F]{64}$/'])],
            'hashedAt' => [new Assert\NotBlank()],
            'previousInvoiceId' => [new Assert\Callback([$this, 'validatePreviousInvoice'])],
        ];
    }

    final public function validatePreviousInvoice(ConstraintViolationList $violations): void {
        if ($this->previousInvoiceId !== null && $this->previousHash === null) {
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                'Previous hash is required if previous invoice ID is provided',
                'previousHash'
            ));
        } elseif ($this->previousHash !== null && $this->previousInvoiceId === null) {
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                'Previous invoice ID is required if previous hash is provided',
                'previousInvoiceId'
            ));
        }
    }

    final public function validatePreviousHash(ConstraintViolationList $violations): void {
        if ($this->previousHash === null) {
            return;
        }
        $constraint = new Assert\Regex(['pattern' => '/^[0-9A-F]{64}$/']);
        $constraint->validate($this->previousHash, 'previousHash', $violations);
    }
}
