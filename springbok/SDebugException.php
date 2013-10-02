<?php
/**
 * Debug and dump args
 */
class SDebugException extends SDetailedException{
	private $args;
	
	/**
	 * @param string $title
	 * @param mixed... $vars
	 */
	public function __construct(/*#if false*/$title,$var1,$var2/*#/if*/){
		$args=func_get_args();
		$title=array_shift($args);
		parent::__construct($title);
		$this->args=$args;
	}
	
	/**
	 * @return string
	 */
	public function getDetails(){
		$MAX_DEPTH=5;
		return UVarDump::dump(count($this->args)===1?$this->args[0]:$this->args,$MAX_DEPTH,false);
	}
	
	/**
	 * @return string
	 */
	public function detailsHtml(){
		$MAX_DEPTH=5;
		return UVarDump::dumpHtml(count($this->args)===1?$this->args[0]:$this->args,$MAX_DEPTH,true);
	}
}
