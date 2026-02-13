<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Core\PayoneApiService;
use Payone\PcpPrototype\Model\Payment;

class OrderController extends OrderController_parent
{
    protected ?string $pcpMerchantReference = null;
    protected ?string $pcpCheckoutReference = null;

    public function render()
    {
        $render = parent::render();

        $oPayment = $this->getPayment();
        if ($oPayment && Payment::isPcpInstallment($oPayment->getId())) {
            $aDynValue = Registry::getRequest()->getRequestParameter('dynvalue');
            if ($aDynValue && isset($aDynValue['pcp_secinstallment_plan'])) {
                Registry::getSession()->setVariable(
                    'pcp_secinstallment_plan',
                    $aDynValue['pcp_secinstallment_plan']
                );
            }
        }

        return $render;
    }

    public function isPcpPayment(): bool
    {
        $oPayment = $this->getPayment();
        if (!$oPayment) {
            return false;
        }
        return Payment::isPcpPaymentType($oPayment->getId());
    }

    public function getPcpMerchantReference(): string
    {
        if ($this->pcpMerchantReference === null) {
            $sSessionRef = Registry::getSession()->getVariable('pcp_merchant_reference');
            if ($sSessionRef) {
                $this->pcpMerchantReference = $sSessionRef;
            } else {
                $oApiService = oxNew(PayoneApiService::class);
                $this->pcpMerchantReference = $oApiService->generateReference('dm');
            }
        }

        return $this->pcpMerchantReference;
    }

    public function getPcpCheckoutReference(): string
    {
        if ($this->pcpCheckoutReference === null) {
            $sSessionRef = Registry::getSession()->getVariable('pcp_checkout_reference');
            if ($sSessionRef) {
                $this->pcpCheckoutReference = $sSessionRef;
            } else {
                $oApiService = oxNew(PayoneApiService::class);
                $this->pcpCheckoutReference = $oApiService->generateReference('ck');
            }
        }

        return $this->pcpCheckoutReference;
    }

    public function pcpIsStoreShipping(): bool
    {
        $oShipSet = $this->getShipSet();
        if (!$oShipSet) {
            return false;
        }

        $sShippingId = $oShipSet->getId();

        return $sShippingId === 'pcpdeliverystore'
            || (bool) Registry::getConfig()->getConfigParam('pcpUseFixedShipping');
    }

    public function execute()
    {
        if (!$this->getSession()->checkSessionChallenge()) {
            return;
        }

        if (!$this->_validateTermsAndConditions()) {
            $this->_blConfirmAGBError = 1;
            return;
        }

        $oUser = $this->getUser();
        if (!$oUser) {
            return 'user';
        }

        if ($this->isPcpPayment()) {
            $oSession = Registry::getSession();

            $sMerchantReference = (string) Registry::getRequest()->getRequestParameter('pcp_merchant_reference');
            $sCheckoutReference = (string) Registry::getRequest()->getRequestParameter('pcp_checkout_reference');

            if ($sMerchantReference) {
                $oSession->setVariable('pcp_merchant_reference', $sMerchantReference);
            }
            if ($sCheckoutReference) {
                $oSession->setVariable('pcp_checkout_reference', $sCheckoutReference);
            }

            $oSession->setVariable('pcp_is_store_shipping', $this->pcpIsStoreShipping());
        }

        return parent::execute();
    }
}