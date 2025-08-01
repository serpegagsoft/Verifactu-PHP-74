<?php
namespace josemmo\Verifactu\Models;

use josemmo\Verifactu\Validation\Constraints as Assert;

/**
 * Computer system
 *
 * @field SistemaInformatico
 */
class ComputerSystem extends Model {
    /**
     * Nombre-razón social de la persona o entidad productora
     *
     * @field NombreRazon
     */
    public string $vendorName;

    /**
     * NIF de la persona o entidad productora
     *
     * @field NIF
     */
    public string $vendorNif;

    /**
     * Nombre dado por la persona o entidad productora a su sistema informático de facturación (SIF)
     *
     * @field NombreSistemaInformatico
     */
    public string $name;

    /**
     * Código identificativo dado por la persona o entidad productora a su sistema informático de facturación (SIF)
     *
     * @field IdSistemaInformatico
     */
    public string $id;

    /**
     * Identificación de la versión del sistema informático de facturación (SIF)
     *
     * @field Version
     */
    public string $version;

    /**
     * Número de instalación del sistema informático de facturación (SIF) utilizado
     *
     * @field NumeroInstalacion
     */
    public string $installationNumber;

    /**
     * Especifica si solo puede funcionar como "VERI*FACTU" o también puede funcionar como "no VERI*FACTU" (offline)
     *
     * @field TipoUsoPosibleSoloVerifactu
     */
    public bool $onlySupportsVerifactu;

    /**
     * Especifica si permite llevar independientemente la facturación de varios obligados tributarios
     *
     * @field TipoUsoPosibleMultiOT
     */
    public bool $supportsMultipleTaxpayers;

    /**
     * En el momento de la generación de este registro, está soportando la facturación de más de un obligado tributario
     *
     * @field IndicadorMultiplesOT
     */
    public bool $hasMultipleTaxpayers;

    public function getConstraints(): array {
        return [
            'vendorName' => [new Assert\NotBlank(), new Assert\Length(['max' => 120])],
            'vendorNif' => [new Assert\NotBlank(), new Assert\Length(['exactly' => 9])],
            'name' => [new Assert\NotBlank(), new Assert\Length(['max' => 30])],
            'id' => [new Assert\NotBlank(), new Assert\Length(['max' => 2])],
            'version' => [new Assert\NotBlank(), new Assert\Length(['max' => 50])],
            'installationNumber' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
            'onlySupportsVerifactu' => [new Assert\Type('boolean')],
            'supportsMultipleTaxpayers' => [new Assert\Type('boolean')],
            'hasMultipleTaxpayers' => [new Assert\Type('boolean')],
        ];
    }
}
