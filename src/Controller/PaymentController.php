<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Core\PayoneApiService;
use Payone\PcpPrototype\Model\Payment;

class PaymentController extends PaymentController_parent
{
    protected ?string $pcpMerchantReference = null;
    protected ?string $pcpCheckoutReference = null;

    public function isPcpPayment(): bool
    {
        $sPaymentId = $this->getCheckedPaymentId();
        return Payment::isPcpPaymentType($sPaymentId);
    }

    public function validatePayment()
    {
        $result = parent::validatePayment();
        $sPaymentId = $this->getCheckedPaymentId();

        if ($result === 'order' && Payment::isPcpInstallment($sPaymentId)) {
            return 'pcpinstallmentcontroller';
        }

        return $result;
    }

    public function pcpGetCardHolder(): string
    {
        $oUser = $this->getUser();
        return $oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value;
    }

    public function pcpGetMerchantReference(string $sPrefix = 'dm'): string
    {
        if ($this->pcpMerchantReference === null || $sPrefix !== 'dm') {
            $oApiService = oxNew(PayoneApiService::class);
            $sRef = $oApiService->generateReference($sPrefix);

            if ($sPrefix === 'dm') {
                $this->pcpMerchantReference = $sRef;
            } else {
                return $sRef;
            }
        }

        return $this->pcpMerchantReference;
    }

    public function pcpGetCheckoutReference(string $sPrefix = 'ck'): string
    {
        if ($this->pcpCheckoutReference === null || $sPrefix !== 'ck') {
            $oApiService = oxNew(PayoneApiService::class);
            $sRef = $oApiService->generateReference($sPrefix);

            if ($sPrefix === 'ck') {
                $this->pcpCheckoutReference = $sRef;
            } else {
                return $sRef;
            }
        }

        return $this->pcpCheckoutReference;
    }
}