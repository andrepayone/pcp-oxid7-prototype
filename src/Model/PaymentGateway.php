<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Model;

class PaymentGateway extends PaymentGateway_parent
{
    public function executePayment($dAmount, &$oOrder)
    {
        if ($oOrder->pcpIsReturnFromRedirect()) {
            return true;
        }

        if ($oOrder->isPcpPaymentType() === false) {
            return parent::executePayment($dAmount, $oOrder);
        }

        return $oOrder->pcpHandleAuthorization(false, $this);
    }
}