<?php

namespace Payone\PcpPrototype\Controller;

use Payone\PcpPrototype\Core\PcpRequest;
use OxidEsales\Eshop\Application\Controller\PaymentController as OxidPaymentController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;

class PaymentController extends PaymentController_parent
{
    /**
     * Validates the selected payment method. If the PAYONE PCP payment method is chosen,
     * it initiates the checkout creation process and redirects the user.
     * Otherwise, it calls the parent method.
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
        $config = Registry::getConfig();

        $requestData = [
            'merchantReference' => 'oxid-' . uniqid(),
            'amount' => $basket->getPayoneBasketAmount(),
            'currency' => $basket->getBasketCurrency()->name,
            'returnUrl' => $config->getShopUrl() . 'index.php?cl=payone_redirect',
            'notificationUrl' => $config->getShopUrl() . 'index.php?cl=payone_notification', // Note: Notification controller does not exist yet
        ];

        $pcpRequest = new PcpRequest();
        $responseJson = $pcpRequest->createCheckout($requestData, $config->getShopConfVar(null, null, 'module:pcpprototype'));

        $response = json_decode($responseJson, true);

        if (isset($response['checkoutId']) && isset($response['redirectUrl'])) {
            $session->setVariable('payoneCheckoutId', $response['checkoutId']);
            $session->setVariable('payoneMerchantReference', $requestData['merchantReference']);
            Utils::redirect($response['redirectUrl'], false);
        } else {
            // Handle error, e.g. show a message to the user
            Registry::get(Utils::class)->addErrorToDisplay('PAYONE_PCP_CHECKOUT_FAILED');
            return 'payment';
        }
    }
}