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
        'pcpinstallmentcontroller'  => \Payone\PcpPrototype\Controller\InstallmentController::class,
        'pcpapilog_controller'      => \Payone\PcpPrototype\Controller\Admin\ApiLogController::class,
        'pcpapilog_list_controller' => \Payone\PcpPrototype\Controller\Admin\ApiLogListController::class,
        'pcpapilog_main_controller' => \Payone\PcpPrototype\Controller\Admin\ApiLogMainController::class,
        'pcpconfig_upload_controller' => \Payone\PcpPrototype\Controller\Admin\ConfigUploadController::class,
    ],
    'templates' => [
        'pcp_apilog.tpl'            => 'Payone/PcpPrototype/views/admin/tpl/pcp_apilog.tpl',
        'pcp_apilog_list.tpl'       => 'Payone/PcpPrototype/views/admin/tpl/pcp_apilog_list.tpl',
        'pcp_apilog_main.tpl'       => 'Payone/PcpPrototype/views/admin/tpl/pcp_apilog_main.tpl',
        'pcpconfig_upload.tpl'      => 'Payone/PcpPrototype/views/admin/tpl/pcpconfig_upload.tpl',
        'pcpinstallment.html.twig'  => 'Payone/PcpPrototype/views/frontend/twig/page/checkout/pcpinstallment.html.twig',
    ],
    'events' => [
        'onActivate'   => '\Payone\PcpPrototype\Core\Events::onActivate',
        'onDeactivate' => '\Payone\PcpPrototype\Core\Events::onDeactivate',
    ],
    'blocks' => [
        // === Admin-Blocks (Smarty) ===
        [
            'template' => 'module_config.tpl',
            'block'    => 'admin_module_config_form',
            'file'     => 'views/admin/blocks/pcp_admin_module_config_form.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_send_form',
            'file'     => 'views/admin/blocks/pcp_admin_order_overview_send_form.tpl',
        ],
        // === Frontend-Blocks (Twig für Apex) ===
        [
            'template' => 'page/checkout/payment.html.twig',
            'block'    => 'select_payment',
            'file'     => 'views/frontend/blocks/pcp_payment_select_override.html.twig',
        ],
        [
            'template' => 'page/checkout/payment.html.twig',
            'block'    => 'change_payment',
            'file'     => 'views/frontend/blocks/pcp_change_payment.html.twig',
        ],
        [
            'template' => 'page/checkout/thankyou.html.twig',
            'block'    => 'checkout_thankyou_proceed',
            'file'     => 'views/frontend/blocks/pcp_thankyou_checkout_thankyou.html.twig',
        ],
        [
            'template' => 'page/checkout/thankyou.html.twig',
            'block'    => 'checkout_thankyou_info',
            'file'     => 'views/frontend/blocks/pcp_checkout_thankyou_info.html.twig',
        ],
        [
            'template' => 'page/checkout/order.html.twig',
            'block'    => 'checkout_order_address',
            'file'     => 'views/frontend/blocks/pcp_checkout_order_address.html.twig',
        ],
        [
            'template' => 'page/checkout/order.html.twig',
            'block'    => 'checkout_order_btn_confirm_bottom',
            'file'     => 'views/frontend/blocks/pcp_checkout_order_btn_confirm_bottom.html.twig',
        ],
        [
            'template' => 'layout/base.html.twig',
            'block'    => 'base_style',
            'file'     => 'views/frontend/blocks/pcp_base_style.html.twig',
        ],
        [
            'template' => 'layout/base.html.twig',
            'block'    => 'base_js',
            'file'     => 'views/frontend/blocks/pcp_base_js.html.twig',
        ],
        [
            'template' => 'layout/header.html.twig',
            'block'    => 'layout_header_logo',
            'file'     => 'views/frontend/blocks/pcp_layout_header_logo.html.twig',
        ],
        [
            'template' => 'page/shop/start.html.twig',
            'block'    => 'start_bargain_articles',
            'file'     => 'views/frontend/blocks/pcp_start_bargain_articles.html.twig',
        ],
        [
            'template' => 'widget/header/categorylist.html.twig',
            'block'    => 'dd_widget_header_categorylist',
            'file'     => 'views/frontend/blocks/pcp_dd_widget_header_categorylist.html.twig',
        ],
        [
            'template' => 'layout/footer.html.twig',
            'block'    => 'dd_footer_manufacturerlist',
            'file'     => 'views/frontend/blocks/pcp_dd_footer_manufacturerlist.html.twig',
        ],
        [
            'template' => 'layout/footer.html.twig',
            'block'    => 'dd_footer_categorytree',
            'file'     => 'views/frontend/blocks/pcp_dd_footer_categorytree.html.twig',
        ],
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