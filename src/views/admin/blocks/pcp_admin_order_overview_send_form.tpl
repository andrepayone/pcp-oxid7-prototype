[{$smarty.block.parent}]
<tr>
    <td class="edittext"></td>
    <td class="edittext" style="border: 1px #0096d6 solid; padding: 5px;">
        <div>
            <img style="padding-top: 5px;max-width: 100px;" src="[{$oViewConf->getModuleUrl('PayonePcpPrototype', 'out/img/payone_logo.png')}]"/>
        </div>
        <div>
            [{assign var=pcpOrderStatus value=$oView->pcpGetOrderState()}]
            [{if $pcpOrderStatus}]
            <table>
                <tr>
                    <td><b>[{oxmultilang ident="PCP_PAYMENT_STATUS"}]:</b></td>
                    <td>[{$pcpOrderStatus.paymentStatusMessage}]</td>
                </tr>
                <tr>
                    <td><b>[{oxmultilang ident="PCP_ORDER_STATUS"}]:</b></td>
                    <td>[{$pcpOrderStatus.checkoutStatusMessage}]</td>
                </tr>
            </table>
            [{if $oView->pcpCaptureAllowed()}]
            <table>
                <tr>
                    <td>
                        <form name="pcpcapture" id="pcpcapture" action="[{$oViewConf->getSelfLink()}]" method="post">
                            [{$oViewConf->getHiddenSid()}]
                            <input type="hidden" name="cl" value="order_overview">
                            <input type="hidden" name="fnc" value="pcpcapture">
                            <input type="hidden" name="oxid" value="[{$oxid}]">
                            <input type="hidden" name="editval[oxorder__oxid]" value="[{$oxid}]">
                            <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="PCP_PAYMENT_CAPTURE"}]" [{$readonly}]>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <form name="pcpcancel" id="pcpcancel" action="[{$oViewConf->getSelfLink()}]" method="post">
                            [{$oViewConf->getHiddenSid()}]
                            <input type="hidden" name="cl" value="order_overview">
                            <input type="hidden" name="fnc" value="pcpcancel">
                            <input type="hidden" name="oxid" value="[{$oxid}]">
                            <input type="hidden" name="editval[oxorder__oxid]" value="[{$oxid}]">
                            <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="PCP_PAYMENT_CANCEL"}]" [{$readonly}]>
                            <label for="pcpcancellationreason">[{oxmultilang ident="PCP_PAYMENT_CANCELLATION_REASON"}]:</label>
                            <select id="pcpcancellationreason" name="pcpcancellationreason">
                                <option value="CONSUMER_REQUEST">[{oxmultilang ident="PCP_CANCEL_CONSUMER_REQUEST"}]</option>
                                <option value="UNDELIVERABLE">[{oxmultilang ident="PCP_CANCEL_UNDELIVERABLE"}]</option>
                                <option value="DUPLICATE">[{oxmultilang ident="PCP_CANCEL_DUPLICATE"}]</option>
                                <option value="FRAUDULENT">[{oxmultilang ident="PCP_CANCEL_FRAUDULENT"}]</option>
                                <option value="ORDER_SHIPPED_IN_FULL">[{oxmultilang ident="PCP_CANCEL_ORDER_SHIPPED_IN_FULL"}]</option>
                                <option value="AUTOMATED_SHIPMENT_FAILED">[{oxmultilang ident="PCP_CANCEL_AUTOMATED_SHIPMENT_FAILED"}]</option>
                            </select>
                        </form>
                    </td>
                </tr>
            </table>
            [{/if}]
            [{if $oView->pcpRefundAllowed()}]
            <table>
                <tr>
                    <td>
                        <form name="pcprefund" id="pcprefund" action="[{$oViewConf->getSelfLink()}]" method="post">
                            [{$oViewConf->getHiddenSid()}]
                            <input type="hidden" name="cl" value="order_overview">
                            <input type="hidden" name="fnc" value="pcprefund">
                            <input type="hidden" name="oxid" value="[{$oxid}]">
                            <input type="hidden" name="editval[oxorder__oxid]" value="[{$oxid}]">
                            <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="PCP_PAYMENT_REFUND"}]" [{$readonly}]>
                        </form>
                    </td>
                </tr>
            </table>
            [{/if}]
            [{/if}]
        </div>
    </td>
</tr>