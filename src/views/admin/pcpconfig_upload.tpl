[{include file="headitem.tpl" title="SYSREQ_MAIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<form autocomplete="off" name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="pcpconfig_upload_controller">
</form>

[{if $pcpResultMessage}]
    <div>
        <b>[{$pcpResultMessage}]</b>
    </div>
<hr id="pcp_setup_result_message">
    [{/if}]
[{if $isPcpDemoModule}]
    <div>
        <form name="pcpShopLogoForm" id="pcpShopLogoForm" enctype="multipart/form-data" action="[{$oViewConf->getSelfLink()}]" method="post">
            <input type="hidden" name="MAX_FILE_SIZE" value="[{$iMaxUploadFileSize}]">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="oxid" value="[{$oxid}]">
            <input type="hidden" name="cl" value="pcpconfig_upload_controller">
            <input type="hidden" name="fnc" value="pcpUploadShopLogo">
            <table style="border: 0;">
                <tr>
                    <td>
                        <input class="editinput" name="pcpShopLogo" type="file">
                    </td>
                    <td>
                        [{assign var="customShopLogo" value=$oView->pcpGetCustomShopLogo()}]
                        [{if $customShopLogo}]
                    <img class="picPreview" src="[{$customShopLogo}]">
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="ARTICLE_PICTURES_SAVE"}]" [{$readonly}]>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    [{else}]
    <div>
        This is only used for PAYONE Commerce Platform Demo module.
    </div>
    [{/if}]