<?php
namespace josemmo\Verifactu\Models\Records;

use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Validation\ConstraintViolationList;

/**
 * Registro de alta de una factura
 *
 * @field RegistroAlta
 */
class RegistrationRecord extends Record {
    /**
     * Nombre-razón social del obligado a expedir la factura
     *
     * @field NombreRazonEmisor
     */
    public string $issuerName;

    /**
     * Especificación del tipo de factura
     *
     * @field TipoFactura
     */
    public $invoiceType;

    /**
     * Descripción del objeto de la factura
     *
     * @field DescripcionOperacion
     */
    public string $description;

    /**
     * Destinatarios de la factura
     *
     * @var array<FiscalIdentifier | ForeignFiscalIdentifier>
     * @field Destinatarios
     */
    public array $recipients = [];

    /**
     * Desglose de la factura
     *
     * @var BreakdownDetails[]
     * @field Desglose
     */
    public array $breakdown = [];

    /**
     * Importe total de la cuota (sumatorio de la Cuota Repercutida y Cuota de Recargo de Equivalencia)
     *
     * @field CuotaTotal
     */
    public string $totalTaxAmount;

    /**
     * Importe total de la factura
     *
     * @field ImporteTotal
     */
    public string $totalAmount;

    /**
     * @inheritDoc
     */
    public function calculateHash(): string {
        // NOTE: Values should NOT be escaped as that what the AEAT says ¯\_(ツ)_/¯
        $payload  = 'IDEmisorFactura=' . $this->invoiceId->issuerId;
        $payload .= '&NumSerieFactura=' . $this->invoiceId->invoiceNumber;
        $payload .= '&FechaExpedicionFactura=' . $this->invoiceId->issueDate->format('d-m-Y');
        $payload .= '&TipoFactura=' . $this->invoiceType;
        $payload .= '&CuotaTotal=' . $this->totalTaxAmount;
        $payload .= '&ImporteTotal=' . $this->totalAmount;
        $payload .= '&Huella=' . ($this->previousHash ?? '');
        $payload .= '&FechaHoraHusoGenRegistro=' . $this->hashedAt->format('c');
        return strtoupper(hash('sha256', $payload));
    }

    public function getConstraints(): array {
        return array_merge(parent::getConstraints(), [
            'issuerName' => [new Assert\NotBlank(), new Assert\Length(['max' => 120])],
            'invoiceType' => [new Assert\NotBlank()],
            'description' => [new Assert\NotBlank(), new Assert\Length(['max' => 500])],
            'recipients' => [new Assert\Valid(), new Assert\Count(['max' => 1000]), new Assert\Callback([$this, 'validateRecipients'])],
            'breakdown' => [new Assert\Valid(), new Assert\Count(['min' => 1, 'max' => 12])],
            'totalTaxAmount' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^-?\d{1,12}\.\d{2}$/'])],
            'totalAmount' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^-?\d{1,12}\.\d{2}$/']), new Assert\Callback([$this, 'validateTotals'])],
        ]);
    }

    final public function validateTotals(ConstraintViolationList $violations): void {
        if (!isset($this->breakdown) || !isset($this->totalTaxAmount) || !isset($this->totalAmount)) {
            return;
        }

        $expectedTotalTaxAmount = 0;
        $totalBaseAmount = 0;
        foreach ($this->breakdown as $details) {
            if (!isset($details->taxAmount) || !isset($details->baseAmount)) {
                return;
            }
            $expectedTotalTaxAmount += $details->taxAmount;
            $totalBaseAmount += $details->baseAmount;
        }

        $expectedTotalTaxAmount = number_format($expectedTotalTaxAmount, 2, '.', '');
        if ($this->totalTaxAmount !== $expectedTotalTaxAmount) {
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                "Expected total tax amount of $expectedTotalTaxAmount, got {$this->totalTaxAmount}",
                'totalTaxAmount'
            ));
        }

        $validTotalAmount = false;
        $bestTotalAmount = $totalBaseAmount + $expectedTotalTaxAmount;
        foreach ([0, -0.01, 0.01, -0.02, 0.02] as $tolerance) {
            $expectedTotalAmount = number_format($bestTotalAmount + $tolerance, 2, '.', '');
            if ($this->totalAmount === $expectedTotalAmount) {
                $validTotalAmount = true;
                break;
            }
        }
        if (!$validTotalAmount) {
            $bestTotalAmount = number_format($bestTotalAmount, 2, '.', '');
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                "Expected total amount of $bestTotalAmount, got {$this->totalAmount}",
                'totalAmount'
            ));
        }
    }

    final public function validateRecipients(ConstraintViolationList $violations): void {
        if (!isset($this->invoiceType)) {
            return;
        }

        $hasRecipients = count($this->recipients) > 0;
        if ($this->invoiceType === InvoiceType::Simplificada || $this->invoiceType === InvoiceType::R5) {
            if ($hasRecipients) {
                $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                    'This type of invoice cannot have recipients',
                    'recipients'
                ));
            }
        } elseif (!$hasRecipients) {
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                'This type of invoice requires at least one recipient',
                'recipients'
            ));
        }
    }
}
