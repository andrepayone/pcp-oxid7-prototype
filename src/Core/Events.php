<?php

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class Events
{
    /**
     * Actions to be performed when the module is activated.
     * Creates the payment method.
     */
    public static function onActivate()
    {
        self::addPaymentMethod();
        self::rebuildViews();
    }

    /**
     * Actions to be performed when the module is deactivated.
     * Deletes the payment method.
     */
    public static function onDeactivate()
    {
        self::removePaymentMethod();
        self::rebuildViews();
    }

    /**
     * Adds the PAYONE PCP payment method to the database.
     */
    private static function addPaymentMethod()
    {
        $payment = oxNew(Payment::class);
        if (!$payment->load('payone_checkout')) {
            $payment->setId('payone_checkout');
            $payment->setEnableMultilang(false);
            $payment->oxpayments__oxactive = new Field(1, Field::T_RAW);
            $payment->oxpayments__oxaddsum = new Field(0, Field::T_RAW);
            $payment->oxpayments__oxaddsumtype = new Field('abs', Field::T_RAW);
            $payment->oxpayments__oxfromamount = new Field(0, Field::T_RAW);
            $payment->oxpayments__oxtoamount = new Field(1000000, Field::T_RAW);
            $payment->oxpayments__oxdesc = new Field('PAYONE PCP Checkout', Field::T_RAW);

            $languages = Registry::getLang()->getLanguageIds();
            foreach ($languages as $langId) {
                $payment->setLanguage($langId);
                $payment->oxpayments__oxdesc = new Field('PAYONE PCP Checkout', Field::T_RAW);
                $payment->save();
            }
        }
    }

    /**
     * Removes the PAYONE PCP payment method from the database.
     */
    private static function removePaymentMethod()
    {
        $payment = oxNew(Payment::class);
        if ($payment->load('payone_checkout')) {
            $payment->delete();
        }
    }

    /**
     * Rebuilds the database view files.
     */
    protected static function rebuildViews()
    {
        if (class_exists(DbMetaDataHandler::class)) {
            $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
            $dbMetaDataHandler->updateViews();
        }
    }
}