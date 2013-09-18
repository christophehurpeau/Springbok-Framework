<?php
if($FirePHPExists=file_exists(PEAR_INSTALL_DIR.'/FirePHPCore/FirePHP.class.php'))
	include PEAR_INSTALL_DIR.'/FirePHPCore/FirePHP.class.php';

class CFirebug{
	private static $_i;
	
	public static function init(){ self::$_i=FirePHP::getInstance(true); }
	
	public static function isAvailable(){ return self::$_i !== null && self::$_i->detectClientExtension(); }
	
	public static function enable(){ self::$_i->setEnabled(true); }
	public static function disable(){ self::$_i->setEnabled(false); }
	public static function isEnabled(){ return self::$_i->getEnabled(); }
	
	/**
	 * Log object to firebug
	 * 
	 * @see http://www.firephp.org/Wiki/Reference/Fb
	 * @param mixed $Object
	 * @return true
	 * @throws Exception
	 */
	public static function send(){ return call_user_func_array(array(self::$_i,'fb'),func_get_args()); }
	
	/**
	 * Start a group for following messages
	 * 
	 * Options:
	 *   Collapsed: [true|false]
	 *   Color:	 [#RRGGBB|ColorName]
	 *
	 * @param string $Name
	 * @param array $Options OPTIONAL Instructions on how to log the group
	 * @return true
	 */
	public static function group($Name, $Options=null){ return self::$_i->group($Name, $Options); }
	
	/**
	 * Ends a group you have started before
	 *
	 * @return true
	 * @throws Exception
	 */
	public static function groupEnd(){ return self::send(null, null, FirePHP::GROUP_END); }
	
	/**
	 * Log object with label to firebug console
	 *
	 * @see FirePHP::LOG
	 * @param mixes $Object
	 * @param string $Label
	 * @return true
	 * @throws Exception
	 */
	public static function log($Object, $Label=null){ return self::send($Object, $Label, FirePHP::LOG); }
	
	
	/**
	 * Log object with label to firebug console
	 *
	 * @see FirePHP::INFO
	 * @param mixes $Object
	 * @param string $Label
	 * @return true
	 * @throws Exception
	 */
	public static function info($Object, $Label=null){ return self::send($Object, $Label, FirePHP::INFO); } 

	/**
	 * Log object with label to firebug console
	 *
	 * @see FirePHP::WARN
	 * @param mixes $Object
	 * @param string $Label
	 * @return true
	 * @throws Exception
	 */
	public static function warn($Object, $Label=null){ return self::send($Object, $Label, FirePHP::WARN); } 

	/**
	 * Log object with label to firebug console
	 *
	 * @see FirePHP::ERROR
	 * @param mixes $Object
	 * @param string $Label
	 * @return true
	 * @throws Exception
	 */
	public static function error($Object, $Label=null){ return self::send($Object, $Label, FirePHP::ERROR); }

	/**
	 * Dumps key and variable to firebug server panel
	 *
	 * @see FirePHP::DUMP
	 * @param string $Key
	 * @param mixed $Variable
	 * @return true
	 * @throws Exception
	 */
	public static function dump($Key, $Variable){ return self::send($Variable, $Key, FirePHP::DUMP); }

	/**
	 * Log a trace in the firebug console
	 *
	 * @see FirePHP::TRACE
	 * @param string $Label
	 * @return true
	 * @throws Exception
	 */
	public static function trace($Label){ return self::send($Label, FirePHP::TRACE); } 

	/**
	 * Log a table in the firebug console
	 *
	 * @see FirePHP::TABLE
	 * @param string $Label
	 * @param string $Table
	 * @return true
	 * @throws Exception
	 */
	public static function table($Label, $Table){ return self::send($Table, $Label, FirePHP::TABLE); }
}

if($FirePHPExists) CFirebug::init();