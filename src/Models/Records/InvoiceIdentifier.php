<?php
namespace josemmo\Verifactu\Models\Records;

use DateTimeImmutable;
use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Models\Model;

/**
 * Identificador de factura
 */
class InvoiceIdentifier extends Model {
    /**
     * Class constructor
     *
     * @param string|null            $issuerId      Issuer ID
     * @param string|null            $invoiceNumber Invoice number
     * @param DateTimeImmutable|null $issueDate     Issue date
     */
    public function __construct(
        ?string $issuerId = null,
        ?string $invoiceNumber = null,
        ?DateTimeImmutable $issueDate = null
    ) {
        if ($issuerId !== null) {
            $this->issuerId = $issuerId;
        }
        if ($invoiceNumber !== null) {
            $this->invoiceNumber = $invoiceNumber;
        }
        if ($issueDate !== null) {
            $this->issueDate = $issueDate;
        }
    }

    /**
     * Número de identificación fiscal (NIF) del obligado a expedir la factura
     *
     * @field IDFactura/IDEmisorFactura
     */
    public string $issuerId;

    /**
     * Nº Serie + Nº Factura que identifica a la factura emitida
     *
     * @field IDFactura/NumSerieFactura
     */
    public string $invoiceNumber;

    /**
     * Fecha de expedición de la factura
     *
     * NOTE: Time part will be ignored.
     *
     * @field IDFactura/FechaExpedicionFactura
     */
    public DateTimeImmutable $issueDate;

    public function getConstraints(): array {
        return [
            'issuerId' => [new Assert\NotBlank(), new Assert\Length(['exactly' => 9])],
            'invoiceNumber' => [new Assert\NotBlank(), new Assert\Length(['max' => 60])],
            'issueDate' => [new Assert\NotBlank()],
        ];
    }
}
