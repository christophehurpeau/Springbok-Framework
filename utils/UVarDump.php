<?php
/** Dump vars */
class UVarDump{
	private $_objects=array(),$MAX_DEPTH,$html;
	
	/** @ignore */
	public function __construct($MAX_DEPTH=3,$html=null){
		$this->html = $html===null ? !defined('STDIN') : $html;
		$this->MAX_DEPTH=$MAX_DEPTH;
	}
	
	/**
	 * Dump a var
	 * 
	 * @param mixed
	 * @param int
	 * @return string
	 */
	public function _dumpVar($var,$currentDepth=0){
		if(is_object($var)){
			if($found=(($id=array_search($var,$this->_objects,true))!==false)) $id=array_push($this->_objects,$var);
			$res=$this->color("Object #".($id+1).":",'BD74BE').$this->color(get_class($var),'BD74BE;font-weight:bold');
			if($found===false && $currentDepth<$this->MAX_DEPTH){
				$objectVars = get_object_vars($var);
				if($var instanceof SModel) $objectVars=array_merge($objectVars,$var->_getData());
				if(!empty($objectVars)) $res.=$this->newLine();
				foreach($objectVars as $key=>&$value)
					$res.=str_repeat($this->color('| ','666'),$currentDepth+1).$key.'= '.$this->_dumpVar($value,$currentDepth+1).$this->newLine();
			}
			return $res;
		}elseif(is_resource($var)){
			return '[ressource]';
		}elseif(is_array($var)){
			//if(isset($var[SHORTDEBUGVAR_KEYVAR])) $res=self::color('= & '.$var[SHORTDEBUGVAR_KEYNAME],'e87800',$html);
			//else{
				reset($var);
				if(empty($var)) $res=$this->color('empty','FFF;font-weight:bold');
				else{
					$count=count($var);
					$res=$this->color('size='.$count,'AAA');
					if($count > 100){
						$res.=' (> 100)';
						$var=array_slice($var,0,100,true);
					}
				}
				if($currentDepth<$this->MAX_DEPTH){
					$res.=$this->newLine();
					foreach($var as $k=>&$v)
						$res.=str_repeat($this->color('| ','666'),$currentDepth+1)./*$this->color($k,'6BCEDE')*/$this->_dumpVar($k,$currentDepth)/**/.'=>'.$this->_dumpVar($v,$currentDepth+1).$this->newLine();
					$res=rtrim($res);
				}
			//}
			return self::color('Array: ','BD74BE',$this->html).$res;
		}elseif(is_string($var)){
			$str=$var;
			if(($enc=mb_detect_encoding($str,'UTF-8, ISO-8859-15, ASCII, GBK'))!=='UTF-8')
				$str=iconv($enc,'UTF-8',$str); 
			return $this->color(UPhp::exportString($str),'EC7600').$this->color('['.$enc.']','f2a04d');
		}
		elseif(is_float($var)) return $this->color(strpos($var,'.')===false ? $var.'.0' : $var,'FFCD22');
		elseif(is_numeric($var)) return $this->color($var,'FFCD22');
		elseif(is_bool($var)) return $this->color($var?'true':'false','93C763;font-weight:bold');
		elseif(is_null($var)) return $this->color('null','93C763;font-weight:bold');
		else return 'UNKNOWN : '.print_r($var,true);
	}
	
	/**
	 * @return string
	 */
	public function newLine(){
		return $this->html ? "<br/>"/* pas de \n */ : "\n";
	}
	
	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	public function color($content,$color){
		if($this->html){
			$str=htmlentities($content,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8',true);
			/*#if DEV */
			if(!Springbok::$inError && strpos($str,'�')!==false)
				throw new Exception('This string has a bad character in it : '.$str);
			/*#/if */
			return '<span style="color:#'.$color.';">'.$str.'</span>';
		}
		return $content;
	}
	
	/**
	 * Dump a var
	 * 
	 * @param mixed
	 * @param int
	 * @param bool dump in html or not. If null, autodetect
	 * @return string
	 */
	public static function dump($var,$MAX_DEPTH=3,$html=null){
		$o=new UVarDump($MAX_DEPTH,$html);
		return $o->_dumpVar($var);
	}
	
	/**
	 * Dump a var in html
	 * 
	 * @param mixed
	 * @param int
	 * @return string
	 */
	public static function dumpHtml($var,$MAX_DEPTH=3){
		$black=true; $message=self::dump($var,$MAX_DEPTH,true);
		return '<div style="text-align:left;'.($black?'background:#1A1A1A;color:#FCFCFC;border:1px solid #050505':'background:#FFDDAA;color:#333;border:1px solid #E07308').';overflow:auto;padding:1px 2px;position:relative;z-index:999999">'
			.'<pre style="text-align:left;margin:0;overflow:auto;font:normal 1em \'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,\'Courier New\',monospace;">'.$message.'</pre>'
			.'</div>';
		;
	}
}
