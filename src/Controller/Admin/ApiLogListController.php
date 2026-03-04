<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Model\ApiLog;

class ApiLogListController extends AdminListController
{
    protected $_sListClass = ApiLog::class;

    protected $_blDesc = true;

    protected $_sDefSortField = 'pcp_timestamp';

    protected $_sThisTemplate = '@PayonePcpPrototype/admin/pcp_apilog_list';

    public function getListSorting()
    {
        if ($this->_aCurrSorting === null) {
            $this->_aCurrSorting = Registry::getRequest()->getRequestParameter('sort');

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

    /**
     * Returns list filter array
     *
     * @return array
     */
    public function getListFilter(): array
    {
        if ($this->_aListFilter === null) {
            $this->_aListFilter = Registry::getRequest()->getRequestParameter("where") ?: [];
        }

        return $this->_aListFilter;
    }

}