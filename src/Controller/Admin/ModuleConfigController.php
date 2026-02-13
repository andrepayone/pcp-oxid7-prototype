<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Model\Payment;

class ModuleConfigController extends ModuleConfigController_parent
{
    protected static string $sContentsTable = 'oxcontents';
    protected static string $sShopsTable = 'oxshops';
    protected static string $sDeliveryTypeTable = 'oxdeliveryset';
    protected static string $sDeliveryCostTable = 'oxdelivery';
    protected static string $sDelivery2CostTable = 'oxdel2delset';
    protected static string $sDelivery2Payment = 'oxobject2payment';

    protected static array $aProductPictures = [
        'hopper_1.jpg',
        'payone_backpack_1.png',
        'payone_shirt_1.png',
        'Shopper_1.jpg',
        'Shopper_2.jpg',
        'Shopper_3.jpg',
    ];

    protected static array $aDeliveryConfig = [
        'pcpdeliverystore' => [
            'pos' => '10',
            'titles' => [
                'Lieferung in Filiale',
                'Deliver to Store',
            ],
            'deliverycosts' => [
                'pcpdeliverycostsstore' => [
                    'titles' => [
                        'Versandkosten für Filiallieferung (Kostenlos)',
                        'Delivery costs for shipping into store (Free of charge)',
                    ],
                    'costs' => 0,
                ],
            ],
            'allowed_payments' => [
                'pcpcreditcard',
                'pcpsecuredebit',
                'pcppaypal',
                'pcpsecureinstallment',
                'pcppayinstore',
            ],
        ],
        'pcpdelivery' => [
            'pos' => '20',
            'titles' => [
                'Lieferung',
                'Delivery',
            ],
            'deliverycosts' => [
                'pcpdeliverycosts' => [
                    'titles' => [
                        'Versandkosten für Lieferung',
                        'Delivery costs for shipping',
                    ],
                    'costs' => 5.9,
                ],
            ],
            'allowed_payments' => [
                'pcpcreditcard',
                'pcpsecuredebit',
                'pcppaypal',
                'pcpsecureinstallment',
            ],
        ],
    ];

    protected static array $aPcpDemoProducts = [
        '1' => [
            'title' => 'Hoptimist',
            'artnum' => 'payone1',
            'longdesc' => "Design Klassiker aus Dänemark\n",
            'price' => 25,
            'pic1' => 'hopper_1.jpg',
            'pic2' => '',
            'pic3' => '',
        ],
        '2' => [
            'title' => 'PAYONE T-Shirt',
            'artnum' => 'payone2',
            'longdesc' => "Farbe: schwarz\nLeichtes, gerade geschnittenes Unisex T-Shirt mit eingesetzten Ärmeln Mit 1x1-Ripp am Nacken und Nackenband aus gleichem Material Doppelnaht-Steppnaht an den Ärmelbündchen und am Saum Legere\nStoffdichte: 155 g/m²\nMaterial: 100% Baumwolle (aus biologischem Anbau)\n",
            'price' => 30,
            'pic1' => 'payone_shirt_1.png',
            'pic2' => '',
            'pic3' => '',
        ],
        '3' => [
            'title' => 'PAYONE Rucksack',
            'artnum' => 'payone3',
            'longdesc' => "Rucksack mit Rollverschluss, gepolstertem Rücken und verstärktem Boden\n",
            'price' => 50,
            'pic1' => 'payone_backpack_1.png',
            'pic2' => '',
            'pic3' => '',
        ],
        '4' => [
            'title' => 'PAYONE SHOPPER BAG',
            'artnum' => 'payone4',
            'longdesc' => "Qualität: Canvas-Baumwolle (15oz)\nGröße: Ca. 38 x 42 x 12 cm\n",
            'price' => 150,
            'pic1' => 'Shopper_1.jpg',
            'pic2' => 'Shopper_2.jpg',
            'pic3' => 'Shopper_3.jpg',
        ],
    ];

    protected static array $aPcpSeoSettings = [
        'de' => [
            'title_prefix' => '',
            'title_suffix' => '',
            'frontpage_title' => 'PAYONE Commerce Platform Demo-Shop',
            'frontpage_meta_description' => 'PAYONE goes Omnichannel mit unserer Commerce-Platform',
            'frontpage_meta_keywords' => 'omnichannel, payone, demo, store, payone-commerce-platform, seamless',
        ],
        'en' => [
            'title_prefix' => '',
            'title_suffix' => '',
            'frontpage_title' => 'PAYONE Commerce Platform Demo-Shop',
            'frontpage_meta_description' => 'PAYONE goes Omnichannel with our Commerce-Platform',
            'frontpage_meta_keywords' => 'omnichannel, payone, demo, store, payone-commerce-platform, seamless',
        ],
    ];

    public function render()
    {
        $render = parent::render();

        $this->_aViewData['isPcpDemoModule'] = false;
        if ($this->_sModuleId === 'PayonePcpPrototype') {
            $this->_aViewData['isPcpDemoModule'] = true;

            $aConfigBools = $this->_aViewData['confbools'];
            $blShowDemoShopButton = (bool) ($aConfigBools['pcpShowDemoShopButton'] ?? false);
            $this->_aViewData['showDemoShopButton'] = $blShowDemoShopButton;
        }

        return $render;
    }

    public function pcpSetupShop(): void
    {
        $this->pcpGenerateDemoArticles();
        $this->pcpGenerateDeliveryCostConfiguration();
        $this->pcpSetSeo();
        $this->pcpSetPaymentMinAmount('pcpsecureinstallment', 300);

        $this->saveConfVars();
        $this->render();
    }

    protected function pcpSetSeo(): void
    {
        try {
            $this->updateShopSeoSettings();
            $this->copyFavIcon();
            $sMessage = "SEO-Settings have been set to demo-mode...<br>";
        } catch (\Exception $e) {
            $sMessage = sprintf(
                "ERROR could not process Updating SEO-Settings! Message: %s",
                $e->getMessage()
            );
        }
        $this->_aViewData['pcpResultMessage'] .= $sMessage;
    }

    protected function pcpGenerateDemoArticles(): void
    {
        try {
            $this->copyArticleDemoPictures();
            $this->deleteAllExistingProductData();
            $this->createPcpDemoArticles();
            $sMessage = "Existing articles have been deleted and demo articles have been generated...<br>";
        } catch (\Exception $e) {
            $sMessage = sprintf(
                "ERROR could not process generating demo articles! Message: %s",
                $e->getMessage()
            );
        }
        $this->_aViewData['pcpResultMessage'] .= $sMessage;
    }

    protected function pcpGenerateDeliveryCostConfiguration(): void
    {
        try {
            $this->deactivateAllDeliveryTypes();
            foreach (self::$aDeliveryConfig as $sDeliveryTypeId => $aDeliveryConfig) {
                $aDeliveryTypeTitles = $aDeliveryConfig['titles'];
                $aDeliveryCosts = $aDeliveryConfig['deliverycosts'];
                $iDeliveryPosition = (int) $aDeliveryConfig['pos'];

                $this->createDeliveryType($sDeliveryTypeId, $aDeliveryTypeTitles, $iDeliveryPosition);

                foreach ($aDeliveryCosts as $sDeliveryCostId => $aDeliveryCostConfig) {
                    $aDeliveryCostTitles = $aDeliveryCostConfig['titles'];
                    $dCosts = (float) $aDeliveryCostConfig['costs'];

                    $this->createDeliveryCostRule($sDeliveryCostId, $aDeliveryCostTitles, $dCosts);

                    $sAssignmentId = md5($sDeliveryCostId . $sDeliveryTypeId);
                    $this->addDeliveryRuleToType($sAssignmentId, $sDeliveryCostId, $sDeliveryTypeId);
                }

                $this->addPaymentsToDeliveryType($sDeliveryTypeId);
            }
            $sMessage = "Delivery costs have been generated...<br>";
        } catch (\Exception $e) {
            $sMessage = sprintf(
                "ERROR could not process generating delivery costs! Message: %s",
                $e->getMessage()
            );
        }
        $this->_aViewData['pcpResultMessage'] .= $sMessage;
    }

    protected function pcpSetPaymentMinAmount(string $sPaymentId, float $dMinAmount): void
    {
        try {
            $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            $oPayment->load($sPaymentId);
            $oPayment->oxpayments__oxfromamount = new Field($dMinAmount);
            $oPayment->save();
            $sMessage = "Configured payment min price for " . $sPaymentId . "...<br>";
        } catch (\Exception $e) {
            $sMessage = sprintf(
                "ERROR! Could not set min amount %d for payment %s Message: %s",
                $dMinAmount,
                $sPaymentId,
                $e->getMessage()
            );
        }
        $this->_aViewData['pcpResultMessage'] .= $sMessage;
    }

    protected function updateShopSeoSettings(): void
    {
        $oDb = DatabaseProvider::getDb();

        $sQuery = sprintf(
            "UPDATE %s SET 
                OXTITLEPREFIX='%s', OXTITLEPREFIX_1='%s',
                OXTITLESUFFIX='%s', OXTITLESUFFIX_1='%s',
                OXSTARTTITLE='%s', OXSTARTTITLE_1='%s'
            WHERE OXID=1 LIMIT 1;",
            self::$sShopsTable,
            self::$aPcpSeoSettings['de']['title_prefix'],
            self::$aPcpSeoSettings['en']['title_prefix'],
            self::$aPcpSeoSettings['de']['title_suffix'],
            self::$aPcpSeoSettings['en']['title_suffix'],
            self::$aPcpSeoSettings['de']['frontpage_title'],
            self::$aPcpSeoSettings['en']['frontpage_title']
        );
        $oDb->execute($sQuery);

        $this->updateContentTable(
            'oxstartmetakeywords',
            self::$aPcpSeoSettings['de']['frontpage_meta_keywords'],
            self::$aPcpSeoSettings['en']['frontpage_meta_keywords']
        );
        $this->updateContentTable(
            'oxstartmetadescription',
            self::$aPcpSeoSettings['de']['frontpage_meta_description'],
            self::$aPcpSeoSettings['en']['frontpage_meta_description']
        );
        $this->updateContentTable(
            'oxstdfooter',
            self::$aPcpSeoSettings['de']['frontpage_meta_description'],
            self::$aPcpSeoSettings['en']['frontpage_meta_description']
        );
    }

    protected function updateContentTable(string $sLoadId, string $sContentDE, string $sContentEN): void
    {
        $sQuery = sprintf(
            "UPDATE %s SET OXCONTENT='%s', OXCONTENT_1='%s' WHERE OXLOADID='%s' LIMIT 1;",
            self::$sContentsTable,
            $sContentDE,
            $sContentEN,
            $sLoadId
        );
        DatabaseProvider::getDb()->execute($sQuery);
    }

    protected function deactivateAllDeliveryTypes(): void
    {
        DatabaseProvider::getDb()->execute(
            sprintf('UPDATE %s SET OXACTIVE=0 WHERE 1', self::$sDeliveryTypeTable)
        );
    }

    protected function addPaymentsToDeliveryType(string $sDeliveryTypeId): void
    {
        $aAllPayments = Payment::getPcpPaymentTypes();
        $aAllowedPaymentIds = self::$aDeliveryConfig[$sDeliveryTypeId]['allowed_payments'];

        foreach ($aAllPayments as $sPaymentId) {
            if (in_array($sPaymentId, $aAllowedPaymentIds, true)) {
                $this->addPaymentToDeliveryType($sPaymentId, $sDeliveryTypeId);
            }
        }
    }

    protected function addPaymentToDeliveryType(string $sPaymentId, string $sDeliveryTypeId): void
    {
        $sOxid = md5($sPaymentId . $sDeliveryTypeId);
        DatabaseProvider::getDb()->execute(sprintf(
            "INSERT INTO %s (OXID, OXPAYMENTID, OXOBJECTID, OXTYPE) VALUES ('%s', '%s', '%s', '%s')",
            self::$sDelivery2Payment,
            $sOxid,
            $sPaymentId,
            $sDeliveryTypeId,
            'oxdelset'
        ));
    }

    protected function addDeliveryRuleToType(string $sOxid, string $sRuleId, string $sDeliveryTypeId): void
    {
        DatabaseProvider::getDb()->execute(sprintf(
            "INSERT INTO %s (OXID, OXDELID, OXDELSETID) VALUES ('%s', '%s', '%s')",
            self::$sDelivery2CostTable,
            $sOxid,
            $sRuleId,
            $sDeliveryTypeId
        ));
    }

    protected function createDeliveryCostRule(string $sId, array $aTitles, float $dPrice): void
    {
        $oDelivery = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);
        $oDelivery->setId($sId);
        $oDelivery->oxdelivery__oxactive = new Field(1);
        $oDelivery->oxdelivery__oxtitle = new Field($aTitles[0]);
        $oDelivery->oxdelivery__oxtitle_1 = new Field($aTitles[1]);
        $oDelivery->oxdelivery__oxaddsum = new Field($dPrice);
        $oDelivery->oxdelivery__oxparam = new Field(0);
        $oDelivery->oxdelivery__oxparamend = new Field(999999);
        $oDelivery->oxdelivery__oxfinalize = new Field(1);
        $oDelivery->save();
    }

    protected function createDeliveryType(string $sId, array $aTitles, int $iDeliveryPosition): void
    {
        $oDeliverySet = oxNew(\OxidEsales\Eshop\Application\Model\DeliverySet::class);
        $oDeliverySet->setId($sId);
        $oDeliverySet->oxdeliveryset__oxactive = new Field(1);
        $oDeliverySet->oxdeliveryset__oxpos = new Field($iDeliveryPosition);
        $oDeliverySet->oxdeliveryset__oxtitle = new Field($aTitles[0]);
        $oDeliverySet->oxdeliveryset__oxtitle_1 = new Field($aTitles[1]);
        $oDeliverySet->save();
    }

    protected function createPcpDemoArticles(): void
    {
        foreach (self::$aPcpDemoProducts as $aDemoProduct) {
            $oArticle = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
            $oArticle->oxarticles__oxactive = new Field(1);
            $oArticle->oxarticles__oxartnum = new Field($aDemoProduct['artnum']);
            $oArticle->oxarticles__oxtitle = new Field($aDemoProduct['title']);
            $oArticle->oxarticles__oxprice = new Field($aDemoProduct['price']);
            $oArticle->setArticleLongDesc($aDemoProduct['longdesc']);

            if (!empty($aDemoProduct['pic1'])) {
                $oArticle->oxarticles__oxpic1 = new Field($aDemoProduct['pic1']);
            }
            if (!empty($aDemoProduct['pic2'])) {
                $oArticle->oxarticles__oxpic2 = new Field($aDemoProduct['pic2']);
            }
            if (!empty($aDemoProduct['pic3'])) {
                $oArticle->oxarticles__oxpic3 = new Field($aDemoProduct['pic3']);
            }

            $oArticle->save();

            $oActions = oxNew(\OxidEsales\Eshop\Application\Model\Actions::class);
            $oActions->load('oxbargain');
            $oActions->addArticle($oArticle->getId());
            $oActions->save();
        }
    }

    protected function deleteAllExistingProductData(): void
    {
        $this->deleteAllObjects(\OxidEsales\Eshop\Application\Model\Article::class, 'oxarticles');
        $this->deleteAllObjects(\OxidEsales\Eshop\Application\Model\Manufacturer::class, 'oxmanufacturers');
        $this->deleteAllObjects(\OxidEsales\Eshop\Application\Model\Category::class, 'oxcategories');
        $this->wipeTable('oxcategories');
        $this->wipeTable('oxobject2category');
    }

    protected function wipeTable(string $sTableName): void
    {
        DatabaseProvider::getDb()->execute(sprintf('TRUNCATE TABLE %s;', $sTableName));
    }

    protected function deleteAllObjects(string $sClassName, string $sTable): void
    {
        $oDb = DatabaseProvider::getDb();
        $aIds = $oDb->getAll(sprintf("SELECT OXID FROM %s WHERE 1", $sTable));

        foreach ($aIds as $aId) {
            $oObject = oxNew($sClassName);
            $oObject->load($aId[0]);
            $oObject->delete();
        }
    }

    protected function copyArticleDemoPictures(): void
    {
        $sModulesDir = Registry::getConfig()->getModulesDir();
        $sSourcePath = $sModulesDir . 'Payone/PcpPrototype/out/pictures/products';
        $sTargetPath = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pictures/master/product';

        foreach (self::$aProductPictures as $sPic) {
            $aSplitPicName = explode('_', $sPic);
            $sPicLastPart = (string) $aSplitPicName[count($aSplitPicName) - 1];
            $aSplitPicLastPart = explode('.', $sPicLastPart);
            $sPicNr = (string) $aSplitPicLastPart[0];

            $sCopySource = sprintf('%s/%s', $sSourcePath, $sPic);
            $sCopyTarget = sprintf('%s/%s/%s', $sTargetPath, $sPicNr, $sPic);

            if (file_exists($sCopySource)) {
                if (!file_exists(dirname($sCopyTarget))) {
                    mkdir(dirname($sCopyTarget), 0777, true);
                }
                copy($sCopySource, $sCopyTarget);
            }
        }
    }

    protected function copyFavIcon(): void
    {
        $sModulesDir = Registry::getConfig()->getModulesDir();
        $sSrcFile = $sModulesDir . 'Payone/PcpPrototype/out/img/favicon.ico';
        $sTgtFile = Registry::getConfig()->getConfigParam('sShopDir') . 'out/flow/img/favicons/favicon.ico';

        if (file_exists($sSrcFile)) {
            if (!file_exists(dirname($sTgtFile))) {
                mkdir(dirname($sTgtFile), 0777, true);
            }
            copy($sSrcFile, $sTgtFile);
        }
    }
}