<?php
class UVarDump{
	private $_objects=array(),$MAX_DEPTH,$html;
	
	public function __construct($MAX_DEPTH=3,$html=null){
		$this->html = $html===null ? !defined('STDIN') : $html;
		$this->MAX_DEPTH=$MAX_DEPTH;
	}
	
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
						$res.=str_repeat($this->color('| ','666'),$currentDepth+1).$this->color($k,'6BCEDE').'=>'.$this->_dumpVar($v,$currentDepth+1).$this->newLine();
					$res=rtrim($res);
				}
			//}
			return self::color('Array: ','BD74BE',$this->html).$res;
		}elseif(is_string($var)) return $this->color(UPhp::exportString($var),'EC7600');
		elseif(is_numeric($var)) return $this->color($var,'FFCD22');
		elseif(is_bool($var)) return $this->color($var?'true':'false','93C763;font-weight:bold');
		elseif(is_null($var)) return $this->color('null','93C763;font-weight:bold');
		else return 'UNKNOWN : '.print_r($var,true);
	}

	public function newLine(){
		return $this->html ? "<br/>"/* pas de \n */ : "\n";
	}
	
	public function color($content,$color){
		return $this->html?'<span style="color:#'.$color.';">'.htmlentities($content,ENT_QUOTES,'UTF-8',true).'</span>':$content;
	}
	
	public static function dump($var,$MAX_DEPTH=3,$html=null){
		$o=new UVarDump($MAX_DEPTH,$html);
		return $o->_dumpVar($var);
	}
}
