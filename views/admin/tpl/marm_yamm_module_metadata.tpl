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

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="marm_yamm_module_metadata">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<pre>
[{php}]$myvar = $this->get_template_vars('oModule'); var_dump($myvar->getMetadata());[{/php}]
</pre>

[{include file="bottomitem.tpl"}]

