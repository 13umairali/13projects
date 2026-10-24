<?php

/*
 * Pakistan FBR Digital Invoicing (PRAL DI API) endpoints, following the
 * official "Technical Specification for DI API" (PRAL, 2025). The gateway
 * host is shared; sandbox routes use the *_sb method names and require a
 * scenarioId in the payload, production routes omit both.
 */
return [

    // Default environment for new tenants.
    'default_environment' => 'sandbox',

    'base_url' => 'https://gw.fbr.gov.pk',

    'environments' => [
        'sandbox' => [
            'label' => 'Sandbox (Testing)',
            'post_path' => '/di_data/v1/di/postinvoicedata_sb',
            'validate_path' => '/di_data/v1/di/validateinvoicedata_sb',
        ],
        'production' => [
            'label' => 'Production',
            'post_path' => '/di_data/v1/di/postinvoicedata',
            'validate_path' => '/di_data/v1/di/validateinvoicedata',
        ],
    ],

    // Reference (lookup) APIs — same for both environments.
    'reference' => [
        'provinces' => '/pdi/v1/provinces',
        'doctypes' => '/pdi/v1/doctypecode',
        'hscodes' => '/pdi/v1/itemdesccode',
        'sroitems' => '/pdi/v1/sroitemcode',
        'transtypes' => '/pdi/v1/transtypecode',
        'uom' => '/pdi/v1/uom',
    ],

    // HTTP timeout (seconds) for calls to the FBR gateway.
    'timeout' => (int) env('FBR_HTTP_TIMEOUT', 30),

    // Sandbox test scenarios (Annexure-B of the DI spec). The applicable
    // set depends on the seller's registered business activity/sector.
    'scenarios' => [
        'SN001' => 'Goods at standard rate to registered buyers',
        'SN002' => 'Goods at standard rate to unregistered buyers',
        'SN003' => 'Sale of Steel (Melted and Re-Rolled)',
        'SN004' => 'Sale by Ship Breakers',
        'SN005' => 'Reduced rate sale',
        'SN006' => 'Exempt goods sale',
        'SN007' => 'Zero rated sale',
        'SN008' => 'Sale of 3rd schedule goods',
        'SN009' => 'Purchase from registered cotton ginners (Textile)',
        'SN010' => 'Mobile operators add-on sale (Telecom)',
        'SN011' => 'Toll manufacturing sale by steel sector',
        'SN012' => 'Sale of petroleum products',
        'SN013' => 'Electricity supply to retailers',
        'SN014' => 'Sale of gas to CNG stations',
        'SN015' => 'Sale of mobile phones',
        'SN016' => 'Processing / conversion of goods',
        'SN017' => 'Sale of goods where FED is charged in ST mode',
        'SN018' => 'Sale of services where FED is charged in ST mode',
        'SN019' => 'Sale of services',
        'SN020' => 'Sale of electric vehicles',
        'SN021' => 'Sale of cement / concrete block',
        'SN022' => 'Sale of potassium chlorate',
        'SN023' => 'Sale of CNG',
        'SN024' => 'Goods as per SRO.297(I)/2023',
        'SN025' => 'Drugs at fixed ST rate (serial 81, 8th schedule)',
        'SN026' => 'Sale to end consumer by retailers (standard rate)',
        'SN027' => 'Sale to end consumer by retailers (3rd schedule)',
        'SN028' => 'Sale to end consumer by retailers (reduced rate)',
    ],

    // Fallback province list (mirrors /pdi/v1/provinces) so the settings
    // form works even before credentials are configured.
    'provinces' => [
        'Punjab',
        'Sindh',
        'Khyber Pakhtunkhwa',
        'Balochistan',
        'Gilgit-Baltistan',
        'Azad Jammu and Kashmir',
        'Capital Territory',
    ],
];
