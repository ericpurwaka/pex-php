<?php

return [

    'BASE_PRODUCTION' => 'https://coreapi.pexcard.com/v42/',
    'BASE_SANDBOX' => 'https://corebeta.pexcard.com/api/v42/',

    'urls' => [
        'masteraccountdetails'  => 'Details/AccountDetails',
        'accountdetails'        => 'Details/AccountDetails/{id}',
        'accountfund'           => 'Card/Fund/{id}',
        'cardupdatestatus'      => 'Card/Status/{id}',
        'cardactivate'          => 'Card/Activate/{id}',
        'defundall'             => 'Bulk/Zero'
    ]

];