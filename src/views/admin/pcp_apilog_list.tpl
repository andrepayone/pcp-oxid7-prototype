[{include file="headitem.tpl" title="SYSREQ_MAIN_TITLE"|oxmultilangassign box="list"}]
[{assign var="where" value=$oView->getListFilter()}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<script type="text/javascript">
    window.onload = function ()
    {
        top.reloadEditFrame();
        [{ if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid}]');
        [{ /if}]
    }
</script>

<div id="liste">
    <form autocomplete="off" name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{include file="_formparams.tpl" cl="pcpapilog_list_controller" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]

        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                <col width="25%">
                <col width="25%">
                <col width="25%">
                <col width="25%">
            </colgroup>
            <tr class="listitem">
                <td class="listfilter">
                    <div class="r1"><div class="b1">
                            <input class="listedit" type="text" size="30" maxlength="128" name="[{$oView->pcpGetInputName('pcpapilog', 'pcp_timestamp')}]" value="[{$oView->pcpGetWhereValue('pcpapilog', 'pcp_timestamp')}]">
                        </div></div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <div class="find"><input class="listedit" type="submit" name="submitit" value="[{oxmultilang ident="GENERAL_SEARCH" }]"></div>
                            <input class="listedit" type="text" size="30" maxlength="128" name="[{$oView->pcpGetInputName('pcpapilog', 'pcp_requesttype')}]" value="[{$oView->pcpGetWhereValue('pcpapilog', 'pcp_requesttype')}]"><br>
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <div class="find"><input class="listedit" type="submit" name="submitit" value="[{oxmultilang ident="GENERAL_SEARCH" }]"></div>
                            <input class="listedit" type="text" size="30" maxlength="128" name="[{$oView->pcpGetInputName('pcpapilog', 'pcp_refnr')}]" value="[{$oView->pcpGetWhereValue('pcpapilog', 'pcp_refnr')}]">
                        </div>
                    </div>
                </td>
                <td class="listfilter" colspan="2" nowrap>
                    <div class="r1">
                        <div class="b1">
                            <div class="find"><input class="listedit" type="submit" name="submitit" value="[{oxmultilang ident="GENERAL_SEARCH" }]"></div>
                            <input class="listedit" type="text" size="30" maxlength="128" name="[{$oView->pcpGetInputName('pcpapilog', 'pcp_response_httpcode')}]" value="[{$oView->pcpGetWhereValue('pcpapilog', 'pcp_response_httpcode')}]">
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="listheader first" height="15">&nbsp;<a href="Javascript:top.oxid.admin.setSorting( document.search, 'pcpapilog', 'pcp_timestamp', 'asc');document.search.submit();" class="listheader">[{oxmultilang ident="PCP_LIST_HEADER_TIMESTAMP"}]</a></td>
                <td class="listheader"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'pcpapilog', 'pcp_requesttype', 'asc');document.search.submit();" class="listheader">[{oxmultilang ident="PCP_LIST_HEADER_REQUESTTYPE"}]</a></td>
                <td class="listheader"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'pcpapilog', 'pcp_refnr', 'asc');document.search.submit();" class="listheader">[{oxmultilang ident="PCP_LIST_HEADER_REFERENCE"}]</a></td>
                <td class="listheader"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'pcpapilog', 'pcp_response_httpcode', 'asc');document.search.submit();" class="listheader">[{oxmultilang ident="PCP_LIST_RESPONSE_HTTPCODE"}]</a></td>
            </tr>

            [{assign var="blWhite" value=""}]
            [{assign var="_cnt" value=0}]
            [{foreach from=$mylist item=listitem}]
            [{assign var="_cnt" value=$_cnt+1}]
            <tr id="row.[{$_cnt}]">
                [{if $listitem->blacklist == 1}]
                [{assign var="listclass" value=listitem3 }]
                [{else}]
                [{assign var="listclass" value=listitem$blWhite }]
                [{/if}]
                [{if $listitem->getId() == $oxid }]
                [{assign var="listclass" value=listitem4 }]
                [{ /if}]
                <td class="[{$listclass}]" height="15"><div class="listitemfloating">&nbsp;<a href="Javascript:top.oxid.admin.editThis('[{$listitem->pcpapilog__oxid->value}]');" class="[{$listclass}]">[{$listitem->pcpapilog__pcp_timestamp->value}]</a></div></td>
                <td class="[{$listclass}]"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->pcpapilog__oxid->value}]');" class="[{$listclass}]">[{$listitem->pcpapilog__pcp_requesttype->value}]</a></div></td>
                <td class="[{$listclass}]"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->pcpapilog__oxid->value}]');" class="[{$listclass}]">[{$listitem->pcpapilog__pcp_refnr->value}]</a></div></td>
                <td class="[{$listclass}]"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->pcpapilog__oxid->value}]');" class="[{$listclass}]">[{$listitem->pcpapilog__pcp_response_httpcode->value}]</a></div></td>
            </tr>
            [{if $blWhite == "2"}]
            [{assign var="blWhite" value=""}]
            [{else}]
            [{assign var="blWhite" value="2"}]
            [{/if}]
            [{/foreach}]
            [{include file="pagenavisnippet.tpl" colspan="8"}]
        </table>
    </form>
</div>

[{include file="pagetabsnippet.tpl"}]

<script type="text/javascript">
    if (parent.parent)
    {   parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
        parent.parent.sMenuItem    = "[{oxmultilang ident="PCP_ADMIN_TITLE"}]";
        parent.parent.sMenuSubItem = "[{oxmultilang ident="PCP_API_LOG"}]";
        parent.parent.sWorkArea    = "[{$_act}]";
        parent.parent.setTitle();
    }
</script>
