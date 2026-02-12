<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'pcpprototype',
    'title'       => [
        'de' => 'PAYONE PCP Checkout Prototyp',
        'en' => 'PAYONE PCP Checkout Prototype',
    ],
    'description' => [
        'de' => 'Ein Prototyp-Modul zur Demonstration des PAYONE Commerce Platform Checkout-Prozesses.',
        'en' => 'A prototype module to demonstrate the PAYONE Commerce Platform checkout process.',
    ],
    'thumbnail'   => 'logo.png',
    'version'     => '1.0.0',
    'author'      => 'PAYONE GmbH',
    'url'         => 'https://www.payone.com',
    'email'       => 'support@payone.com',
    'controllers'  => [
        'payone_checkout' => \Payone\PcpPrototype\Controller\CheckoutController::class,
        'payone_redirect' => \Payone\PcpPrototype\Controller\RedirectController::class,
        'payone_thankyou' => \Payone\PcpPrototype\Controller\ThankyouController::class,
    ],
    'extend'      => [
        \OxidEsales\Eshop\Application\Model\Basket::class => \Payone\PcpPrototype\Model\Basket::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => \Payone\PcpPrototype\Controller\PaymentController::class,
    ],
    'events'      => [
        'onActivate'   => '\Payone\PcpPrototype\Core\Events::onActivate',
        'onDeactivate' => '\Payone\PcpPrototype\Core\Events::onDeactivate',
    ],
    'templates'   => [
        'payone_checkout.tpl'  => 'Application/views/pages/payone_checkout.tpl',
        'payone_redirect.tpl'  => 'Application/views/pages/payone_redirect.tpl',
        'payone_thankyou.tpl'  => 'Application/views/pages/payone_thankyou.tpl',
    ],
    'settings' => [
        [
            'group' => 'main',
            'name' => 'pcpMerchantId',
            'type' => 'str',
        ],
        [
            'group' => 'main',
            'name' => 'pcpApiEndpoint',
            'type' => 'str',
            'value' => 'https://api.preprod.commerce.payone.com'
        ],
        [
            'group' => 'main',
            'name' => 'pcpApiKey',
            'type' => 'str',
        ],
        [
            'group' => 'main',
            'name' => 'pcpApiSecret',
            'type' => 'str',
        ],
        [
            'group' => 'main',
            'name' => 'pcpDemoPaymentToken',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'main',
            'name' => 'pcpUseFixedNoregestration',
            'type' => 'bool',
            'value' => true
        ],
        [
            'group' => 'main',
            'name' => 'pcpUseFixedShipping',
            'type' => 'bool',
            'value' => true
        ],
        [
            'group' => 'main',
            'name' => 'pcpShowDemoShopButton',
            'type' => 'bool',
            'value' => true
        ],
        [
            'group' => 'main',
            'name' => 'pcpFixedShippingName',
            'type' => 'str',
            'value' => 'Shop-Company'
        ],
        [
            'group' => 'main',
            'name' => 'pcpFixedShippingStreet',
            'type' => 'str',
            'value' => 'Shop-Company Street'
        ],
        [
            'group' => 'main',
            'name' => 'pcpFixedShippingStreetNr',
            'type' => 'str',
            'value' => '123'
        ],
        [
            'group' => 'main',
            'name' => 'pcpFixedShippingZip',
            'type' => 'str',
            'value' => '40474'
        ],
        [
            'group' => 'main',
            'name' => 'pcpFixedShippingCity',
            'type' => 'str',
            'value' => 'Düsseldorf'
        ],
        [
            'group' => 'main',
            'name' => 'pcpUseCustomShopLogo',
            'type' => 'bool',
            'value' => false
        ],
        [
            'group' => 'main',
            'name' => 'pcpUseCustomColors',
            'type' => 'bool',
            'value' => false
        ],
        [
            'group' => 'main',
            'name' => 'pcpPrimaryColor',
            'type' => 'str',
            'value' => '#0096d6'
        ],
        [
            'group' => 'main',
            'name' => 'pcpSecondaryColorBright',
            'type' => 'str',
            'value' => '#87cdec'
        ],
        [
            'group' => 'main',
            'name' => 'pcpSecondaryColorDark',
            'type' => 'str',
            'value' => '#005a80'
        ],
        [
            'group' => 'main',
            'name' => 'pcpTertiaryColor1',
            'type' => 'str',
            'value' => '#005a80'
        ],
        [
            'group' => 'main',
            'name' => 'pcpDangerColor',
            'type' => 'str',
            'value' => '#f50057'
        ],
        [
            'group' => 'main',
            'name' => 'pcpWarningColor',
            'type' => 'str',
            'value' => '#f50057'
        ],
        [
            'group' => 'main',
            'name' => 'pcpBlackColor',
            'type' => 'str',
            'value' => '#0d0d0d'
        ],
    ]
];