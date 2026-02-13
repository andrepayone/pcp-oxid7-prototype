<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;

class ConfigUploadController extends AdminDetailsController
{
    protected $_sThisTemplate = 'pcpconfig_upload';

    public function render()
    {
        parent::render();

        $sModuleId = $this->getSelectedModuleId();
        $this->_aViewData['isPcpDemoModule'] = ($sModuleId === 'PayonePcpPrototype');

        return $this->_sThisTemplate;
    }

    public function pcpUploadShopLogo(): void
    {
        $aFiles = $_FILES;
        if (!isset($aFiles['pcpShopLogo']['name'])) {
            return;
        }

        if (!$this->checkUploadedLogoFileIsImage()) {
            $this->_aViewData['pcpResultMessage'] = 'Uploaded file is not an image';
            $this->render();
            return;
        }

        $sTargetPath = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pcp';
        if (!file_exists($sTargetPath)) {
            mkdir($sTargetPath, 0777, true);
        }

        $sUploadedImageFileName = basename($aFiles['pcpShopLogo']['name']);
        $sTmpFile = $aFiles['pcpShopLogo']['tmp_name'];
        $sTmpTarget = sprintf('%s/%s', $sTargetPath, $sUploadedImageFileName);
        $sImageFileType = strtolower(pathinfo($sTmpTarget, PATHINFO_EXTENSION));

        $aAllowedExtensions = ['png', 'jpg', 'jpeg'];
        if (!in_array($sImageFileType, $aAllowedExtensions, true)) {
            $this->_aViewData['pcpResultMessage'] = sprintf(
                'Filetype %s not allowed. Must be %s',
                $sImageFileType,
                implode(' or ', $aAllowedExtensions)
            );
            $this->render();
            return;
        }

        $sCopyTarget = sprintf('%s/%s', $sTargetPath, 'shop_logo.' . $sImageFileType);
        if (move_uploaded_file($sTmpFile, $sCopyTarget)) {
            $this->_aViewData['pcpResultMessage'] = sprintf(
                'The file %s has been uploaded and moved to %s',
                $sUploadedImageFileName,
                $sCopyTarget
            );
        } else {
            $this->_aViewData['pcpResultMessage'] = 'Sorry, there was an error uploading your file.';
        }

        $this->render();
    }

    public function pcpGetCustomShopLogo(): string|false
    {
        $sTargetPath = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pcp';
        $aFiletypes = ['png', 'jpg', 'jpeg'];

        foreach ($aFiletypes as $sFiletype) {
            $sTargetFile = sprintf('%s/%s', $sTargetPath, 'shop_logo.' . $sFiletype);
            if (file_exists($sTargetFile)) {
                return sprintf('out/pcp/%s', 'shop_logo.' . $sFiletype);
            }
        }

        return false;
    }

    protected function getSelectedModuleId(): string
    {
        $moduleId = $this->_sEditObjectId
            ?? Registry::getRequest()->getRequestParameter('oxid')
            ?? Registry::getSession()->getVariable('saved_oxid');

        if ($moduleId === null) {
            throw new \InvalidArgumentException('Module id not found.');
        }

        return $moduleId;
    }

    protected function checkUploadedLogoFileIsImage(): bool
    {
        $check = getimagesize($_FILES['pcpShopLogo']['tmp_name']);
        return $check !== false;
    }
}