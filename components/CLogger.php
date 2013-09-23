<?php
define('LOGS_PATH',/*#if DEV */dirname(APP).'/data/logs/'/*#/if*//*#if false*/./*#/if*//*#if PROD*/APP.'logs/'/*#/if*/);

/**
 * Logger class
 * 
 * <code>
 * CLogger::get('test')->log('Hello');
 * </code>
 */
abstract class CLogger{
	static private $_instances;
 
	abstract function log($message = '');
 
	/**
	 *
	 * @param string $name
	 * @param string $type
	 * @return Logger
	 */
	static public function get($name,$type='FileLoggerWithScriptName'){
		if(isset(self::$_instances[$name])) return self::$_instances[$name];
		return self::$_instances[$name]=new $type($name);
	}
}

class FileLogger extends CLogger{
	private $_file;

	public function __construct($name='info'){
		$this->_file=fopen(LOGS_PATH.static::name($name),'a+');
	}

	public function log($message = ''){
		$this->write(date('m-d H:i:s')."\t".$message);
	}
	public function write($message){
		fwrite($this->_file, $message."\r\n");
	}

	public function __destruct(){
		fclose($this->_file);
   }
	
	protected static function name($name,$ext='log'){
		if(stripos($name,'/')===false)
			return date('Y-m-').$name.'.'.$ext;
		return dirname($name).'/'.date('Y-m-').basename($name).'.'.$ext;
	}
}
class FileLoggerWithScriptName extends FileLogger{
	protected static function name($name,$ext='log'){
		return date('Y-m-')./*#if DEV */(class_exists('Springbok',false)?/*#/if*/Springbok::$prefix/*#if DEV */:'')/*#/if*/.$name.'.'.$ext;
	}
}

class SqliteLogger extends CLogger{
	private static $_connect;
	private $name;
	
	private static function connect(){
		if(self::$_connect!==null) return;
		self::$_connect=new SQLite3(APP.'logs'.DS.date('Y-m'),SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		if(!self::$_connect) throw new DBException('Unable to connect',null);
	}
	
	public function __construct($name='info'){
		self::connect();
	}
	
	public function log($message = ''){
		$q = sprintf('INSERT INTO logs (created, ident, priority, message) ' .
					 "VALUES ('%s', '%s', %d, '%s')",
					 $this->_table,
					 strftime('%Y-%m-%d %H:%M:%S', time()),
					 sqlite_escape_string($this->_ident),
					 $priority,
					 sqlite_escape_string($message));
		$columns=array(
			'created'=>array('type'=>'datetime','notnull'=>true,'unique'=>false,'default'=>false),
			'c'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>'"a"'),
			't'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false)
		);
		if(!$db->tableExist('t')) $db->doUpdate('CREATE TABLE logs( created DATETIME, c TEXT, t t');
		
		return sqlite_unbuffered_query($this->_db, $q);
	}
}
