<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;


class ViewConfig extends ViewConfig_parent
{
    protected static array $validColorGroups = [
        'pcpBackgroundColor',
        'pcpPrimaryColor',
        'pcpSecondaryColorBright',
        'pcpSecondaryColorDark',
        'pcpTertiaryColor1',
        'pcpDangerColor',
        'pcpWarningColor',
        'pcpBlackColor',
    ];

    public function pcpGetStringConfigParam(string $sConfigParam): string
    {
        return (string) $this->pcpGetShopConfVar($sConfigParam);
    }

    public function pcpUseFixedNoregestration(): bool
    {
        return (bool) $this->pcpGetShopConfVar('pcpUseFixedNoregestration');
    }

    public function pcpUseFixedShipping(): bool
    {
        return (bool) $this->pcpGetShopConfVar('pcpUseFixedShipping');
    }

    public function pcpUseCustomColors(): bool
    {
        return (bool) $this->pcpGetShopConfVar('pcpUseCustomColors');
    }

    public function pcpUseCustomShopLogo(): bool
    {
        return (bool) $this->pcpGetShopConfVar('pcpUseCustomShopLogo');
    }

    public function pcpGetColor(string $sColorGroup): string
    {
        if (!in_array($sColorGroup, self::$validColorGroups, true)) {
            return '#000';
        }

        return (string) $this->pcpGetShopConfVar($sColorGroup);
    }

    public function pcpGetShopLogo(): string
    {
        if ($this->pcpUseCustomShopLogo()) {
            $sTargetPath = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pcp';
            $aFiletypes = ['png', 'jpg', 'jpeg'];

            foreach ($aFiletypes as $sFiletype) {
                $sTargetFile = sprintf('%s/%s', $sTargetPath, 'shop_logo.' . $sFiletype);
                if (file_exists($sTargetFile)) {
                    return sprintf('out/pcp/%s', 'shop_logo.' . $sFiletype);
                }
            }
        }

        return $this->getModuleUrl('PayonePcpPrototype', 'img/payone_logo.png');
    }

    /**
     * Returns config value
     *
     * @param  string $sVarName
     * @return mixed|false
     */
    public function pcpGetShopConfVar($sVarName)
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $moduleConfiguration =
            $container->get(ModuleConfigurationDaoBridgeInterface::class)->get("PayonePcpPrototype");
        if (!$moduleConfiguration->hasModuleSetting($sVarName)) {
            return false;
        }
        return $moduleConfiguration->getModuleSetting($sVarName)->getValue();
    }

}