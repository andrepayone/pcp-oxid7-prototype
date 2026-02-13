<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Model\ApiLog;

class ApiLogController extends AdminDetailsController
{
    protected $_sThisTemplate = 'pcp_apilog';

    public function render()
    {
        parent::render();

        $sOxid = Registry::getRequest()->getRequestParameter('oxid');
        if ($sOxid != '-1' && isset($sOxid)) {
            $oLogEntry = oxNew(ApiLog::class);
            $oLogEntry->load($sOxid);
            $this->_aViewData['edit'] = $oLogEntry;
        }

        return $this->_sThisTemplate;
    }
}