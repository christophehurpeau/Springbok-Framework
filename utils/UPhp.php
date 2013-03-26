<?php
class UPhp{
	public static function exportCode($var,$ifEmptyArray=false,$addLine=''){
		$content='';
		$content=self::exportCode_recursiveArray($content,$var,true,$addLine);
		if($ifEmptyArray!==false && ($content==='false' || $content==='array()')) return $ifEmptyArray;
		return $content;
	}
	
	public static function exportCode_recursiveArray($content,$array,$start,$addLine=''){
		if(!is_array($array)) self::exportCode_addVar($content,$array);
		else{
			$content.='array(';
			$prevKey=-1;
			foreach($array as $key=>$val){
				if(! (is_int($key) && is_int($prevKey) && $key===($prevKey+1))){
					self::exportCode_addVar($content,$key);
					$content.='=>';
				}
				$content=self::exportCode_recursiveArray($content,$val,false);
				$content=rtrim($content,','.$addLine);
				$content.=','.$addLine;
				$prevKey=$key;
			}
			$content=rtrim($content,',');
			$content.='),';
		}
		return $start?rtrim($content,','):$content;
	}
	
	public static function exportCode_addVar(&$content,$var){
		if(is_string($var)) $content.= self::exportString($var);//var_export($var,true);
		elseif(is_numeric($var)) $content.= $var;
		elseif(is_bool($var)) $content.= $var ? 'true' : 'false';
		elseif(is_null($var)) $content.='null';
		else throw new Exception('exportCode addVar - UNKNOWN : '.print_r($var,true));
	}
	
	public static function exportString($string){
		if(strpos($string,"'")===false) return "'".$string."'";
		if(strpos($string,'"')===false) return '"'.$string.'"';
		
		if(strpos($string,"\n")!==false || strpos($string,"\r")!==false || strpos($string,"\t")!==false
				 || strpos($string,"\v")!==false || strpos($string,"\f")!==false)
			return '"'.str_replace(array('\\',"\n","\r","\t","\v","\f",'$'),array('\\\\','\n','\r','\t','\v','\f','\$'),$string).'"';
		
		$count1=substr_count($string,"'")+substr_count($string,"\\'"); $count2=substr_count($string,'"')+substr_count($string,'$')+substr_count($string,'\\');
		if($count2 < $count1) return '"'.str_replace(array('\\','$','"'),array('\\\\','\$','\"'),$string).'"';
		else return "'".str_replace(array('\\\'','\''),array('\\\\\'','\\\''),$string)."'";
	}
	
	public static function toAnnotation($name,$value){
		return '@'.$name.substr(self::exportCode($value,''),5);
	}
	
	public static function recursive(/* HIDE */$callback,$args/* /HIDE */){
		$callback=func_get_arg(0);
		return call_user_func_array($callback,func_get_args());
	}
	
	public static function tryMultipleTimes($callback,$nb=7,$sleep=array(1,3,10,15,15,30,60)){
		$nb--;
		for($i=0;$i<$nb;){
			try{
				return $callback();
			}catch(Exception $ex){}
			sleep($sleep[$i++]);
		}
		return $callback();
	}
}
