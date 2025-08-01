<?php
namespace josemmo\Verifactu\Models\Records;

use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Models\Model;

/**
 * Identificador fiscal
 *
 * @field Caberecera/ObligadoEmision
 * @field Caberecera/Representante
 */
class FiscalIdentifier extends Model {
    /**
     * Class constructor
     *
     * @param string|null $name Name
     * @param string|null $nif  NIF
     */
    public function __construct(
        ?string $name = null,
        ?string $nif = null
    ) {
        if ($name !== null) {
            $this->name = $name;
        }
        if ($nif !== null) {
            $this->nif = $nif;
        }
    }

    /**
     * Nombre-razón social
     *
     * @field NombreRazon
     */
    public string $name;

    /**
     * Número de identificación fiscal (NIF)
     *
     * @field NIF
     */
    public string $nif;

    public function getConstraints(): array {
        return [
            'name' => [new Assert\NotBlank(), new Assert\Length(['max' => 120])],
            'nif' => [new Assert\NotBlank(), new Assert\Length(['exactly' => 9])],
        ];
    }
}
