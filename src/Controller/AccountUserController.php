<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;

class AccountUserController extends AccountUserController_parent
{
    public function showShipAddress(): bool
    {
        if ((bool) Registry::getConfig()->getConfigParam('pcpUseFixedShipping')) {
            Registry::getSession()->setVariable('blshowshipaddress', '0');
            return false;
        }

        return parent::showShipAddress();
    }
}