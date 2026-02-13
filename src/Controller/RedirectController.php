<?php

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;

class RedirectController extends FrontendController
{
    /**
     * Handles the return from the PAYONE payment page.
     * In a real scenario, this would involve verifying the payment status via another API call.
     * For now, we assume the payment was successful, finalize the order,
     * and redirect to the thank you page.
     *
     * @return void
     */
    public function render()
    {
        parent::render();

        /** @var Session $session */
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $user = $this->getUser();

        // For this prototype, we assume the payment was successful upon return.

        /** @var Order $order */
        $order = oxNew(Order::class);

        // Finalize the order using the current basket and user.
        $orderState = $order->finalizeOrder($basket, $user, false);

        // Check if order was finalized successfully
        if ($orderState === \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_OK) {
            $session->setVariable('payone_last_order_id', $order->getId());
            // Redirect to our custom thank you page.
            Utils::redirect(Registry::getConfig()->getShopUrl() . 'index.php?cl=payone_thankyou', false);
        }

        // If order finalization fails, redirect to payment page with an error.
        Registry::get(Utils::class)->addErrorToDisplay('PAYONE_PCP_ORDER_FAILED');
        Utils::redirect(Registry::getConfig()->getShopUrl() . 'index.php?cl=payment', false);
    }
}