<?php
abstract class CLogger{
	static private $_instances;
 
    abstract function log($message = '');
 
    /**
     *
     * @param string $name
     * @param string $type
     * @return Logger
     */
    static public function get($name,$type='FileLogger'){
        if(isset(self::$_instances[$name])) return self::$_instances[$name];
        return self::$_instances[$name]=new $type($name);
    }
}

class FileLogger extends CLogger{
	private $_file;

	public function __construct($name='info'){
		$this->_file=fopen(/* DEV */dirname(APP).'/data/logs/'/* /DEV *//* HIDE */./* /HIDE *//* PROD */APP.'logs/'/* /PROD */.date('Y-m-')./* DEV */(class_exists('Springbok',false)?/* /DEV */Springbok::$prefix/* DEV */:'')/* /DEV */.$name.'.log', 'a+');
	}

	public function log($message = ''){
		fwrite($this->_file, date('m-d H:i:s')."\t".$message."\r\n");
	}

	public function __destruct(){
		fclose($this->_file);
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
