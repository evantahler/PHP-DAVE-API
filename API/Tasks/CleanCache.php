<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a task that will clean both the DB and file caches
***********************************************/

class CleanCache extends task
{		
	protected static $description = "Use me to clean both the DB and file-based cache";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $DBOBJ;
		
		if (self::check_DBObj())
		{
			/////////////////////////////////////////////////////////////////////////
			// Check the CACHE DB table for old entries, and remove them
			$SQL= 'DELETE FROM `'.$CONFIG['DB'].'`.`'.$CONFIG['CacheTable'].'` WHERE (`ExpireTime` < "'.(time() - $CONFIG['CacheTime']).'") ;';
			$Status = $DBOBJ->GetStatus();
			if ($Status === true)
			{
				$DBOBJ->Query($SQL);
				$this->task_log('Deleted '.$DBOBJ->NumRowsEffected()." entries from the CACHE DB");
			}
		
			/////////////////////////////////////////////////////////////////////////
			// Check the CACHE Folder table for old entries, and remove them
			$files = scandir($CONFIG['CacheFolder']);
			$counter = 0;
			foreach ($files as $num => $fname)
			{
				$ThisFile = $CONFIG['CacheFolder'].$fname;
				if (file_exists($ThisFile) && ((time() - filemtime($ThisFile)) > $CONFIG['CacheTime']) && $fname != "." && $fname != ".." && $fname != ".svn") 
				{
					unlink($ThisFile);
					$counter++;
				}
			}
			$this->task_log('Deleted '.$counter." files from the CACHE directory");
		}
	}
}

?>