<?php
/**
 * This file is part of a yammalade GmbH project
 *
 * It is Open Source and may be redistributed.
 * For contact information please visit http://www.yammalade.de
 *
 * Version:    1.0
 * Author URI: http://www.yammalade.de
 */


class yamm_module_list extends yamm_module_list_parent
{
    public function render()
    {
        $x = parent::render();
        if ( defined('YAMM_TURNED_OFF') )
            return $x;
        return 'yamm_module_list.tpl';
    }

}
