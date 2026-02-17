<?php

$sMetadataVersion = '2.1';

$aModule = [
    'id' => 'PayonePcpPrototype',
    'title' => [
        'de' => 'PAYONE PCP für OXID eShop',
        'en' => 'PAYONE PCP for OXID eShop',
    ],
    'description' => [
        'de' => 'Demo-Plugin für die PCP-Omnichannel-Plattform "PAYONE COMMERCE PLATFORM".',
        'en' => 'Demo-Plugin for the PCP omnichannel platform "PAYONE COMMERCE PLATFORM".',
    ],
    'thumbnail' => 'picture.gif',
    'version' => '2.0.0',
    'author' => 'PAYONE GmbH',
    'email' => 'integrations@payone.com',
    'url' => 'https://docs.payone.com/pcp/payone-commerce-platform',
    'extend' => [
        \OxidEsales\Eshop\Core\ViewConfig::class
        => \Payone\PcpPrototype\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class
        => \Payone\PcpPrototype\Controller\Admin\ModuleConfigController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class
        => \Payone\PcpPrototype\Controller\Admin\OrderOverviewController::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class
        => \Payone\PcpPrototype\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class
        => \Payone\PcpPrototype\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\ThankYouController::class
        => \Payone\PcpPrototype\Controller\ThankyouController::class,
        \OxidEsales\Eshop\Application\Controller\AccountUserController::class
        => \Payone\PcpPrototype\Controller\AccountUserController::class,
        \OxidEsales\Eshop\Application\Controller\UserController::class
        => \Payone\PcpPrototype\Controller\UserController::class,
        \OxidEsales\Eshop\Application\Model\Order::class
        => \Payone\PcpPrototype\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\Payment::class
        => \Payone\PcpPrototype\Model\Payment::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class
        => \Payone\PcpPrototype\Model\PaymentGateway::class,
    ],
    'controllers' => [
        'PcpInstallmentController'  => \Payone\PcpPrototype\Controller\InstallmentController::class,
        'PcpApiLogController'      => \Payone\PcpPrototype\Controller\Admin\ApiLogController::class,
        'PcpApiLogListController' => \Payone\PcpPrototype\Controller\Admin\ApiLogListController::class,
        'PcpApiLogMainController' => \Payone\PcpPrototype\Controller\Admin\ApiLogMainController::class,
        'PcpConfigUploadController' => \Payone\PcpPrototype\Controller\Admin\ConfigUploadController::class,
    ],
    'events' => [
        'onActivate'   => '\Payone\PcpPrototype\Core\Events::onActivate',
        'onDeactivate' => '\Payone\PcpPrototype\Core\Events::onDeactivate',
    ],
    'settings' => [
        [
            'group' => 'main',
            'name'  => 'pcpMerchantId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpApiEndpoint',
            'type'  => 'str',
            'value' => 'https://api.preprod.commerce.payone.com',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpApiKey',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpApiSecret',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpDemoPaymentToken',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpUseFixedNoregestration',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'main',
            'name'  => 'pcpUseFixedShipping',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'main',
            'name'  => 'pcpShowDemoShopButton',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'main',
            'name'  => 'pcpFixedShippingName',
            'type'  => 'str',
            'value' => 'Shop-Company',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpFixedShippingStreet',
            'type'  => 'str',
            'value' => 'Shop-Company Street',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpFixedShippingStreetNr',
            'type'  => 'str',
            'value' => '123',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpFixedShippingZip',
            'type'  => 'str',
            'value' => '40474',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpFixedShippingCity',
            'type'  => 'str',
            'value' => 'Düsseldorf',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpUseCustomShopLogo',
            'type'  => 'bool',
            'value' => false,
        ],
        [
            'group' => 'main',
            'name'  => 'pcpUseCustomColors',
            'type'  => 'bool',
            'value' => false,
        ],
        [
            'group' => 'main',
            'name'  => 'pcpPrimaryColor',
            'type'  => 'str',
            'value' => '#0096d6',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpSecondaryColorBright',
            'type'  => 'str',
            'value' => '#87cdec',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpSecondaryColorDark',
            'type'  => 'str',
            'value' => '#005a80',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpTertiaryColor1',
            'type'  => 'str',
            'value' => '#005a80',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpDangerColor',
            'type'  => 'str',
            'value' => '#f50057',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpWarningColor',
            'type'  => 'str',
            'value' => '#f50057',
        ],
        [
            'group' => 'main',
            'name'  => 'pcpBlackColor',
            'type'  => 'str',
            'value' => '#0d0d0d',
        ],
    ],
];