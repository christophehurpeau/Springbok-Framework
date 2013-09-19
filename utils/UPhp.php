<?php
/** PHP Code utils */
class UPhp{
	/**
	 * Export a var into real PHP Code
	 * 
	 * @param string
	 * @param string if !==false : return this value if content is false or content === array()
	 * @param string
	 * @return string
	 */
	public static function exportCode($var,$ifEmptyArray=false,$addLine=''){
		$content='';
		$content=self::exportCode_recursiveArray($content,$var,true,$addLine);
		if($ifEmptyArray!==false && ($content==='false' || $content==='array()')) return $ifEmptyArray;
		return $content;
	}
	
	private static function exportCode_recursiveArray($content,$array,$start,$addLine=''){
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
	
	private static function exportCode_addVar(&$content,$var){
		if(is_string($var)) $content.= self::exportString($var);//var_export($var,true);
		elseif(is_numeric($var)) $content.= $var;
		elseif(is_bool($var)) $content.= $var ? 'true' : 'false';
		elseif(is_null($var)) $content.='null';
		else throw new Exception('exportCode addVar - UNKNOWN : '.print_r($var,true));
	}
	
	/**
	 * Export a string into a PHP string optimized (using "" or '')
	 * 
	 * @param string
	 * @return string
	 */
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
	
	/**
	 * Create an Springbok PHP annotation
	 * 
	 * @param string
	 * @param mixed
	 * @return string
	 */
	public static function toAnnotation($name,$value){
		return '@'.$name.substr(self::exportCode($value,''),5);
	}
	
	/**
	 * Use for recursive things, like a tree
	 * <code>
	 * $dir=new RecursiveDirectoryIterator($folderPath,FilesystemIterator::SKIP_DOTS);
	 * UPhp::recursive(function($callback,$children){
	 * 	foreach($children as $path=>$file){
	 * 	if($file->isDir())
	 * 		$callback($callback,new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS));
	 * 	else{
	 * 		echo $path;
	 * 	}
	 * },$dir);
	 * </code>
	 * 
	 * @param function
	 * @return mixed
	 */
	public static function recursive(/*#if false*/$callback,$args/*#/if*/){
		$callback=func_get_arg(0);
		return call_user_func_array($callback,func_get_args());
	}
	
	/**
	 * Try a callback multiple times, if it fails sleep before retrying
	 * 
	 * @param function
	 * @param int max number of 
	 * @param array
	 * @return mixed the callback result
	 */
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
