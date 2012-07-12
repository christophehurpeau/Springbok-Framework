<?php
class DelayedEnhance{
	private static $instance;
	
	public static function &get(EnhancedApp $enhanced){
		if(self::$instance===null) self::$instance=new DelayedEnhance($enhanced);
		return self::$instance;
	}
	
	private $disabled;
	
	public function __construct(EnhancedApp $enhanced){
		if($this->disabled=(!class_exists('App',false))) return;
		$this->db=DB::init('_enhancedDelayed',array(
			'type'=>'SQLite',
			'file'=>$enhanced->getAppDir().'delayedEnhance.db',
			'flags'=>SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
		));
		$dbSchema=new DBSchemaSQLite($this->db,'files');
		$dbSchema->setModelInfos(array(
			'primaryKeys'=>array('path'),
			'columns'=>array(
				'path'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false),
				'type'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false),
			)
		));
		if(!$dbSchema->tableExist()) $dbSchema->createTable();
		$this->db->beginTransaction();
	}
	
	public function add($path,$type){
		if($this->disabled) return;
		CLogger::get('delayedEnhance')->log('add: '.$path.' - '.$type);
		$this->db->doUpdate('INSERT OR IGNORE INTO `files`(`path`,`type`) VALUES ('.$this->db->escape($path).','.$this->db->escape($type).')');
	}
	
	public function commit(){
		if($this->disabled) return;
		$this->db->commit();
	}
}
