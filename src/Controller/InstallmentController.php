<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use Payone\PcpPrototype\Core\PayoneApiService;

class InstallmentController extends FrontendController
{
    protected $_blIsOrderStep = true;
    protected $_sThisTemplate = 'pcpinstallment';
    protected ?string $pcpMerchantReference = null;

    public function render()
    {
        parent::render();

        $sMerchantReference = $this->getPcpMerchantReference();
        if ($sMerchantReference) {
            Registry::getSession()->setVariable('pcp_merchant_reference', $sMerchantReference);
        }

        return $this->_sThisTemplate;
    }

    public function pcpGetCardHolder(): string
    {
        $oUser = $this->getUser();
        return $oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value;
    }

    public function getPcpMerchantReference(): string
    {
        if ($this->pcpMerchantReference === null) {
            $aDynValue = Registry::getRequest()->getRequestParameter('dynvalue');

            if ($aDynValue && isset($aDynValue['pcp_merchant_reference'])) {
                $this->pcpMerchantReference = $aDynValue['pcp_merchant_reference'];
            } else {
                $oApiService = oxNew(PayoneApiService::class);
                $this->pcpMerchantReference = $oApiService->generateReference('dm');
            }
        }

        return $this->pcpMerchantReference;
    }

    public function pcpGetBNPLInstallmentOptions(): array|false
    {
        $oUser = $this->getUser();
        $oApiService = oxNew(PayoneApiService::class);

        $orderResponse = $oApiService->getInstallmentOptions($oUser);

        $installmentOptions = $this->extractInstallmentOptions($orderResponse);

        if ($installmentOptions === null) {
            return false;
        }

        return $this->prepareInstallmentOptions($installmentOptions);
    }

    protected function extractInstallmentOptions($orderResponse): ?array
    {
        $payment = $orderResponse->getCreatePaymentResponse();
        if ($payment === null) {
            return null;
        }

        $paymentOutput = $payment->getPaymentOutput();
        if ($paymentOutput === null) {
            return null;
        }

        $financingOutput = $paymentOutput->getFinancingPaymentMethodSpecificOutput();
        if ($financingOutput === null) {
            return null;
        }

        $product3391Output = $financingOutput->getPaymentProduct3391SpecificOutput();
        if ($product3391Output === null) {
            return null;
        }

        $installmentOptions = $product3391Output->getInstallmentOptions();
        if ($installmentOptions === null || count($installmentOptions) === 0) {
            return null;
        }

        return $installmentOptions;
    }

    protected function prepareInstallmentOptions(array $installmentOptions): array
    {
        $prepared = [];
        $count = count($installmentOptions);

        foreach ($installmentOptions as $index => $option) {
            $prepared[] = [
                'installmentOptionId' => $option->getInstallmentOptionId(),
                'numberOfPayments' => $option->getNumberOfPayments(),
                'monthlyAmount' => [
                    'amount' => $this->formatAmount($option->getMonthlyAmount()->getAmount()),
                    'currency' => $option->getMonthlyAmount()->getCurrencyCode(),
                ],
                'lastRateAmount' => [
                    'amount' => $this->formatAmount($option->getLastRateAmount()->getAmount()),
                    'currency' => $option->getLastRateAmount()->getCurrencyCode(),
                ],
                'totalAmount' => [
                    'amount' => $this->formatAmount($option->getTotalAmount()->getAmount()),
                    'currency' => $option->getTotalAmount()->getCurrencyCode(),
                ],
                'effectiveInterestRate' => $this->formatRate($option->getEffectiveInterestRate()),
                'nominalInterestRate' => $this->formatRate($option->getNominalInterestRate()),
                'firstRateDate' => $option->getFirstRateDate(),
                'creditInformation' => $option->getCreditInformation(),
                'isDefault' => $index === ($count - 1),
            ];
        }

        return $prepared;
    }

    protected function formatAmount(int $centAmount): string
    {
        return number_format($centAmount / 100, 2, ',', '.');
    }

    protected function formatRate(int $rate): string
    {
        return number_format($rate / 100, 2, ',', '.');
    }
}