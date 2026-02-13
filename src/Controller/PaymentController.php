<?php

namespace Payone\PcpPrototype\Controller;

use Payone\PcpPrototype\Core\PayoneApiService;
use OxidEsales\Eshop\Application\Controller\PaymentController as OxidPaymentController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;

class PaymentController extends PaymentController_parent
{
    /**
     * Validates the selected payment method. If the PAYONE PCP payment method is chosen,
     * it uses the PayoneApiService to create a checkout and redirects the user.
     *
     * @return mixed
     */
    public function validatePayment()
    {
        $paymentId = Registry::get(Request::class)->getRequestEscapedParameter('paymentid');

        if ($paymentId !== 'payone_checkout') {
            return parent::validatePayment();
        }

        $session = $this->getSession();
        $basket = $session->getBasket();

        $apiService = new PayoneApiService();
        $merchantReference = 'oxid-' . uniqid();

        $response = $apiService->createCheckout(
            $basket->getPayoneBasketAmount(),
            $basket->getBasketCurrency()->name,
            $merchantReference
        );

        if ($response && isset($response['checkoutId'], $response['redirectUrl'])) {
            $session->setVariable('payoneCheckoutId', $response['checkoutId']);
            $session->setVariable('payoneMerchantReference', $merchantReference);
            Utils::redirect($response['redirectUrl'], false);
        } else {
            Registry::get(Utils::class)->addErrorToDisplay('PAYONE_PCP_CHECKOUT_FAILED');
            return 'payment';
        }
    }
}