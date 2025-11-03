<?php
namespace josemmo\Verifactu\Models\Records;

class GeneratorType {
    /**
     * Issuer (obliged to issue the cancelled invoice)
     */
    public const ISSUER = 'E';

    /**
     * Recipient
     */
    public const RECIPIENT = 'D';

    /**
     * Third party
     */
    public const THIRD_PARTY = 'T';
}
