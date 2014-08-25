<?php
/**
 * This file is part of a marmalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.marmalade.de
 *
 * Version:    1.0
 * Author URI: http://www.marmalade.de
 */


class marm_yamm_module_main extends marm_yamm_module_main_parent
{

    public function render()
    {
        $x = parent::render();
        if ( defined('MARM_YAMM_TURNED_OFF') )
            return $x;
        return 'marm_yamm_module_main.tpl';
    }

    public function YAMMBlocksControl()
    {
        return oxUtilsObject::getInstance()->getModuleVar(marm_yamm_oxutilsobject::BLOCK_CONTROL);
    }

}
