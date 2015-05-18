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

[{if $aErrors}]
<h3 class="error">Errors:</h3>
<ul class="error">
[{foreach from=$aErrors item=oException}]
    <li>[{$oException->getMessage()}]</li>
[{/foreach}]
</ul>
[{/if}]

[{if $sOutputType == "echo" && $aGeneratedOutput}]
    [{foreach from=$aGeneratedOutput item=sGeneratedOutput}]
    <div>
        [{$sGeneratedOutput|highlight_string:true}]
    </div>
    [{/foreach}]
[{/if}]
<div>
    <h3>Export Einstellungen: </h3>
    <form action="[{$sAction}]" method="POST">
        <table>
            <tbody>
                [{if $blHasParentShop}]
                <tr>
                    <td class="edittext">
                        <label for="blInheritConfigFromParent">Config von Vatershop erben:</label>
                    </td>
                    <td class="edittext">

                        <input type="hidden" name="yamm_export[blInheritConfigFromParent]" value="0"/>
                        <input type="checkbox" name="yamm_export[blInheritConfigFromParent]" id="blInheritConfigFromParent" value="1" [{if $blInheritConfigFromParent}]checked="checked"[{/if}]/>
                    </td>
                </tr>

                <tr>
                    <td class="edittext">
                        <label for="yamm_export[iOverwriteParent]">Vatershop ID überschreiben:</label>
                    </td>
                    <td class="edittext">
                        [{$iOverwriteParent}]
                        <select  name="yamm_export[iOverwriteParent]" class="select">
                            [{foreach from=$aShopIds item=sShopId}]
                            <option [{if $sShopId == $iOverwriteParent}]selected="selected"[{/if}]>[{$sShopId}]</option>
                            [{/foreach}]
                        </select>
                    </td>
                </tr>

                [{/if}]
                <tr>
                    <td class="edittext">
                        <label for="blExportDisabledModules">Aktivieren von inaktiven Modulen sperren:</label>
                    </td>
                    <td class="edittext">

                        <input type="hidden" name="yamm_export[blExportDisabledModules]" value="0"/>
                        <input type="checkbox" name="yamm_export[blExportDisabledModules]" id="blExportDisabledModules" value="1" [{if $blExportDisabledModules}]checked="checked"[{/if}]/>
                    </td>
                </tr>

                <tr>
                    <td class="edittext">
                        <label for="blExportClassOrder">Klassenreihenfolge forcieren:</label>
                    </td>
                    <td class="edittext">

                        <input type="hidden" name="yamm_export[blExportClassOrder]" value="0"/>
                        <input type="checkbox" name="yamm_export[blExportClassOrder]" id="blExportClassOrder" value="1" [{if $blExportClassOrder}]checked="checked"[{/if}]/>
                    </td>
                </tr>

                <tr>
                    <td class="edittext">
                        <label for="blWriteToFileSystem">Config ausgeben:</label>
                    </td>
                    <td class="edittext">
                        <input type="radio" id="sOutputType" name="yamm_export[sOutputType]" value="echo" [{if $sOutputType == "echo"}]checked="checked"[{/if}]/>
                    </td>
                </tr>
                <tr>
                    <td class="edittext">
                        <label for="blWriteToFileSystem">Config herunterladen:</label>
                    </td>
                    <td class="edittext">
                        <input type="radio" id="sOutputType" name="yamm_export[sOutputType]" value="download" [{if $sOutputType == "download"}]checked="checked"[{/if}]/>
                    </td>
                </tr>

                <tr>
                    <td class="edittext">
                        <label for="aShopIds">Für Shops erzeugen (Auswahl leer lassen, für nur aktueller Shop):</label>
                    </td>
                    <td class="edittext">
                        <select multiple="multiple" name="yamm_export[aShopIds][]" class="select" size="[{math equation="x / 3" x=$aShopIds|@count}]">
                            [{foreach from=$aShopIds item=sShopId}]
                                <option [{if in_array($sShopId, $aSelectedShopIds)}]selected="selected"[{/if}]>[{$sShopId}]</option>
                            [{/foreach}]
                        </select>
                    </td>
                </tr>

                [{if $sContext != 'production'}]
                <tr>
                    <td class="edittext">
                        <label for="blWriteToFileSystem">Config im Dateisystem überschreiben:</label>
                    </td>
                    <td class="editselect">
                        <input type="radio" id="sOutputType" name="yamm_export[sOutputType]" value="save" [{if $sOutputType == "save"}]checked="checked"[{/if}]/>
                    </td>
                </tr>
                [{/if}]

                <tr>
                    <td class="edittext">
                    </td>
                    <td class="edittext">
                        <input type="submit"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]