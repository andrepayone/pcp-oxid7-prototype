<?php

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Module\Module;

class Events
{
    /**
     * Actions to be performed when the module is activated.
     */
    public static function onActivate()
    {
        self::rebuildViews();
    }

    /**
     * Actions to be performed when the module is deactivated.
     */
    public static function onDeactivate()
    {
        self::rebuildViews();
    }

    /**
     * Rebuilds the database view files.
     */
    protected static function rebuildViews()
    {
        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $dbMetaDataHandler->updateViews();
    }
}