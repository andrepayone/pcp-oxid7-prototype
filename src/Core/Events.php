<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class Events
{
    public static string $sQueryTablePcpApiLog = "
        CREATE TABLE IF NOT EXISTS pcpapilog (
          OXID char(32) NOT NULL,
          PCP_TIMESTAMP DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PCP_REFNR varchar(32) NOT NULL DEFAULT '0',
          PCP_REQUESTTYPE varchar(32) NOT NULL DEFAULT '',
          PCP_REQUESTURL varchar(255) NOT NULL DEFAULT '',
          PCP_REQUEST text NOT NULL,
          PCP_RESPONSE text NOT NULL,
          PCP_RESPONSE_HTTPCODE varchar(3) NOT NULL DEFAULT '',
          PRIMARY KEY (OXID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    public static array $aPaymentMethods = [
        'pcppayinstore' => [
            'name' => 'PAYONE Reserve Online - Pay in Store (Demo)',
            'checked' => 1,
            'sorting' => 1,
        ],
        'pcpcreditcard' => [
            'name' => 'PAYONE Credit Card Payment (Demo)',
            'checked' => 0,
            'sorting' => 2,
        ],
        'pcppaypal' => [
            'name' => 'PAYONE PayPal Payment (Demo)',
            'checked' => 0,
            'sorting' => 3,
        ],
        'pcpsecuredebit' => [
            'name' => 'PAYONE Secured Direct Debit (Demo)',
            'checked' => 0,
            'sorting' => 4,
        ],
        'pcpsecureinstallment' => [
            'name' => 'PAYONE Secured Installment (Demo)',
            'checked' => 0,
            'sorting' => 5,
        ],
    ];

    public static array $aOtherPaymentsToDeactivate = [
        'oxidcashondel',
        'oxiddebitnote',
        'oxidinvoice',
        'oxidpayadvance',
    ];

    public static function onActivate(): void
    {
        self::addTableIfNotExists('pcpapilog', self::$sQueryTablePcpApiLog);
        self::addPayonePcpPayments();
        self::preparePromotions();
        self::deactivateOtherPaymentMethods();
        self::activatePcpPayments();
        self::regenerateViews();
        self::clearTmp();
    }

    public static function onDeactivate(): void
    {
        self::deactivatePcpPaymentMethods();
        self::clearTmp();
    }

    public static function regenerateViews(): void
    {
        $oShop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
        $oShop->generateViews();
    }

    public static function clearTmp(): void
    {
        $output = shell_exec(VENDOR_PATH . '/bin/oe-console oe:cache:clear');
    }

    public static function preparePromotions(): void
    {
        $oDb = DatabaseProvider::getDb();

        $aBannerPictures = [
            'payone_banner_1.png',
        ];

        self::copyBanners($aBannerPictures);

        $oDb->execute("UPDATE oxactions SET oxactive=0 WHERE oxtype=3;");

        foreach ($aBannerPictures as $iBannerIndex => $sBannerPicture) {
            $iBannerNr = $iBannerIndex + 1;
            $sActionOxid = $oDb->getOne(sprintf(
                "SELECT OXID FROM oxactions WHERE oxtype=3 AND OXTITLE='Banner %s' LIMIT 1;",
                $iBannerNr
            ));

            if ($sActionOxid) {
                $oDb->execute(sprintf(
                    "UPDATE oxactions SET OXACTIVE=1, OXPIC='%s', OXLINK='' WHERE OXID='%s' LIMIT 1;",
                    $sBannerPicture,
                    $sActionOxid
                ));
                $oDb->execute(sprintf(
                    "DELETE FROM oxobject2action WHERE OXACTIONID='%s' LIMIT 1;",
                    $sActionOxid
                ));
            }
        }
    }

    public static function copyBanners(array $aFilesToCopy): void
    {
        $sModuleDir = Registry::get(ViewConfig::class)->getModulePath('PayonePcpPrototype');
        $sPromoPathPlugin = $sModuleDir . 'out/pictures/promo';
        $sPromoPathShop = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pictures/promo';

        foreach ($aFilesToCopy as $sFileName) {
            $sPathSrc = sprintf('%s/%s', $sPromoPathPlugin, $sFileName);
            $sPathDst = sprintf('%s/%s', $sPromoPathShop, $sFileName);
            if (!file_exists(dirname($sPathDst))) {
                mkdir(dirname($sPathDst), 0777, true);
            }
            if (file_exists($sPathSrc)) {
                copy($sPathSrc, $sPathDst);
            }
        }
    }

    public static function addPayonePcpPayments(): void
    {
        $oDb = DatabaseProvider::getDb();
        $sShopId = Registry::getConfig()->getShopId();

        foreach (self::$aPaymentMethods as $sPaymentOxid => $aPayment) {
            $sPaymentName = $aPayment['name'];
            $iChecked = $aPayment['checked'];
            $iSort = $aPayment['sorting'];

            $blMethodCreated = self::insertRowIfNotExists(
                'oxpayments',
                ['OXID' => $sPaymentOxid],
                "INSERT INTO oxpayments(
                    OXID, OXACTIVE, OXDESC, OXADDSUM, OXADDSUMTYPE,
                    OXFROMAMOUNT, OXTOAMOUNT, OXVALDESC, OXCHECKED,
                    OXDESC_1, OXVALDESC_1, OXDESC_2, OXVALDESC_2,
                    OXDESC_3, OXVALDESC_3, OXLONGDESC, OXLONGDESC_1,
                    OXLONGDESC_2, OXLONGDESC_3, OXSORT
                ) VALUES (
                    '{$sPaymentOxid}', 1, '{$sPaymentName}', 0, 'abs',
                    0, 1000000, '', {$iChecked},
                    '{$sPaymentName}', '', '', '',
                    '', '', '', '',
                    '', '', {$iSort}
                );"
            );

            if ($blMethodCreated) {
                $aGroups = [
                    'oxidadmin', 'oxidcustomer', 'oxiddealer',
                    'oxidforeigncustomer', 'oxidgoodcust', 'oxidmiddlecust',
                    'oxidnewcustomer', 'oxidnewsletter', 'oxidnotyetordered',
                    'oxidpowershopper', 'oxidpricea', 'oxidpriceb',
                    'oxidpricec', 'oxidsmallcust',
                ];

                foreach ($aGroups as $sGroupId) {
                    $oDb->execute(
                        "INSERT INTO oxobject2group(OXID,OXSHOPID,OXOBJECTID,OXGROUPSID) 
                         VALUES (REPLACE(UUID(),'-',''), '{$sShopId}', '{$sPaymentOxid}', '{$sGroupId}');"
                    );
                }
            }

            self::insertRowIfNotExists(
                'oxobject2payment',
                ['OXPAYMENTID' => $sPaymentOxid, 'OXTYPE' => 'oxdelset'],
                "INSERT INTO oxobject2payment(OXID,OXPAYMENTID,OXOBJECTID,OXTYPE) 
                 VALUES (REPLACE(UUID(),'-',''), '{$sPaymentOxid}', 'oxidstandard', 'oxdelset');"
            );
        }
    }

    public static function deactivatePcpPaymentMethods(): void
    {
        $sPaymentIds = "'" . implode("','", array_keys(self::$aPaymentMethods)) . "'";
        DatabaseProvider::getDb()->execute(
            "UPDATE oxpayments SET oxactive = 0 WHERE oxid IN ({$sPaymentIds})"
        );
    }

    public static function deactivateOtherPaymentMethods(): void
    {
        $sPaymentIds = "'" . implode("','", self::$aOtherPaymentsToDeactivate) . "'";
        DatabaseProvider::getDb()->execute(
            "UPDATE oxpayments SET oxactive = 0 WHERE oxid IN ({$sPaymentIds})"
        );
    }

    public static function activatePcpPayments(): void
    {
        $sPaymentIds = "'" . implode("','", array_keys(self::$aPaymentMethods)) . "'";
        DatabaseProvider::getDb()->execute(
            "UPDATE oxpayments SET oxactive = 1 WHERE oxid IN ({$sPaymentIds})"
        );
    }

    public static function insertRowIfNotExists(string $sTableName, array $aKeyValue, string $sQuery): bool
    {
        $sWhere = '';
        foreach ($aKeyValue as $key => $value) {
            $sWhere .= " AND {$key} = '{$value}'";
        }

        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1" . $sWhere;
        $sExisting = DatabaseProvider::getDb()->getOne($sCheckQuery);

        if (!$sExisting) {
            DatabaseProvider::getDb()->execute($sQuery);
            return true;
        }
        return false;
    }

    public static function addTableIfNotExists(string $sTableName, string $sQuery): bool
    {
        $aTables = DatabaseProvider::getDb()->getAll("SHOW TABLES LIKE '{$sTableName}'");
        if (!$aTables || count($aTables) === 0) {
            DatabaseProvider::getDb()->execute($sQuery);
            return true;
        }
        return false;
    }
}