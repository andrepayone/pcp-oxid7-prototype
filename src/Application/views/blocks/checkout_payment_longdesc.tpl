[{parent}]
<dl>
    <dt>
        <input id="payment_payone_checkout" type="radio" name="paymentid" value="payone_checkout" [{if $oView->getCheckedPaymentId() == 'payone_checkout'}]checked[{/if}]>
        <label for="payment_payone_checkout"><b>[{oxmultilang ident="PAYONE_PCP_CHECKOUT_TITLE"}]</b></label>
    </dt>
    <dd class="[{if $oView->getCheckedPaymentId() == 'payone_checkout'}]activePayment[{/if}]">
        <p>[{oxmultilang ident="PAYONE_PCP_CHECKOUT_DESCRIPTION"}]</p>
    </dd>
</dl>