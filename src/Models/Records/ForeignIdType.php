<?php
namespace josemmo\Verifactu\Models\Records;

class ForeignIdType {
    /** NIF-IVA */
    const VAT = '02';

    /** Pasaporte */
    const Passport = '03';

    /** Documento oficial de identificación expedido por el país o territorio de residencia */
    const NationalId = '04';

    /** Certificado de residencia */
    const Residence = '05';

    /** Otro documento probatorio */
    const Other = '06';

    /** No censado */
    const Unregistered = '07';
}
