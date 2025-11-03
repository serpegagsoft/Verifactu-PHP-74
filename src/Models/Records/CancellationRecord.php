<?php
namespace josemmo\Verifactu\Models\Records;

use josemmo\Verifactu\Validation\Constraints as Assert;
use josemmo\Verifactu\Validation\ConstraintViolationList;

/**
 * Registro de alta de una factura
 *
 * @field RegistroAlta
 */
class CancellationRecord extends Record {


    /**
     * No previous record found indicator (SinRegistroPrevio, optional) S/N
     * @var string|null
     */
    public $noPreviousRecord;

    /**
     * Previous rejection indicator (RechazoPrevio, optional) S/N
     * @var string|null
     */
    public $previousRejection;

    /**
     * Generator (GeneradoPor, optional) \josemmo\Verifactu\Models\GeneratorType
     * @var string|null
     */
    public $generator;

    /**
     * Generator data (Generador, optional)
     * @var object|null
     */
    public $generatorData;


    /**
     * @inheritDoc
     */
    public function calculateHash(): string {
        // NOTE: Values should NOT be escaped as that what the AEAT says ¯\_(ツ)_/¯
        $payload  = 'IDEmisorFacturaAnulada=' . $this->invoiceId->issuerId;
        $payload .= '&NumSerieFacturaAnulada=' . $this->invoiceId->invoiceNumber;
        $payload .= '&FechaExpedicionFacturaAnulada=' . $this->invoiceId->issueDate->format('d-m-Y');
        $payload .= '&Huella=' . ($this->previousHash ?? '');
        $payload .= '&FechaHoraHusoGenRegistro=' . $this->hashedAt->format('c');
        return strtoupper(hash('sha256', $payload));
    }

    public function getConstraints(): array {
        return  array_merge(parent::getConstraints(), [
            'description' => [new Assert\NotBlank(), new Assert\Length(['max' => 500])],
            'noPreviousRecord' => [new Assert\Regex(['pattern' => '/^(|S|N)$/'])],
            'previousRejection' => [new Assert\Regex(['pattern' => '/^(|S|N)$/'])],
            'generator' => [new Assert\Regex(['pattern' => '/^(|E|D|T)$/'])],

        ]);
    }

}
