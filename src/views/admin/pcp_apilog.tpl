[{include file="headitem.tpl" title="SYSREQ_MAIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<form autocomplete="off" name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="pcpapilog_controller">
</form>

[{if $oxid == '-1'}]
    [{oxmultilang ident="PCP_NO_APILOG"}]
    [{else}]
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top;width: 50%;">
                <div style="margin-bottom: 15px;">
                    <input id="requestToClipboard" type="button" onclick="copyRequestToClipboard()" value="[{oxmultilang ident="COPY_REQUEST_TO_CLIPBOARD"}]">
                </div>
                <div><b>REQUEST:</b></div>
                <div><i>[{$edit->pcpapilog__pcp_requesturl->value}]</i></div>
                <div>
                    <pre id="jsonRequest">[{$edit->pcpapilog__pcp_request->value}]</pre>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div style="margin-bottom: 15px;">
                    <input id="responseToClipboard" type="button" onclick="copyResponseToClipboard()" value="[{oxmultilang ident="COPY_RESPONSE_TO_CLIPBOARD"}]">
                </div>
                <div><b>RESPONSE ([{$edit->pcpapilog__pcp_response_httpcode->value}]):</b></div>
                <div>
                    <pre id="jsonResponse">[{$edit->pcpapilog__pcp_response->value}]</pre>
                </div>
            </td>
        </tr>
    </table>
    <script>
        const jsonRequestString = document.getElementById('jsonRequest').innerHTML;
        const jsonRequestData = JSON.parse(jsonRequestString);
        document.getElementById("jsonRequest").innerHTML = JSON.stringify(jsonRequestData, undefined, 2);

        const jsonResponseString = document.getElementById('jsonResponse').innerHTML;
        const jsonResponseData = JSON.parse(jsonResponseString);
        document.getElementById("jsonResponse").innerHTML = JSON.stringify(jsonResponseData, undefined, 2);

        function copyRequestToClipboard() {
            const requestHeadline = "REQUEST\nType: [{$edit->pcpapilog__pcp_requesttype->value}]\nUrl: [{$edit->pcpapilog__pcp_requesturl->value}]\nBody:";
            const jsonRequestString = document.getElementById('jsonRequest').innerHTML;
            const jsonRequestData = JSON.parse(jsonRequestString);
            const jsonRequestFormatted = JSON.stringify(jsonRequestData, undefined, 2);
            const copyString = requestHeadline + "\n" + jsonRequestFormatted;
            navigator.clipboard.writeText(copyString);
            toggleCopied('request');
        }

        function copyResponseToClipboard() {
            const responseHeadline = "RESPONSE ([{$edit->pcpapilog__pcp_response_httpcode->value}])";
            const jsonResponseString = document.getElementById('jsonResponse').innerHTML;
            const jsonResponseData = JSON.parse(jsonResponseString);
            const jsonResponseFormatted = JSON.stringify(jsonResponseData, undefined, 2);
            const copyString = responseHeadline + "\n" + jsonResponseFormatted;
            navigator.clipboard.writeText(copyString);
            toggleCopied('response');
        }

        function toggleCopied(type) {
            const buttonRequest = document.getElementById('requestToClipboard');
            const buttonResponse = document.getElementById('responseToClipboard');
            const requestBaseText = "[{oxmultilang ident="COPY_REQUEST_TO_CLIPBOARD"}]";
            const responseBaseText = "[{oxmultilang ident="COPY_RESPONSE_TO_CLIPBOARD"}]";

            buttonRequest.value = requestBaseText;
            buttonResponse.value = responseBaseText;

            if (type === 'request') {
                buttonRequest.value = requestBaseText + " ✓";
            }
            if (type === 'response') {
                buttonResponse.value = responseBaseText + " ✓";
            }
        }
    </script>
    [{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]