[{*
  * This file is part of a marmalade GmbH project
  *
  * It is Open Source and may be redistributed.
  * For contact information please visit http://www.marmalade.de
  *
  * Version:    1.0
  * Author URI: http://www.marmalade.de
  *}]
[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="box"}]

[{if $updatenav }]
    [{oxscript add="top.oxid.admin.reloadNavigation('`$shopid`');" priority=10}]
[{/if}]


<div id="yamm_metadata">
    <form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
        [{ $oViewConf->getHiddenSid() }]
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="hidden" name="cl" value="marm_yamm_module_metadata">
        <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
    </form>
    
    [{assign var="metadata" value=$oModule->getMetadata()}]
    
    <h3>[{ oxmultilang ident="YAMM_EXTENDED_CLASSES" }]</h3>
    <table>
        <colgroup>
            <col width="33%">
            <col width="68%">
        </colgroup>
        [{foreach from=$metadata.extend item=file key=class}]
        [{cycle values="listitem,listitem2" assign="zebra"}]
        <tr>
            <td class="[{$zebra}]">[{$class}]</td>
            <td class="[{$zebra}]">[{$file}]</td>
        </tr>
        [{/foreach}]
    </table>
    
    <h3>[{ oxmultilang ident="YAMM_FILES" }]</h3>
    <table>
        <colgroup>
            <col width="33%">
            <col width="68%">
        </colgroup>
        [{foreach from=$metadata.files item=file key=class}]
        [{cycle values="listitem,listitem2" assign="zebra"}]
        <tr>
            <td class="[{$zebra}]">[{$class}]</td>
            <td class="[{$zebra}]">[{$file}]</td>
        </tr>
        [{/foreach}]
    </table>
    
    <h3>[{ oxmultilang ident="YAMM_TEMPLATES" }]</h3>
    <table>
        <colgroup>
            <col width="33%">
            <col width="68%">
        </colgroup>
        [{foreach from=$metadata.templates item=file key=class}]
        [{cycle values="listitem,listitem2" assign="zebra"}]
        <tr>
            <td class="[{$zebra}]">[{$class}]</td>
            <td class="[{$zebra}]">[{$file}]</td>
        </tr>
        [{/foreach}]
    </table>
    
    <h3>[{ oxmultilang ident="YAMM_BLOCKS" }]</h3>
    <table>
        <colgroup>
            <col width="33%">
            <col width="33%">
            <col width="35%">
        </colgroup>
        [{foreach from=$metadata.blocks item=block}]
        [{cycle values="listitem,listitem2" assign="zebra"}]
        <tr>
            <td class="[{$zebra}]">[{$block.template}]</td>
            <td class="[{$zebra}]">[{$block.block}]</td>
            <td class="[{$zebra}]">[{$block.file}]</td>
        </tr>
        [{/foreach}]
    </table>
    
</div>
[{include file="bottomitem.tpl"}]

