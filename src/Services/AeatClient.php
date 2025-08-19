<?php
namespace josemmo\Verifactu\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use UXML\UXML;
use josemmo\Verifactu\Models\ComputerSystem;
use josemmo\Verifactu\Models\InvoiceQuery;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;
use josemmo\Verifactu\Models\Records\RegistrationRecord;

/**
 * Class to communicate with the AEAT web service endpoint for VERI*FACTU
 */
class AeatClient {
    const NS_SOAPENV = 'http://schemas.xmlsoap.org/soap/envelope/';
    const NS_SUM = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd';
    const NS_SUM1 = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd';

    private ComputerSystem $system;
    private FiscalIdentifier $taxpayer;
    private ?FiscalIdentifier $representative = null;
    private Client $client;
    private bool $isProduction = true;

    /**
     * Class constructor
     *
     * @param ComputerSystem   $system       Computer system details
     * @param FiscalIdentifier $taxpayer     Taxpayer details (party that issues the invoices)
     * @param string           $certPath     Path to encrypted PEM certificate or PKCS#12 bundle
     * @param string|null      $certPassword Certificate password or `null` for none
     */
    public function __construct(
        ComputerSystem $system,
        FiscalIdentifier $taxpayer,
        string $certPath,
        ?string $certPassword = null
    ) {
        $this->system = $system;
        $this->taxpayer = $taxpayer;
        $this->client = new Client([
            'cert' => ($certPassword === null) ? $certPath : [$certPath, $certPassword],
            'headers' => [
                'User-Agent' => "Mozilla/5.0 (compatible; {$system->name}/{$system->version})",
            ],
        ]);
    }

    /**
     * Set representative
     *
     * NOTE: Requires the represented fiscal entity to fill the "GENERALLEY58" form at AEAT.
     *
     * @param  FiscalIdentifier|null $representative Representative details (party that sends the invoices)
     * @return $this                                 This instance
     */
    public function setRepresentative(?FiscalIdentifier $representative) {
        $this->representative = $representative;
        return $this;
    }

    /**
     * Set production environment
     *
     * @param  bool  $production Pass `true` for production, `false` for testing
     * @return $this             This instance
     */
    public function setProduction(bool $production) {
        $this->isProduction = $production;
        return $this;
    }

    /**
     * Send registration records
     *
     * @param  RegistrationRecord[] $records Registration records
     * @return UXML                          XML response from web service
     * @throws GuzzleException if request failed
     */
    public function sendRegistrationRecords(array $records): UXML {
        // Build initial request
        $xml = UXML::newInstance('soapenv:Envelope', null, [
            'xmlns:soapenv' => self::NS_SOAPENV,
            'xmlns:sum' => self::NS_SUM,
            'xmlns:sum1' => self::NS_SUM1,
        ]);
        $xml->add('soapenv:Header');
        $baseElement = $xml->add('soapenv:Body')->add('sum:RegFactuSistemaFacturacion');

        // Add header
        $cabeceraElement = $baseElement->add('sum:Cabecera');
        $obligadoEmisionElement = $cabeceraElement->add('sum1:ObligadoEmision');
        $obligadoEmisionElement->add('sum1:NombreRazon', $this->taxpayer->name);
        $obligadoEmisionElement->add('sum1:NIF', $this->taxpayer->nif);
        if ($this->representative !== null) {
            $representanteElement = $cabeceraElement->add('sum1:Representante');
            $representanteElement->add('sum1:NombreRazon', $this->representative->name);
            $representanteElement->add('sum1:NIF', $this->representative->nif);
        }

        // Add registration records
        foreach ($records as $record) {
            $recordElement = $baseElement->add('sum:RegistroFactura')->add('sum1:RegistroAlta');
            $recordElement->add('sum1:IDVersion', '1.0');

            $idFacturaElement = $recordElement->add('sum1:IDFactura');
            $idFacturaElement->add('sum1:IDEmisorFactura', $record->invoiceId->issuerId);
            $idFacturaElement->add('sum1:NumSerieFactura', $record->invoiceId->invoiceNumber);
            $idFacturaElement->add('sum1:FechaExpedicionFactura', $record->invoiceId->issueDate->format('d-m-Y'));

            $recordElement->add('sum1:NombreRazonEmisor', $record->issuerName);
            $recordElement->add('sum1:TipoFactura', $record->invoiceType);
            $recordElement->add('sum1:DescripcionOperacion', $record->description);

            if (count($record->recipients) > 0) {
                $destinatariosElement = $recordElement->add('sum1:Destinatarios');
                foreach ($record->recipients as $recipient) {
                    $destinatarioElement = $destinatariosElement->add('sum1:IDDestinatario');
                    $destinatarioElement->add('sum1:NombreRazon', $recipient->name);
                    if ($recipient instanceof FiscalIdentifier) {
                        $destinatarioElement->add('sum1:NIF', $recipient->nif);
                    } else {
                        $idOtroElement = $destinatarioElement->add('sum1:IDOtro');
                        $idOtroElement->add('sum1:CodigoPais', $recipient->country);
                        $idOtroElement->add('sum1:IDType', $recipient->type);
                        $idOtroElement->add('sum1:ID', $recipient->value);
                    }
                }
            }

            $desgloseElement = $recordElement->add('sum1:Desglose');
            foreach ($record->breakdown as $breakdownDetails) {
                $detalleDesgloseElement = $desgloseElement->add('sum1:DetalleDesglose');
                $detalleDesgloseElement->add('sum1:Impuesto', $breakdownDetails->taxType);
                $detalleDesgloseElement->add('sum1:ClaveRegimen', $breakdownDetails->regimeType);
                $detalleDesgloseElement->add('sum1:CalificacionOperacion', $breakdownDetails->operationType);
                $detalleDesgloseElement->add('sum1:TipoImpositivo', $breakdownDetails->taxRate);
                $detalleDesgloseElement->add('sum1:BaseImponibleOimporteNoSujeto', $breakdownDetails->baseAmount);
                $detalleDesgloseElement->add('sum1:CuotaRepercutida', $breakdownDetails->taxAmount);
            }

            $recordElement->add('sum1:CuotaTotal', $record->totalTaxAmount);
            $recordElement->add('sum1:ImporteTotal', $record->totalAmount);

            $encadenamientoElement = $recordElement->add('sum1:Encadenamiento');
            if ($record->previousInvoiceId === null) {
                $encadenamientoElement->add('sum1:PrimerRegistro', 'S');
            } else {
                $registroAnteriorElement = $encadenamientoElement->add('sum1:RegistroAnterior');
                $registroAnteriorElement->add('sum1:IDEmisorFactura', $record->previousInvoiceId->issuerId);
                $registroAnteriorElement->add('sum1:NumSerieFactura', $record->previousInvoiceId->invoiceNumber);
                $registroAnteriorElement->add('sum1:FechaExpedicionFactura', $record->previousInvoiceId->issueDate->format('d-m-Y'));
                $registroAnteriorElement->add('sum1:Huella', $record->previousHash);
            }

            $sistemaInformaticoElement = $recordElement->add('sum1:SistemaInformatico');
            $sistemaInformaticoElement->add('sum1:NombreRazon', $this->system->vendorName);
            $sistemaInformaticoElement->add('sum1:NIF', $this->system->vendorNif);
            $sistemaInformaticoElement->add('sum1:NombreSistemaInformatico', $this->system->name);
            $sistemaInformaticoElement->add('sum1:IdSistemaInformatico', $this->system->id);
            $sistemaInformaticoElement->add('sum1:Version', $this->system->version);
            $sistemaInformaticoElement->add('sum1:NumeroInstalacion', $this->system->installationNumber);
            $sistemaInformaticoElement->add('sum1:TipoUsoPosibleSoloVerifactu', $this->system->onlySupportsVerifactu ? 'S' : 'N');
            $sistemaInformaticoElement->add('sum1:TipoUsoPosibleMultiOT', $this->system->supportsMultipleTaxpayers ? 'S' : 'N');
            $sistemaInformaticoElement->add('sum1:IndicadorMultiplesOT', $this->system->hasMultipleTaxpayers ? 'S' : 'N');

            $recordElement->add('sum1:FechaHoraHusoGenRegistro', $record->hashedAt->format('c'));
            $recordElement->add('sum1:TipoHuella', '01'); // SHA-256
            $recordElement->add('sum1:Huella', $record->hash);
        }
        //echo $xml->asXML();
        // Send request
        $response = $this->client->post('/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', [
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'Content-Type' => 'text/xml',
            ],
            'body' => $xml->asXML(),
        ]);
        return UXML::fromString($response->getBody()->getContents());
    }

    /**
     * Query invoices from AEAT
     *
     * @param  InvoiceQuery $query Query parameters
     * @return UXML                XML response from web service
     * @throws GuzzleException if request failed
     */
    public function queryInvoices(InvoiceQuery $query): UXML {
        // Validate the query
        $query->validate();

        // Build initial request with correct namespaces
        $xml = UXML::newInstance('soapenv:Envelope', null, [
            'xmlns:soapenv' => self::NS_SOAPENV,
            'xmlns:con' => 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/ConsultaLR.xsd',
            'xmlns:sum' => self::NS_SUM1,
        ]);
        $xml->add('soapenv:Header');
        $baseElement = $xml->add('soapenv:Body')->add('con:ConsultaFactuSistemaFacturacion');

        // Add header with IDVersion (required)
        $cabeceraElement = $baseElement->add('con:Cabecera');
        $cabeceraElement->add('sum:IDVersion', '1.0');

        $obligadoEmisionElement = $cabeceraElement->add('sum:ObligadoEmision');
        $obligadoEmisionElement->add('sum:NombreRazon', $this->taxpayer->name);
        $obligadoEmisionElement->add('sum:NIF', $this->taxpayer->nif);
        if ($this->representative !== null) {
            $representanteElement = $cabeceraElement->add('sum:Representante');
            $representanteElement->add('sum:NombreRazon', $this->representative->name);
            $representanteElement->add('sum:NIF', $this->representative->nif);
        }

        // Add FiltroConsulta (not just Filtro)
        $filtroElement = $baseElement->add('con:FiltroConsulta');

        // Add PeriodoImputacion (required)
        $periodoElement = $filtroElement->add('con:PeriodoImputacion');
        $periodoElement->add('sum:Ejercicio', $query->year);
        $periodoElement->add('sum:Periodo', $query->period);

        // Optional filters
        if ($query->seriesNumber !== null) {
            $filtroElement->add('con:NumSerieFactura', $query->seriesNumber);
        }

        $counterparty = $query->getCounterparty();
        if ($counterparty !== null) {
            $contraparteElement = $filtroElement->add('sum:Contraparte');
            if (isset($counterparty['nif'])) {
                $contraparteElement->add('sum:NIF', $counterparty['nif']);
            }
            if (isset($counterparty['name'])) {
                $contraparteElement->add('sum:NombreRazon', $counterparty['name']);
            }
        }

        if ($query->issueDate !== null) {
            $filtroElement->add('sum:FechaExpedicionFactura', $query->issueDate);
        }

        $systemInfo = $query->getSystemInfo();
        if ($systemInfo !== null) {
            $sistemaElement = $filtroElement->add('sum:SistemaInformatico');
            $sistemaElement->add('sum:NombreSistemaInformatico', $systemInfo['system']);
            $sistemaElement->add('sum:Version', $systemInfo['version']);
        }

        if ($query->externalRef !== null) {
            $filtroElement->add('sum:RefExterna', $query->externalRef);
        }

        $paginationKey = $query->getPaginationKey();
        if ($paginationKey !== null) {
            $claveElement = $filtroElement->add('sum:ClavePaginacion');
            $claveElement->add('sum:Pagina', (string)$paginationKey['page']);
            $claveElement->add('sum:TamanoPagina', (string)$paginationKey['size']);
        }

        $xmlstring = $xml->asXML();
        // die();
        // Send request to the correct endpoint (same as registration)
        $response = $this->client->post('/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP', [
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'Content-Type' => 'text/xml',
            ],
            'body' => $xmlstring,
        ]);
        $xml_response = UXML::fromString($response->getBody()->getContents());

        //die($xmlstring . "\n\n" . $xml_response->asXML());
        return $xml_response;
    }

    /**
     * Get base URI of web service
     *
     * @return string Base URI
     */
    private function getBaseUri(): string {
        return $this->isProduction ? 'https://www1.aeat.es' : 'https://prewww1.aeat.es';
    }
}
