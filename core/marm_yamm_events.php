<?php

class marm_yamm_events {
	
	public static function activate()
	{
		
	}
	
	public static function deactivate()
	{
		define('MARM_YAMM_TURNED_OF', TRUE);
	}
}
