<?php
class DelayedEnhanceDaemon extends Daemon{
	public static function start($instanceName){
		class_exists("UFile");
		$baseApp=dirname(APP).'/';
		file_put_contents($baseApp.'block_delayedEnhanceDaemon','');
		sleep(2);
		while(file_exists($baseApp.'block_enhance')) sleep(2);
		$srcDir=$baseApp.'src/';
		$db=DB::init('_enhancedDelayed',array(
			'type'=>'SQLite',
			'file'=>$baseApp.'delayedEnhance.db',
			'flags'=>SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
		));
		while(($row=$db->doSelectRow('SELECT * FROM files LIMIT 1'))){
			$db->doUpdate('DELETE FROM files WHERE path='.$db->escape($row['path']));
			$srcFile=new File($srcDir.$row['path']);
			$devFile=new File($baseApp.'dev/'.$row['path']);
			$prodFile=new File($baseApp.'prod/'.$row['path']);
			
			switch($row['type']){
				case 'Img':
					
					
					break;
			}
		}
		unlink($baseApp.'block_delayedEnhanceDaemon');
	}
	
	public static function _exit(){}
	public static function _restart(){}
}
