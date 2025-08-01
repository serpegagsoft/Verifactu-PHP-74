<?php
namespace josemmo\Verifactu\Models\Records;

use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Validation\ConstraintViolationList;
use josemmo\Verifactu\Models\Model;

/**
 * Detalle de desglose
 *
 * @field DetalleDesglose
 */
class BreakdownDetails extends Model {
    /**
     * Impuesto de aplicación
     *
     * @field Impuesto
     */
    public $taxType;

    /**
     * Clave que identifica el tipo de régimen del impuesto o una operación con trascendencia tributaria
     *
     * @field ClaveRegimen
     */
    public $regimeType;

    /**
     * Clave de la operación sujeta y no exenta o de la operación no sujeta
     *
     * @field CalificacionOperacion
     */
    public $operationType;

    /**
     * Porcentaje aplicado sobre la base imponible para calcular la cuota
     *
     * @field TipoImpositivo
     */
    public string $taxRate;

    /**
     * Magnitud dineraria sobre la que se aplica el tipo impositivo / Importe no sujeto
     *
     * @field BaseImponibleOimporteNoSujeto
     */
    public string $baseAmount;

    /**
     * Cuota resultante de aplicar a la base imponible el tipo impositivo
     *
     * @field CuotaRepercutida
     */
    public string $taxAmount;

    public function getConstraints(): array {
        return [
            'taxType' => [new Assert\NotBlank()],
            'regimeType' => [new Assert\NotBlank()],
            'operationType' => [new Assert\NotBlank()],
            'taxRate' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^\d{1,3}\.\d{2}$/'])],
            'baseAmount' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^-?\d{1,12}\.\d{2}$/'])],
            'taxAmount' => [
                new Assert\NotBlank(),
                new Assert\Regex(['pattern' => '/^-?\d{1,12}\.\d{2}$/']),
                new Assert\Callback([$this, 'validateTaxAmount'])
            ],
        ];
    }

    final public function validateTaxAmount(ConstraintViolationList $violations): void {
        if (!isset($this->taxRate) || !isset($this->baseAmount) || !isset($this->taxAmount)) {
            return;
        }

        $validTaxAmount = false;
        $bestTaxAmount = (float) $this->baseAmount * ((float) $this->taxRate / 100);
        foreach ([0, -0.01, 0.01, -0.02, 0.02] as $tolerance) {
            $expectedTaxAmount = number_format($bestTaxAmount + $tolerance, 2, '.', '');
            if ($this->taxAmount === $expectedTaxAmount) {
                $validTaxAmount = true;
                break;
            }
        }
        if (!$validTaxAmount) {
            $bestTaxAmount = number_format($bestTaxAmount, 2, '.', '');
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                "Expected tax amount of $bestTaxAmount, got {$this->taxAmount}",
                'taxAmount'
            ));
        }
    }
}
