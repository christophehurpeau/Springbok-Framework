<?php
class STestCheck{
	private $testClass,$var,$varInfo,$length;
	
	/**
	 * @param STest
	 * @param mixed
	 * @param string|null
	 */
	public function __construct($testClass,$var,$varInfo=null){
		$this->testClass=$testClass;
		$this->var=$var;
		$this->varInfo=$varInfo;
	}
	
	/**
	 * @param mixed
	 * @return mixed
	 */
	private function _varInfoOr($or){
		return $this->varInfo===null?$or:$this->varInfo;
	}
	
	/**
	 * @param string
	 * @param string
	 */
	private function ex($message,$details){
		$this->testClass->ex($message,$details);
	}
	
	/* INFO : if you want add methods here, don't forget to add it in the doc in STest class ! */
	
	
	/* ALL */
	
	/**
	 * @param mixed the expected value
	 * @return STestCheck|self
	 */
	public function equals($expected){
		if($this->var!==$expected)
			throw new Exception('[value] '.UVarDump::dump($this->var,5,false).' !== [expected] '.UVarDump::dump($expected,5,false));
		return $this;
	}
	
	/* ARRAY */
	
	/**
	 * Check if the value is an array
	 * 
	 * @return STestCheck|self
	 */
	public function isArray(){
		if(!is_array($this->var))
			$this->ex($this->_varInfoOr('The var').' is not an array','type='.gettype($this->var).', var= '.$this->var);
		return $this;
	}
	
	/**
	 * Check if the value is an array and count the size of it
	 * 
	 * @return int
	 */
	public function _getCount(){
		$this->isArray();
		if($this->length===null) return $this->length=count($this->var);
		return $this->length;
	}
	
	/**
	 * The array value must have the exact size
	 * 
	 * @param int
	 * @return STestCheck|self
	 */
	public function size($size){
		if($this->_getCount()!==$size)
			$this->ex($this->_varInfoOr('The array').' has a size of '.$this->_getCount().', not '.$size,'array= '.UVarDump::dump($this->var,3,false));
		
	}
	
	/* ARRAY OR STRING */
	
	/**
	 * The value must contains $string
	 * 
	 * @return STestCheck|self
	 */
	public function contains($string){
		if(is_string($this->var)){
			if(UString::pos($this->var,$string)===false)
				$this->ex($this->_varInfoOr('The string').' does not contains "'.$string.'"','string= '.$this->var);
		}elseif( is_array($this->var) ){
			if(array_key_exists($string,$this->var)===false)
				$this->ex($this->_varInfoOr('The array').' does not contains the key "'.$string.'"','array= '.UVarDump::dump($this->var,3,false));
		}else
			$this->ex($this->_varInfoOr('This').' is not a string nor an array','val= '.UVarDump::dump($this->var,3,false));
	}
	
	
	/* STRING */
	
	/**
	 * Checks if the value is a string
	 * 
	 * @return STestCheck|self
	 */
	public function isString(){
		if(!is_string($this->var))
			$this->ex($this->_varInfoOr('The var').' is not a string','type='.gettype($this->var).', var= '.UVarDump::dump($this->var,3,false));
		if(($enc=mb_detect_encoding($this->var,'UTF-8, ISO-8859-15, ASCII, GBK'))!=='UTF-8')
			$this->ex($this->_varInfoOr('The string').' is not an UTF-8 string','encoding='.($enc?$enc:'unknown').', string= '.iconv($enc,'UTF-8',$this->var));
		return $this;
	}
	/**
	 * Checks if the value is a string and return its length
	 * 
	 * @return int
	 */
	public function _getLength(){
		$this->isString();
		if($this->length===null) return $this->length=UString::length($this->var);
		return $this->length;
	}
	
	/**
	 * The string should have a maximum length (<=)
	 * 
	 * @param int
	 * @return STestCheck|self
	 */
	public function maxLength($maxLength){
		$l=$this->_getLength();
		$l=strlen($this->var);
		if($l>$maxLength)
			$this->ex('The length of '.$this->_varInfoOr('the string').' is > '.$maxLength,'size= '.$l.', string= '.$this->var);
		return $this;
	}
	
	/**
	 * The string should have a minimum length (>=)
	 * 
	 * @param int
	 * @return STestCheck|self
	 */
	public function minLength($minLength){
		$l=$this->_getLength();
		if($l<$minLength)
			$this->ex('The length of '.$this->_varInfoOr('the string').' is < '.$minLength,'size= '.$l.', string= '.$this->var);
		return $this;
	}
	
	/**
	 * The string should not have two consecutive space
	 * 
	 * @return STestCheck|self
	 */
	public function doubleSpace(){
		$this->isString();
		/*if(strpos($this->var,'  ')!==false)*/
		if(preg_match('/\h{2,}/u',$this->var))
			$this->ex($this->_varInfoOr('The string').' has at least two successive spaces','string= '.preg_replace('/\h/u','[space]',$this->var));
		return $this;
	}
	
}
