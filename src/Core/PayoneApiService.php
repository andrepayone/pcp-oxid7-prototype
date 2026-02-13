<?php

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\Registry;
use PayoneCommercePlatform\Sdk\ApiClient\CheckoutApiClient;
use PayoneCommercePlatform\Sdk\ApiClient\CommunicatorException;
use PayoneCommercePlatform\Sdk\CommunicatorConfiguration;
use PayoneCommercePlatform\Sdk\Errors\ApiErrorResponseException;
use PayoneCommercePlatform\Sdk\Models\CreateCheckoutRequest;

class PayoneApiService
{
    private CheckoutApiClient $checkoutApiClient;

    /**
     * Initializes the service by setting up the SDK communicator and client.
     */
    public function __construct()
    {
        $config = Registry::getConfig()->getShopConfVar(null, null, 'module:pcpprototype');

        $communicatorConfig = new CommunicatorConfiguration(
            $config['pcpApiEndpoint'] ?? '',
            $config['pcpApiKey'] ?? '',
            $config['pcpApiSecret'] ?? ''
        );

        $this->checkoutApiClient = new CheckoutApiClient($communicatorConfig);
    }

    /**
     * Creates a new checkout using the PCP SDK's CheckoutApiClient.
     *
     * @param int $amount The amount in the smallest currency unit (e.g., cents).
     * @param string $currency The ISO currency code.
     * @param string $merchantReference A unique reference for this transaction.
     * @return array|null An array containing 'checkoutId' and 'redirectUrl' on success, null on failure.
     */
    public function createCheckout(int $amount, string $currency, string $merchantReference): ?array
    {
        $returnUrl = Registry::getConfig()->getShopUrl() . 'index.php?cl=payone_redirect';
        $notificationUrl = Registry::getConfig()->getShopUrl() . 'index.php?cl=payone_notification';

        $checkoutRequest = new CreateCheckoutRequest();
        $checkoutRequest->setAmount($amount);
        $checkoutRequest->setCurrency($currency);
        $checkoutRequest->setMerchantReference($merchantReference);
        $checkoutRequest->setReturnUrl($returnUrl);
        $checkoutRequest->setNotificationUrl($notificationUrl);

        try {
            $response = $this->checkoutApiClient->createCheckout($checkoutRequest);

            return [
                'checkoutId' => $response->getCheckoutId(),
                'redirectUrl' => $response->getRedirectUrl(),
            ];
        } catch (ApiErrorResponseException | CommunicatorException $e) {
            Registry::getLogger()->error('PAYONE PCP SDK Error: ' . $e->getMessage(), [$e]);
            return null;
        }
    }
}