<?php
namespace josemmo\Verifactu\Models\Records;

use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Validation\ConstraintViolationList;
use josemmo\Verifactu\Models\Model;

/**
 * Identificador fiscal de fuera de España
 *
 * @field Caberecera/ObligadoEmision
 * @field Caberecera/Representante
 * @field RegistroAlta/Tercero
 * @field IDDestinatario
 */
class ForeignFiscalIdentifier extends Model {
    /**
     * Nombre-razón social
     *
     * @field NombreRazon
     */
    public string $name;

    /**
     * Código del país (ISO 3166-1 alpha-2 codes)
     *
     * @field IDOtro/CodigoPais
     */
    public string $country;

    /**
     * Clave para establecer el tipo de identificación en el país de residencia
     *
     * @field IDOtro/IDType
     */
    public $type;

    /**
     * Número de identificación en el país de residencia
     *
     * @field IDOtro/ID
     */
    public string $value;

    public function getConstraints(): array {
        return [
            'name' => [new Assert\NotBlank(), new Assert\Length(['max' => 120])],
            'country' => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^[A-Z]{2}$/']), new Assert\Callback([$this, 'validateCountry'])],
            'type' => [new Assert\NotBlank()],
            'value' => [new Assert\NotBlank(), new Assert\Length(['max' => 20])],
        ];
    }

    final public function validateCountry(ConstraintViolationList $violations): void {
        if (isset($this->country) && $this->country === 'ES') {
            $violations->add(new \josemmo\Verifactu\Validation\ConstraintViolation(
                'Country code cannot be "ES", use the `FiscalIdentifier` model instead',
                'country'
            ));
        }
    }
}
