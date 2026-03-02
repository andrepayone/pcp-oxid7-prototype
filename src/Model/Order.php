<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Counter;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Core\PayoneApiService;
use PayoneCommercePlatform\Sdk\Models\ActionType;
use PayoneCommercePlatform\Sdk\Models\CompletePaymentResponse;
use PayoneCommercePlatform\Sdk\Models\CreateCommerceCaseResponse;

class Order extends Order_parent
{
    protected bool $blIsReturnFromRedirect = false;

    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $this->pcpSetReturnFromRedirect();
        parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        return self::ORDER_STATE_OK;
    }

    protected function _setUser($oUser)
    {
        parent::_setUser($oUser);
        $this->pcpCheckStoreShipping();
    }

    public function isPcpPaymentType(?string $sPaymentType = null): bool
    {
        if (!$sPaymentType) {
            $sPaymentType = $this->oxorder__oxpaymenttype->value;
        }
        return Payment::isPcpPaymentType($sPaymentType);
    }

    public function pcpHandleAuthorization(bool $blReturnRedirectUrl = false, $paymentGateway = null): bool
    {
        $dynValue = Registry::getSession()->getVariable('dynvalue') ?: [];
        $user = $this->getOrderUser();
        Registry::getLogger()->error('Handle authorization with dynvalue: ' . print_r($dynValue, true));

        $apiService = oxNew(PayoneApiService::class);
        $response = $apiService->sendRequestAuthorization($this, $user, $dynValue);

        return $this->pcpHandleAuthorizationResponse($response, $paymentGateway, $dynValue);
    }

    public function pcpIsReturnFromRedirect(): bool
    {
        return $this->blIsReturnFromRedirect;
    }

    public function validateDelivery($oBasket)
    {
        return null;
    }

    public function validateOrder($oBasket, $oUser)
    {
        return null;
    }

    protected function pcpSetReturnFromRedirect(): void
    {
        $this->blIsReturnFromRedirect = false;
        $oRequest = Registry::getRequest();

        $sTxid = $oRequest->getRequestParameter('txid');
        $iSuccess = (int) $oRequest->getRequestParameter('pcpreturn');

        if ($iSuccess === 1 && $sTxid) {
            $oSession = Registry::getSession();
            $oSession->setVariable('pcpreturn', true);
            $oSession->setVariable('txid', $sTxid);
            $this->blIsReturnFromRedirect = true;
        }
    }

    protected function pcpCheckStoreShipping(): void
    {
        $oSession = Registry::getSession();
        $blIsStoreShipping = $oSession->getVariable('pcp_is_store_shipping');

        if (!$blIsStoreShipping) {
            return;
        }

        $oConfig = Registry::getConfig();
        $this->oxorder__oxdelcompany = new Field($oConfig->getConfigParam('pcpFixedShippingName'));
        $this->oxorder__oxdelfname = new Field('');
        $this->oxorder__oxdellname = new Field('');
        $this->oxorder__oxdelstreet = new Field($oConfig->getConfigParam('pcpFixedShippingStreet'));
        $this->oxorder__oxdelstreetnr = new Field($oConfig->getConfigParam('pcpFixedShippingStreetNr'));
        $this->oxorder__oxdeladdinfo = new Field('');
        $this->oxorder__oxdelcity = new Field($oConfig->getConfigParam('pcpFixedShippingCity'));
        $this->oxorder__oxdelcountryid = new Field('a7c40f631fc920687.20179984');
        $this->oxorder__oxdelstateid = new Field('');
        $this->oxorder__oxdelzip = new Field($oConfig->getConfigParam('pcpFixedShippingZip'));
        $this->oxorder__oxdelfon = new Field('');
        $this->oxorder__oxdelfax = new Field('');
        $this->oxorder__oxdelsal = new Field('');
    }

    protected function pcpHandleAuthorizationResponse(CreateCommerceCaseResponse | CompletePaymentResponse $response, $payGateway, $dynValue): bool
    {
        $aSessionData = $this->extractResponseDataForSession($response, $dynValue);

        Registry::getSession()->setVariable('pcpOrderResponse', $aSessionData);
        Registry::getSession()->setVariable('pcpOrderId', $this->getId());
        $this->pcpSaveResponseReferences($aSessionData);

        if ($response instanceof CompletePaymentResponse) {
            // Completed installment payment. No need for further processing
            return true;
        }

        $checkout = $response->getCheckout();

        if ($checkout !== null && $checkout->getErrorResponse() !== null) {
            return false;
        }

        if ($checkout !== null && $checkout->getPaymentResponse() !== null) {
            $paymentResponse = $checkout->getPaymentResponse();

            $merchantAction = $paymentResponse->getMerchantAction();
            if ($merchantAction !== null && $merchantAction->getActionType() === ActionType::REDIRECT) {
                $redirectUrl = $merchantAction->getRedirectData()->getRedirectURL();
                $this->pcpHandleAuthorizationRedirect($redirectUrl);
                return false;
            }

            $payment = $paymentResponse->getPayment();
            if ($payment !== null && $payment->getStatus() === 'PENDING_CAPTURE') {
                return true;
            }
        }

        if ($this->isSuccessfulOrderCompleteResponse($response)) {
            return true;
        }

        if ($this->isReservedForPaymentInStore($response)) {
            return true;
        }

        return false;
    }

    protected function isReservedForPaymentInStore($response): bool
    {
        $sPaymentId = $this->oxorder__oxpaymenttype->value;
        if ($sPaymentId !== 'pcppayinstore') {
            return false;
        }

        $checkout = $response->getCheckout();
        if ($checkout === null) {
            return false;
        }

        $statusOutput = $checkout->getStatusOutput();
        if ($statusOutput === null) {
            return false;
        }

        return $statusOutput->getPaymentStatus() === 'WAITING_FOR_PAYMENT';
    }

    protected function isSuccessfulOrderCompleteResponse($response): bool
    {
        $checkout = $response->getCheckout();
        if ($checkout === null || $checkout->getPaymentResponse() === null) {
            return false;
        }

        $payment = $checkout->getPaymentResponse()->getPayment();
        if ($payment === null) {
            return false;
        }

        return $payment->getStatus() === 'PENDING_CAPTURE';
    }

    protected function extractResponseDataForSession(CreateCommerceCaseResponse | CompletePaymentResponse $response, $dynValue): array
    {
        $data = [];

        if ($response instanceof CompletePaymentResponse) {
            Registry::getLogger()->info('Response of request is of type complete request');
            $data['commerceCaseId'] = Registry::getSession()->getVariable('bnplInstallmentCommerceCaseId');
            $checkoutId = Registry::getSession()->getVariable('bnplInstallmentCheckoutId');
            $data['merchantReference'] = $dynValue['pcp_merchant_reference'];
            $data['checkout'] = [
                'checkoutId' => $checkoutId,
            ];
            return $data;
        }

        $data['commerceCaseId'] = $response->getCommerceCaseId();
        $data['merchantReference'] = $response->getMerchantReference();

        $checkout = $response->getCheckout();
        if ($checkout !== null) {
            $data['checkout'] = [
                'checkoutId' => $checkout->getCheckoutId(),
            ];

            $references = $checkout->getReferences();
            if ($references !== null) {
                $data['checkout']['references'] = [
                    'merchantReference' => $references->getMerchantReference(),
                ];
            }
        }

        return $data;
    }

    protected function pcpSaveResponseReferences(array $aData): void
    {
        $oSession = Registry::getSession();
        $aParts = [];

        if (!empty($aData['commerceCaseId'])) {
            $aParts[] = 'commerceCaseId: ' . $aData['commerceCaseId'];
        }
        if (!empty($aData['merchantReference'])) {
            $aParts[] = 'merchantReference: ' . $aData['merchantReference'];
        }
        if (!empty($aData['checkout']['checkoutId'])) {
            $aParts[] = 'checkoutId: ' . $aData['checkout']['checkoutId'];
        }

        $sInstallmentCommerceCaseId = $oSession->getVariable('bnplInstallmentCommerceCaseId');
        if ($sInstallmentCommerceCaseId) {
            $aParts[] = 'commerceCaseId: ' . $sInstallmentCommerceCaseId;
        }
        $sMerchantReference = $oSession->getVariable('pcpMerchantReference');
        if ($sMerchantReference) {
            $aParts[] = 'merchantReference: ' . $sMerchantReference;
        }
        $sInstallmentCheckoutId = $oSession->getVariable('bnplInstallmentCheckoutId');
        if ($sInstallmentCheckoutId) {
            $aParts[] = 'checkoutId: ' . $sInstallmentCheckoutId;
        }

        $sRemark = implode(' | ', $aParts);

        if ($sRemark) {
            $this->oxorder__oxremark = new Field($sRemark, Field::T_RAW);
            $this->save();
        }
    }

    protected function pcpHandleAuthorizationRedirect(string $sRedirectUrl): void
    {
        if (!$this->oxorder__oxordernr->value) {
            $this->setNumber();
        } else {
            oxNew(Counter::class)->update(
                $this->getCounterIdent(),
                $this->oxorder__oxordernr->value
            );
        }
        $this->save();

        Registry::getUtils()->redirect($sRedirectUrl, false);
    }
}