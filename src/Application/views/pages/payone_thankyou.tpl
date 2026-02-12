[{extends file="layout/page.tpl"}]

[{block name="page_content"}]
    <div class="checkout-process">
        <div class="row">
            <div class="col-md-12">
                <h1>[{oxmultilang ident="THANK_YOU"}]</h1>
                <p>[{oxmultilang ident="YOUR_ORDER_HAS_BEEN_PLACED_SUCCESSFULLY"}]</p>
                [{if $sOrderId}]
                <p>
                    [{oxmultilang ident="ORDER_NUMBER"}]
                    <strong>[{$sOrderId}]</strong>
                </p>
                [{/if}]
                <p>[{oxmultilang ident="WE_HAVE_SENT_YOU_AN_EMAIL_WITH_THE_ORDER_DETAILS"}]</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <a href="[{$oViewConf->getHomeLink()}]" class="btn btn-primary">[{oxmultilang ident="CONTINUE_SHOPPING"}]</a>
            </div>
        </div>
    </div>
[{/block}]