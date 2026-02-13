<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

class ApiLogListController extends AdminListController
{
    protected $_sListClass = 'Payone\PcpPrototype\Model\ApiLog';

    protected $_blDesc = true;

    protected $_sDefSortField = 'pcp_timestamp';

    protected $_sThisTemplate = 'pcp_apilog_list';

    public function getListSorting()
    {
        if ($this->_aCurrSorting === null) {
            $this->_aCurrSorting = $this->getConfig()->getRequestParameter('sort');

            if (!$this->_aCurrSorting && $this->_sDefSortField && ($oBaseObject = $this->getItemListBaseObject())) {
                $this->_aCurrSorting[$oBaseObject->getCoreTableName()] = [$this->_sDefSortField => 'desc'];
            }
        }

        return $this->_aCurrSorting;
    }

    public function pcpGetInputName(string $sTable, string $sField): string
    {
        return "where[{$sTable}][{$sField}]";
    }

    public function pcpGetWhereValue(string $sTable, string $sField): string
    {
        $aWhere = $this->getListFilter();
        return $aWhere[$sTable][$sField] ?? '';
    }
}