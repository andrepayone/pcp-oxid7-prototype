<?php

namespace Payone\PcpPrototype\Core;

use GuzzleHttp\Exception\GuzzleException;
use OxidEsales\Eshop\Core\Registry;
use Payone\Pcp\SDK\Communicator\CommunicatorConfiguration;
use Payone\Pcp\SDK\Communicator\CommunicatorException;
use Payone\Pcp\SDK\Service\CheckoutService;
use Payone\Pcp\SDK\Service\Processor\CheckoutProcessor;
use Payone\Pcp\SDK\Session\Session;

class PayoneApiService
{
    private CheckoutProcessor $checkoutProcessor;

    /**
     * Initializes the service by setting up the SDK communicator and processors.
     */
    public function __construct()
    {
        $config = Registry::getConfig()->getShopConfVar(null, null, 'module:pcpprototype');

        $communicatorConfig = new CommunicatorConfiguration(
            $config['pcpApiEndpoint'] ?? '',
            $config['pcpApiKey'] ?? '',
            $config['pcpApiSecret'] ?? ''
        );

        $sdkSession = new Session();
        $checkoutService = new CheckoutService($communicatorConfig);
        $this->checkoutProcessor = new CheckoutProcessor($checkoutService, $sdkSession);
    }

    /**
     * Creates a new checkout using the PCP SDK.
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

        try {
            $response = $this->checkoutProcessor->createCheckout(
                $amount,
                $currency,
                $merchantReference,
                $returnUrl,
                $notificationUrl
            );

            return [
                'checkoutId' => $response->getCheckoutId(),
                'redirectUrl' => $response->getRedirectUrl(),
            ];
        } catch (CommunicatorException | GuzzleException $e) {
            // In a real module, you would log this error.
            // For now, we return null to indicate failure.
            Registry::getLogger()->error('PAYONE PCP SDK Error: ' . $e->getMessage());
            return null;
        }
    }
}