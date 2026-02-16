[{if $pcpResultMessage}]
    <div>
        <b>[{$pcpResultMessage}]</b>
    </div>
<hr id="pcp_setup_result_message">
    [{/if}]

[{if $isPcpDemoModule && $showDemoShopButton}]
    <div>
        <input
                type="submit"
                class="confinput"
                name="setup_demoshop"
                value="[{oxmultilang ident="PCP_SETUP_DEMO_SHOP"}]"
                onClick="triggerShopSetup()"
                [{$readonly}]
        >
        <script>
            function triggerShopSetup() {
                document.module_configuration.fnc.value='pcpSetupShop'
                const showDemoShopButton = document.getElementsByName('confbools[pcpShowDemoShopButton]');
                showDemoShopButton.checked = false;
            }
        </script>
    </div>
<hr>
    [{/if}]

[{$smarty.block.parent}]