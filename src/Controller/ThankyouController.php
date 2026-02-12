<?php

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class ThankyouController extends FrontendController
{
    /**
     * The template file for this controller.
     * @var string
     */
    protected $_sThisTemplate = 'payone_thankyou.tpl';

    /**
     * Renders the thank you page.
     * It retrieves the last order ID from the session and assigns it to the view data.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $session = Registry::getSession();
        $lastOrderId = $session->getVariable('payone_last_order_id');
        $this->_aViewData['sOrderId'] = $lastOrderId;

        // Clear the session variable after displaying it
        $session->deleteVariable('payone_last_order_id');
        // Clear basket
        $session->getBasket()->deleteBasket();

        return $this->_sThisTemplate;
    }
}