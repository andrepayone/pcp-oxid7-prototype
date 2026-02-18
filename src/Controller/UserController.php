<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;

class UserController extends UserController_parent
{
    public function getLoginOption(): int
    {
        $logger = Registry::getLogger();
        if ($this->pcpUseFixedNoregestration()) {
            $logger->error('pcpUseFixedNoregestration is active, return fixed value for getLoginOption()');
            return 1;
        }

        $logger->error('pcpUseFixedNoregestration is not active, return parent value for getLoginOption()');
        return (int) parent::getLoginOption();
    }

    public function pcpUseFixedNoregestration(): bool
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $logger = Registry::getLogger();
        $logger->error('Check if pcpUseFixedNoregestration is active');
        $moduleConfigService = $container->get(ModuleConfigurationDaoBridgeInterface::class);
        $moduleConfig = $moduleConfigService->get('PayonePcpPrototype');
        if (!$moduleConfig->hasModuleSetting('pcpUseFixedNoregestration')) {
            $logger->error('Module setting pcpUseFixedNoregestration not found, return false');
            return false;
        }
        return (bool) $moduleConfig->getModuleSetting('pcpUseFixedNoregestration')->getValue();
    }
}