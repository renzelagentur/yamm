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

<div id="container">

    <form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
        [{ $oViewConf->getHiddenSid() }]
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="hidden" name="cl" value="module_main">
        <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
    </form>

     <div id="infoContent">

         [{ if $aDeletedExt }]
            <div class="msgBox">

                <div class="info">
                    <p>[{ oxmultilang ident="MODULE_EXTENSIONISDELETED" }]</p>
                    <p>[{ oxmultilang ident="MODULE_DELETEEXTENSION" }]</p>
                    <ul>
                        [{foreach from=$aDeletedExt item=aModules key=sOxClass }]
                            [{foreach from=$aModules item=sModule }]
                            <li>[{$sOxClass}]=&gt;[{$sModule}]</li>
                            [{/foreach}]
                        [{/foreach}]
                    </ul>
                </div>

                <div>
                    <form name="remove" action="[{ $oViewConf->getSelfLink() }]" method="post">
                        [{ $oViewConf->getHiddenSid() }]
                        <input type="hidden" name="cl" value="module_sortlist">
                        <input type="hidden" name="fnc" value="remove">
                        <input type="hidden" name="oxid" value="[{ $oxid }]">
                        <input type="hidden" name="aModules" value="">
                        <input type="hidden" name="updatelist" value="1">
                        <input type="submit" name="yesButton" class="saveButton" value="[{oxmultilang ident="GENERAL_YES"}]">
                        <input type="submit" name="noButton" class="saveButton" value="[{oxmultilang ident="GENERAL_NO"}]">
                    </form>
                </div>
            </div>
         [{else}]

             [{if $aExtClasses}]
                <ul class="sortable[{ if $oView->YAMMBlocksControl() }] disabled[{ /if }]" id="aModulesList">
                [{foreach from=$aExtClasses item=aModuleNames key=sClassName }]
                    <li id="[{$sClassName}]">
                        <span>[{$sClassName}]</span>
                        <ul class="sortable2[{ if $oView->YAMMBlocksControl() }] disabled[{ /if }]" id="[{$sClassName}]_modules">
                            [{foreach from=$aModuleNames item=sModule }]
                                [{if is_array($aDisabledModules) && in_array($sModule, $aDisabledModules)}]
                                [{assign var="cssDisabled" value="disabled"}]
                                [{else}]
                                [{assign var="cssDisabled" value=""}]
                                [{/if}]
                                <li id="[{$sModule}]"><span class="[{$cssDisabled}]">[{$sModule}]</span></li>
                            [{/foreach}]
                        </ul>
                    </li>
                [{/foreach}]
                </ul>
             [{/if}]
         [{/if}]
     </div>


	[{ if !$oView->YAMMBlocksControl() }]
	
	    [{oxscript add="$('#aModulesList').oxModulesList();" priority=10}]
	
	    [{oxscript include="js/libs/jquery.min.js"}]
	    [{oxscript include="js/libs/jquery-ui.min.js"}]
	    [{oxscript include="js/libs/json2.js"}]

	    [{oxscript include="js/widgets/oxmoduleslist.js"}]
	[{ /if }]

</div>

[{ if !$aDeletedExt && $aExtClasses && !$oView->YAMMBlocksControl() }]
    <div id="footerBox">
        <div class="buttonBox">
            <form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
                [{ $oViewConf->getHiddenSid() }]
                <input type="hidden" name="cl" value="module_sortlist">
                <input type="hidden" name="fnc" value="save">
                <input type="hidden" name="oxid" value="[{ $oxid }]">
                <input type="hidden" name="aModules" value="">
                <input type="button" name="saveButton" class="saveButton" value="[{ oxmultilang ident="GENERAL_SAVE" }]" disabled>
            </form>
            <div class="description">
                <p>[{ oxmultilang ident="MODULE_DRAGANDDROP" }]</p>
            </div>
        </div>
        
        [{oxscript include=$oViewConf->getModuleUrl('yamm/yamm', 'out/admin/src/js/yamm.js')}]
        
        <div class="buttonBox">
            <div>
                <button class="saveButton" name="exportButton" id="yammExportButton">[{ oxmultilang ident="YAMM_EXPORT" }]</button>
            </div>
            <div class="description">
                <p>[{ oxmultilang ident="YAMM_EXPORT_DESC" }]</p>
            </div>
        </div>
    </div>
[{/if}]


[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]

