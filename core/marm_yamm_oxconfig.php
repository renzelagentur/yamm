<?php

if(false)
{
    class marm_yamm_oxconfig_parent extends oxConfig{}
}

class marm_yamm_oxconfig extends marm_yamm_oxconfig_parent
{    
    protected $_sConfigFile = 'marm_yamm.config.php';
    
    protected $_staticEntries = array();

    public function init()
    {
        parent::init();
        
        if( !isset( $this->_staticEntries ) && file_exists( getShopBasePath() . $this->_sConfigFile ) )
        {
            include( getShopBasePath() . $this->_sConfigFile );
        }
        
        foreach( $this->_staticEntries as $name => $config )
        {
            $this->_aConfigParams[$name] = unserialize($config);
        }
    }
    
}
