<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\Registry;

class ViewConfig extends ViewConfig_parent
{
    protected static array $validColorGroups = [
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
        return (string) Registry::getConfig()->getConfigParam($sConfigParam);
    }

    public function pcpUseFixedNoregestration(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('pcpUseFixedNoregestration');
    }

    public function pcpUseFixedShipping(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('pcpUseFixedShipping');
    }

    public function pcpUseCustomColors(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('pcpUseCustomColors');
    }

    public function pcpUseCustomShopLogo(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('pcpUseCustomShopLogo');
    }

    public function pcpGetColor(string $sColorGroup): string
    {
        if (!in_array($sColorGroup, self::$validColorGroups, true)) {
            return '#000';
        }

        return (string) Registry::getConfig()->getConfigParam($sColorGroup);
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

        return $this->getModuleUrl('PayonePcpPrototype', 'out/img/payone_logo.png');
    }
}