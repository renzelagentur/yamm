<?php

if(false)
{
    class marm_yamm_oxutilsobject_parent extends oxUtilsObject{}
}

class marm_yamm_oxutilsobject extends marm_yamm_oxutilsobject_parent
{
    
    protected $_sConfigFile = 'marm_yamm.config.php';

    protected $_staticEntries = null;

    public function getModuleVar( $sModuleVarName )
    {
        
        if( !isset( $this->_staticEntries ) && file_exists( getShopBasePath() . $this->_sConfigFile ) )
        {
            include( getShopBasePath() . $this->_sConfigFile );
        }
        
        if( isset( $this->_staticEntries ) && array_key_exists( $sModuleVarName, $this->_staticEntries ) )
        {
            return unserialize($this->_staticEntries[$sModuleVarName]);
        }
        $result = parent::getModuleVar($sModuleVarName);
        
        $sLogDist       = getShopBasePath() . 'log/yamm_entries.txt';
        $sLogMessage    = $sModuleVarName . ': ' .  serialize($result) . "\n";
        
        if(!$this->alreadyWritten)
        {
            if (file_exists($sLogDist)) {
                unlink($sLogDist);
            }
            $this->alreadyWritten = true;
            
        }
        
        if ( ( $oHandle = fopen( $sLogDist, 'a' ) ) !== false ) {
            fwrite( $oHandle, $sLogMessage );
            $blOk = fclose( $oHandle );
        }
        
        return $result;
    }

}

