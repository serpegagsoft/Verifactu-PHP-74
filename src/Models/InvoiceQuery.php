<?php
namespace josemmo\Verifactu\Models;

/**
 * Model representing a query/filter for submitted invoices.
 * Based on: ConsultaFactuSistemaFacturacionType (ConsultaLR.xsd.xml)
 */
class InvoiceQuery extends Model {
    /**
     * Tax year (Ejercicio)
     * @var string
     */
    public string $year;

    /**
     * Period (Periodo), usually month or quarter
     * @var string
     */
    public string $period;

    /**
     * Series + invoice number to filter (NumSerieFactura, optional)
     * @var string|null
     */
    public ?string $seriesNumber = null;

    /**
     * Counterparty information (Contraparte, optional)
     * @var array|null
     */
    private ?array $counterparty = null;

    /**
     * Issue date filter (FechaExpedicionFactura, optional)
     * @var string|null
     */
    public ?string $issueDate = null;

    /**
     * System information filter (SistemaInformatico, optional)
     * @var array|null
     */
    private ?array $systemInfo = null;

    /**
     * External reference filter (RefExterna, optional)
     * @var string|null
     */
    public ?string $externalRef = null;

    /**
     * Pagination key (ClavePaginacion, optional)
     * @var array|null
     */
    private ?array $paginationKey = null;

    /**
     * InvoiceQuery constructor
     *
     * @param string $year   Tax year
     * @param string $period Period
     */
    public function __construct(string $year, string $period) {
        $this->year = $year;
        $this->period = $period;
    }

    /**
     * Get the counterparty information
     * @return array|null
     */
    public function getCounterparty(): ?array {
        return $this->counterparty;
    }

    /**
     * Set the counterparty information
     * @param string      $nif  Counterparty NIF
     * @param string|null $name Counterparty name (optional)
     * @return $this
     */
    public function setCounterparty(string $nif, ?string $name = null): self {
        $this->counterparty = [
            'nif' => $nif
        ];

        if ($name !== null) {
            $this->counterparty['name'] = $name;
        }

        return $this;
    }

    /**
     * Get the system information
     * @return array|null
     */
    public function getSystemInfo(): ?array {
        return $this->systemInfo;
    }

    /**
     * Set the system information
     * @param string $system  System name
     * @param string $version System version
     * @return $this
     */
    public function setSystemInfo(string $system, string $version): self {
        $this->systemInfo = [
            'system' => $system,
            'version' => $version
        ];
        return $this;
    }

    /**
     * Get the pagination key
     * @return array|null
     */
    public function getPaginationKey(): ?array {
        return $this->paginationKey;
    }

    /**
     * Set the pagination key
     * @param int $page Page number
     * @param int $size Page size
     * @return $this
     */
    public function setPaginationKey(int $page, int $size): self {
        $this->paginationKey = [
            'page' => $page,
            'size' => $size
        ];
        return $this;
    }

    /**
     * Get constraints for validation
     * @return array<string,mixed>
     */
    public function getConstraints(): array {
        return [
            'year' => [
                new \josemmo\Verifactu\Validation\Constraints\NotBlank(),
                new \josemmo\Verifactu\Validation\Constraints\Type('string'),
                new \josemmo\Verifactu\Validation\Constraints\Regex(['pattern' => '/^\d{4}$/'])
            ],
            'period' => [
                new \josemmo\Verifactu\Validation\Constraints\NotBlank(),
                new \josemmo\Verifactu\Validation\Constraints\Type('string'),
                new \josemmo\Verifactu\Validation\Constraints\Regex(['pattern' => '/^\w+$/'])
            ],
            'seriesNumber' => [
                new \josemmo\Verifactu\Validation\Constraints\Type('string', true)
            ],
            'issueDate' => [
                new \josemmo\Verifactu\Validation\Constraints\Type('string', true),
                new \josemmo\Verifactu\Validation\Constraints\Regex(['pattern' => '/^\d{2}-\d{2}-\d{4}$/'], true)
            ],
            'externalRef' => [
                new \josemmo\Verifactu\Validation\Constraints\Type('string', true)
            ]
        ];
    }

    /**
     * Serializes the invoice query to XML.
     * 
     * @return \DOMDocument
     * @throws \DOMException
     */
    public function toXml(): \DOMDocument {
        // Create the XML document
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // Create root element: ConsultaFactuSistemaFacturacion
        $root = $doc->createElement('ConsultaFactuSistemaFacturacion');
        $doc->appendChild($root);

        // Ejercicio (required)
        $root->appendChild($doc->createElement('Ejercicio', $this->year));

        // Periodo (required)
        $root->appendChild($doc->createElement('Periodo', $this->period));

        // NumSerieFactura (optional)
        if (!empty($this->seriesNumber)) {
            $root->appendChild($doc->createElement('NumSerieFactura', $this->seriesNumber));
        }

        // Contraparte (optional)
        if (!empty($this->counterparty) && is_array($this->counterparty)) {
            $contraparteNode = $doc->createElement('Contraparte');
            if (!empty($this->counterparty['nif'])) {
                $contraparteNode->appendChild($doc->createElement('NIF', $this->counterparty['nif']));
            }
            if (!empty($this->counterparty['name'])) {
                $contraparteNode->appendChild($doc->createElement('NombreRazon', $this->counterparty['name']));
            }
            if (!empty($this->counterparty['otherId'])) {
                $contraparteNode->appendChild($doc->createElement('OtroID', $this->counterparty['otherId']));
            }
            $root->appendChild($contraparteNode);
        }

        // FechaExpedicionFactura (optional)
        if (!empty($this->issueDate)) {
            $root->appendChild($doc->createElement('FechaExpedicionFactura', $this->issueDate));
        }

        // SistemaInformatico (optional)
        if (!empty($this->systemInfo) && is_array($this->systemInfo)) {
            $sistemaNode = $doc->createElement('SistemaInformatico');
            foreach ($this->systemInfo as $key => $value) {
                $sistemaNode->appendChild($doc->createElement($key, $value));
            }
            $root->appendChild($sistemaNode);
        }

        // RefExterna (optional)
        if (!empty($this->externalRef)) {
            $root->appendChild($doc->createElement('RefExterna', $this->externalRef));
        }

        // ClavePaginacion (optional)
        if (!empty($this->paginationKey) && is_array($this->paginationKey)) {
            $claveNode = $doc->createElement('ClavePaginacion');
            foreach ($this->paginationKey as $key => $value) {
                $claveNode->appendChild($doc->createElement($key, $value));
            }
            $root->appendChild($claveNode);
        }

        return $doc;
    }
}