<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;

class UserController extends UserController_parent
{
    public function getLoginOption(): int
    {
        if ($this->pcpUseFixedNoregestration()) {
            return 1;
        }

        return parent::getLoginOption();
    }

    public function pcpUseFixedNoregestration(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('pcpUseFixedNoregestration');
    }
}