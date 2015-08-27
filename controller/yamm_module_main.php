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


class yamm_module_main extends yamm_module_main_parent
{

    public function render()
    {
        $x = parent::render();
        if ( defined('YAMM_TURNED_OFF') )
            return $x;
        return 'yamm_module_main.tpl';
    }

    public function YAMMBlocksControl()
    {
        return oxUtilsObject::getInstance()->getModuleVar(yamm_oxutilsobject::BLOCK_CONTROL);
    }

}
