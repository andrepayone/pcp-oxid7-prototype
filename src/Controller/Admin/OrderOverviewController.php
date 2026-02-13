<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Field;
use Payone\PcpPrototype\Core\PayoneApiService;
use PayoneCommercePlatform\Sdk\Models\CancellationReason;
use PayoneCommercePlatform\Sdk\Models\StatusCheckout;

class OrderOverviewController extends OrderOverviewController_parent
{
    protected ?string $sPaymentStatus = null;
    protected ?string $sCheckoutStatus = null;
    protected ?array $aGeneralStatus = null;
    protected ?array $aTranslatedStatusMessage = null;

    public function __construct()
    {
        parent::__construct();

        $oLang = Registry::getLang();
        $this->aTranslatedStatusMessage = [
            'payment' => [
                'PAYMENT_NOT_COMPLETED' => $oLang->translateString('PCP_STATUS_PAYMENT_NOT_COMPLETED'),
                'WAITING_FOR_PAYMENT' => $oLang->translateString('PCP_STATUS_PAYMENT_NOT_COMPLETED'),
                'PAYMENT_COMPLETED' => $oLang->translateString('PCP_STATUS_PAYMENT_COMPLETED'),
            ],
            'checkout' => [
                'COMPLETED' => $oLang->translateString('PCP_STATUS_COMPLETED'),
                'BILLED' => $oLang->translateString('PCP_STATUS_BILLED'),
                'OPEN' => $oLang->translateString('PCP_STATUS_OPEN'),
            ],
        ];
    }

    public function pcpcapture()
    {
        $aOrderState = $this->pcpGetOrderState();
        if (!$aOrderState) {
            return;
        }

        $oApiService = oxNew(PayoneApiService::class);
        $oApiService->captureOrder(
            $aOrderState['commerceCaseId'],
            $aOrderState['checkoutId']
        );

        $this->displayMessage('PCP_PAYMENT_SEND_CAPTURE');
    }

    public function pcprefund()
    {
        $aOrderState = $this->pcpGetOrderState();
        if (!$aOrderState) {
            return;
        }

        $oApiService = oxNew(PayoneApiService::class);
        $oApiService->refundOrder(
            $aOrderState['commerceCaseId'],
            $aOrderState['checkoutId']
        );

        $this->displayMessage('PCP_PAYMENT_SEND_REFUND');
    }

    public function pcpcancel()
    {
        $aOrderState = $this->pcpGetOrderState();
        if (!$aOrderState) {
            return;
        }

        $sPaymentExecutionId = $this->extractPaymentExecutionId($aOrderState['paymentExecutions']);
        if (!$sPaymentExecutionId) {
            return;
        }

        $sCancellationReason = Registry::getRequest()->getRequestParameter('pcpcancellationreason');

        $oApiService = oxNew(PayoneApiService::class);
        $oApiService->cancelOrder(
            $aOrderState['commerceCaseId'],
            $aOrderState['checkoutId'],
            $sPaymentExecutionId,
            CancellationReason::from($sCancellationReason ?: 'CONSUMER_REQUEST'),
        );
    }

    public function pcpCaptureAllowed(): bool
    {
        return in_array($this->sPaymentStatus, [
                'WAITING_FOR_PAYMENT',
                'PAYMENT_NOT_COMPLETED',
            ]) && $this->sCheckoutStatus === StatusCheckout::COMPLETED;
    }

    public function pcpRefundAllowed(): bool
    {
        $blNotRefunded = isset($this->aGeneralStatus['refundedAmount'])
            && $this->aGeneralStatus['refundedAmount'] === 0;

        return $blNotRefunded
            && $this->sPaymentStatus === 'PAYMENT_COMPLETED'
            && $this->sCheckoutStatus === StatusCheckout::BILLED;
    }

    public function pcpGetOrderState(): array|false
    {
        $sOxid = $this->getEditObjectId();
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if (!$oOrder->load($sOxid)) {
            return false;
        }

        $sRemark = $oOrder->oxorder__oxremark->value;
        if (!$sRemark) {
            return false;
        }

        $aPcpData = $this->fetchPcpData($sRemark);
        if (!$aPcpData['commerceCaseId'] || !$aPcpData['checkoutId']) {
            return false;
        }

        $oApiService = oxNew(PayoneApiService::class);
        $checkoutResponse = $oApiService->getCheckoutDetails(
            $aPcpData['commerceCaseId'],
            $aPcpData['checkoutId']
        );

        $statusOutput = $checkoutResponse->getStatusOutput();
        $this->sPaymentStatus = $statusOutput?->getPaymentStatus();
        $this->sCheckoutStatus = $checkoutResponse->getCheckoutStatus();
        $this->aGeneralStatus = $this->extractGeneralStatus($statusOutput);

        $sPaymentStatusMessage = $this->getPaymentStatusMessage($this->sPaymentStatus);
        $sCheckoutStatusMessage = $this->getCheckoutStatusMessage($this->sCheckoutStatus);

        $paymentExecutions = $checkoutResponse->getPaymentExecutions();
        $shoppingCart = $checkoutResponse->getShoppingCart();

        return [
            'commerceCaseId' => $aPcpData['commerceCaseId'],
            'checkoutId' => $aPcpData['checkoutId'],
            'paymentStatus' => $this->sPaymentStatus,
            'paymentStatusMessage' => $sPaymentStatusMessage,
            'checkoutStatus' => $this->sCheckoutStatus,
            'checkoutStatusMessage' => $sCheckoutStatusMessage,
            'statusOutput' => $this->aGeneralStatus,
            'availableActions' => $checkoutResponse->getAllowedPaymentActions(),
            'paymentExecutions' => $paymentExecutions,
            'items' => $shoppingCart !== null ? $shoppingCart->getItems() : [],
        ];
    }

    protected function displayMessage(string $sMultilangIdent): void
    {
        $sMessage = Registry::getLang()->translateString($sMultilangIdent);
        echo '<div style="color:#0096d6; font-weight: bold;">';
        echo $sMessage;
        echo '</div>';
    }

    protected function extractPaymentExecutionId($paymentExecutions): ?string
    {
        if ($paymentExecutions === null || count($paymentExecutions) === 0) {
            return null;
        }

        $firstExecution = $paymentExecutions[0];
        return $firstExecution->getPaymentExecutionId();
    }

    protected function extractGeneralStatus($statusOutput): array
    {
        if ($statusOutput === null) {
            return ['refundedAmount' => 0];
        }

        return [
            'paymentStatus' => $statusOutput->getPaymentStatus(),
            'refundedAmount' => method_exists($statusOutput, 'getRefundedAmount')
                ? $statusOutput->getRefundedAmount()
                : 0,
        ];
    }

    protected function getPaymentStatusMessage(?string $sPaymentStatus): string
    {
        $sMessage = $this->aTranslatedStatusMessage['payment'][$sPaymentStatus] ?? $sPaymentStatus ?? '';

        if (isset($this->aGeneralStatus['refundedAmount']) && $this->aGeneralStatus['refundedAmount'] > 0) {
            $sMessage .= sprintf(' (%s)', Registry::getLang()->translateString('PCP_PAYMENT_REFUNDED'));
        }

        return $sMessage;
    }

    protected function getCheckoutStatusMessage(?StatusCheckout $sCheckoutStatus): string
    {
        if ($sCheckoutStatus === null) {
            return '';
        }
        return $this->aTranslatedStatusMessage['checkout'][$sCheckoutStatus->value] ?? $sCheckoutStatus->value;
    }

    protected function fetchPcpData(string $inputString): array
    {
        $result = [
            'commerceCaseId' => null,
            'checkoutId' => null,
        ];

        if (preg_match('/commerceCaseId:\s*([a-f0-9-]+)/', $inputString, $matches)) {
            $result['commerceCaseId'] = $matches[1];
        }

        if (preg_match('/checkoutId:\s*([a-f0-9-]+)/', $inputString, $matches)) {
            $result['checkoutId'] = $matches[1];
        }

        return $result;
    }
}