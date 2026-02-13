<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

class ApiLog extends BaseModel
{
    protected $_sCoreTbl = 'pcpapilog';
    protected $_sClassName = 'ApiLog';

    public function __construct()
    {
        parent::__construct();
        $this->init('pcpapilog');
    }

    public function getRequestArray(): array|false
    {
        return $this->decodeStoredData($this->pcpapilog__pcp_request->rawValue);
    }

    public function getResponseArray(): array|false
    {
        return $this->decodeStoredData($this->pcpapilog__pcp_response->rawValue);
    }

    protected function decodeStoredData(?string $sData): array|false
    {
        if (!$sData) {
            return false;
        }

        $aDecoded = json_decode($sData, true);
        if (is_array($aDecoded)) {
            return $aDecoded;
        }

        $aDecoded = unserialize($sData, ['allowed_classes' => false]);
        return is_array($aDecoded) ? $aDecoded : false;
    }
}