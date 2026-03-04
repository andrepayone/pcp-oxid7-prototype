<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Model\ApiLog;

class ApiLogController extends AdminDetailsController
{
    protected $_sThisTemplate = '@PayonePcpPrototype/admin/pcp_apilog';

    public function render()
    {
        parent::render();

        $oxId = Registry::getRequest()->getRequestParameter('oxid');
        if ($oxId != '-1' && isset($oxId)) {
            $logEntry = oxNew(ApiLog::class);
            $logEntry->load($oxId);
            $this->_aViewData['edit'] = $logEntry;
        }

        return $this->_sThisTemplate;
    }
}