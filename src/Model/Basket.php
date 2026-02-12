<?php

namespace Payone\PcpPrototype\Model;

use OxidEsales\Eshop\Application\Model\Basket as OxidBasket;

class Basket extends Basket_parent
{
    /**
     * Returns the basket price in the smallest currency unit (e.g., cents).
     *
     * @return int
     */
    public function getPayoneBasketAmount(): int
    {
        $price = $this->getPrice();
        if (!$price) {
            return 0;
        }

        return (int) round($price->getBruttoPrice() * 100);
    }
}