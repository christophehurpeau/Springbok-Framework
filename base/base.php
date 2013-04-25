<?php
function replaceAppAndCoreInFile($file){
	return str_replace(array(APP,CORE,realpath(CORE).'/'),array('APP/','CORE/','CORE/'),$file);
}

function prettyBackTrace($skipLength=1,$trace=false){
	if(!$trace) $trace=debug_backtrace();
	$prettyMessage='';
	// Skip the unecessary stack trace
	if($skipLength && count($trace)>$skipLength)
		$trace=array_slice($trace,$skipLength);
	
	foreach($trace as $i=>$t){
		if(!isset($t['file'])) $t['file']='unknown';
		if(!isset($t['line'])) $t['line']=0;
		if(!isset($t['function'])) $t['function']='unknown';
		$prettyMessage.='#'.$i.' '.replaceAppAndCoreInFile($t['file']).'('.$t['line'].'): ';
		if(isset($t['object']) && is_object($t['object']))
			$prettyMessage.=get_class($t['object']).'->';
		$prettyMessage.=$t['function']."()\n";
	}
	return $prettyMessage;
}
/* DEV */
function openLocalFile($file,$line=null){
	return '<a href="openlocalfile://'.h($file).($line===null?'':'?'.$line).'">';
}

function prettyHtmlBackTrace($skipLength=1,$trace=false){
	if(!$trace) $trace=function_exists('xdebug_get_function_stack') ? xdebug_get_function_stack() : debug_backtrace();
	$prettyMessage='';
	// Skip the unecessary stack trace
	if($skipLength && count($trace)>$skipLength)
		$trace=array_slice($trace,$skipLength);
	
	foreach($trace as $i=>$t){
		$id=uniqid('',true);
		if(!isset($t['file'])) $t['file']='unknown';
		if(!isset($t['line'])) $t['line']=0;
		if(!isset($t['function'])) $t['function']='unknown';
		
		$isGoodFile=file_exists($t['file'])?file_get_contents($t['file']):false;
		
		if($isGoodFile || !empty($t['args'])) $prettyMessage.='<div><a href="javascript:;" style="color:#CC7A00;text-decoration:none;outline:none;" onclick="var el=document.getElementById(\''.$id.'\'); el.style.display=el.style.display==\'none\'?\'block\':\'none\';">';
		$prettyMessage.='#'.$i.' '.($isGoodFile?openLocalFile($t['file'],$t['line']):'').replaceAppAndCoreInFile($t['file']).'('.$t['line'].')'.($isGoodFile?'</a>':'').': ';
		if(isset($t['object']) && is_object($t['object']))
			$prettyMessage.=get_class($t['object']).'->';
		$prettyMessage.=$t['function']."()";
		if($isGoodFile || !empty($t['args']) || !empty($t['params'])){
			$prettyMessage.='</a></div><div id="'.$id.'" style="margin-top:5px;display:none">';
			
			if(!empty($t['args']) || !empty($t['params'])){
				$prettyMessage.='<b>Arguments :</b><br />';
				if(!empty($t['args'])){
					foreach($t['args'] as $num=>$arg){ 
						$prettyMessage.='<i style="color:#AAA;font-size:7pt;">Arg '.$num.'</i> ';
						$prettyMessage.=UVarDump::dump($arg);
						$prettyMessage.="<br />";
					}
				}else{
					foreach($t['params'] as $argName=>$argVal){ 
						$prettyMessage.='<i style="color:#666;font-size:7pt;">'.$argName.'</i> ';
						$prettyMessage.=h($argVal);
						$prettyMessage.="<br />";
					}
				}
				
				
				//echo "\t".str_replace("\n", "\n\t",print_r($t['args'],true))."\n";
			}
			if($isGoodFile){
				$prettyMessage.='<b>File content :</b><br />';
				$prettyMessage.=HText::highlightLine($isGoodFile,'php',$t['line'],false,'background:#EBB',true,4);
			}
			
			$prettyMessage.='</div>';
		}
		else $prettyMessage.='<br />';
	}
	return $prettyMessage;
}

function prettyDebug($message,$skipLength=2,$flush=true,$black=false){
	if(!defined('STDIN')){
		$id=uniqid('',true);
		echo str_pad('<div style="text-align:left;'.($black?'background:#1A1A1A;color:#FCFCFC;border:1px solid #050505':'background:#FFDDAA;color:#333;border:1px solid #E07308').';overflow:auto;padding:1px 2px;position:relative;z-index:999999">'
			.'<pre style="background:#FFF;color:#222;text-align:left;margin:0;overflow:auto;font:normal 1em \'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,\'Courier New\',monospace;">'.$message.'</pre>'
			.'<div style="margin-top:5px"><a href="javascript:;" style="color:#CA6807;text-decoration:none;font-size:7pt;font-style:italic;" onclick="var el=document.getElementById(\''.$id.'\'); el.style.display=el.style.display==\'none\'?\'block\':\'none\';">Afficher / cacher le backtrace</a></div><div id="'.$id.'" class="backtrace" style="display:none">'
			.($skipLength!==false?'<pre style="text-align:left;margin:0;overflow:auto;background:#FFFFCE;color:#222;font:normal 9pt \'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,\'Courier New\',monospace;">'.prettyHtmlBackTrace($skipLength).'</pre>':'')
			.'</div></div><br />',4096);
	}else{
		echo $message;
		if($skipLength!==false) echo PHP_EOL.prettyBackTrace($skipLength);
	}
	if($flush){
		if(ob_get_length()>0) ob_flush();
		flush();
	}
}
function _debug($objects,$flush=true,$MAX_DEPTH=5){
	if(count($objects)===1) $objects=$objects[0];
	prettyDebug(UVarDump::dump($objects,$MAX_DEPTH),2,$flush,true);
}
function debug(){ _debug(func_get_args(),true); }
function debugNoFlush(){ _debug(func_get_args(),false); }

function debugCode($code,$withBacktrace=true){
	prettyDebug(htmlentities(UEncoding::convertToUtf8((string)$code),ENT_QUOTES,'UTF-8',true),$withBacktrace?2:false,true);
}
function debugVar(){
	ob_start();
	call_user_func_array('var_dump',func_get_args());
	$message=ob_get_clean();
	prettyDebug($message,2);
}
function debugVarNoFlush(){
	ob_start();
	call_user_func_array('var_dump',func_get_args());
	$message=ob_get_clean();
	prettyDebug($message,2,false);
}
function debugPrintr($var,$flush=true){
	prettyDebug(htmlentities(print_r($var,true),ENT_QUOTES,'UTF-8'),2,$flush);
}

function dev_test_preg_error(){
	if(preg_last_error() !== PREG_NO_ERROR){
		switch(preg_last_error()){
			case PREG_INTERNAL_ERROR: $strError='Internal Error'; break;
			case PREG_BACKTRACK_LIMIT_ERROR: $strError='Backtrack limit was exhausted!'; break;
			case PREG_RECURSION_LIMIT_ERROR: $strError='Recursion limit was exhausted!'; break;
			case PREG_BAD_UTF8_ERROR: $strError='Bad UTF8'; break;
			case PREG_BAD_UTF8_OFFSET_ERROR: $strError='Bad UTF8 Offset'; break;
			default: $strError='Unknown';
		}
		
		throw new Exception('preg error : '.$strError);
	}
}
function dev_preg_replace($pattern,$replacement,$subject,$limit=-1,&$count=NULL){
	$res=preg_replace($pattern,$replacement,$subject,$limit,$count);
	dev_test_preg_error();
	return $res;
}
function dev_preg_filter($pattern,$replacement,$subject,$limit=-1,&$count=NULL){
	$res=preg_filter($pattern,$replacement,$subject,$limit,$count);
	dev_test_preg_error();
	return $res;
}
function dev_preg_grep($pattern,$input,$flags=0){
	$res=preg_grep($pattern,$input,$flags);
	dev_test_preg_error();
	return $res;
}
function dev_preg_match_all($pattern,$subject,&$matches=NULL,$flags=PREG_PATTERN_ORDER,$offset=0){
	$res=preg_match_all($pattern,$subject,$matches,$flags,$offset);
	dev_test_preg_error();
	return $res;
}
function dev_preg_match($pattern,$subject,&$matches=NULL,$flags=0,$offset=0){
	$res=preg_match($pattern,$subject,$matches,$flags,$offset);
	dev_test_preg_error();
	return $res;
}
function dev_preg_replace_callback($pattern,$callback,$subject,$limit=-1,&$count=NULL){
	$res=preg_replace_callback($pattern,$callback,$subject,$limit,$count);
	dev_test_preg_error();
	return $res;
}
function dev_preg_split($pattern,$subject,$limit=-1,$flags=0){
	$res=preg_split($pattern,$subject,$limit,$flags);
	dev_test_preg_error();
	return $res;
}

function dev_eval($code){
	try{
		$code=eval($code);
		return $code;
	}catch(Exception $e){
		throw new Exception('Unable to eval code : '.$e->getMessage()."\n".$code,0,$e);
	}
}

/* /DEV */

/* PROD */
function prettyDebug($message,$skipLength=2){}
function debug($object){}
function debugNoFlush(){}
function debugCode($code){}
function debugVar($var){}
function debugVarNoFlush(){}
function debugPrintr($var){}
function dev_eval($code){return eval($code);}
/* /PROD */

function h($data,$double=true){return htmlspecialchars((string)$data,ENT_QUOTES,'UTF-8',$double);}
/* PROD */
function h2($data,$double=true){return htmlspecialchars((string)$data,ENT_QUOTES,'UTF-8',$double);}
/* /PROD */
function urlenc($string){return urlencode(urlencode($string)); }
function startsWith($haystack,$needle){ $l=UString::length($needle); return mb_substr($haystack,0,$l)===$needle;}
function endsWith($haystack,$needle){ $l=UString::length($needle); return mb_strrpos($haystack,$needle)===UString::length($haystack)-$l;}

//TODO PhpFileEnhancer
function isE(&$var,$then,$else='ReplaceWithVar'){ return empty($var) ? $then : ($else==='ReplaceWithVar'?$var:$else); }
function notE(&$var,$then,$else=''){ return empty($var) ? $else : $then; }
function isTrue($cond,$then,$else=''){ return $cond===true ? $then : $else; }
function isFalse($cond,$then,$else=''){ return $cond===false ? $then : $else; }

function is_function($f){ return is_object($f) && $f instanceof Closure; }

function render($file,$vars,$return=false){
	extract($vars);
	if($return){
		ob_start();// ob_implicit_flush(false);
		include $file;
		return ob_get_clean();
	}else include $file;
}
/* PROD */
//backward compatibility
function notFoundIfFalse($v){if($v===false)notFound();}
function e/* space */(&$var,$else){ return empty($var) ? $else : $var; }
/* /PROD */

function displayJson($content){
	header('Content-type: application/json; charset=UTF-8');
	echo json_encode($content);
}
function displayXml($content){
	header('Content-type: application/xml; charset=UTF-8');
	echo xmlrpc_encode($content);
}


function br2nl($string){
	return preg_replace('#(\r\n|\r|\n|\n)?\<br\s*/?\>(\r\n|\r|\n|\n)?#i',"\n",$string);
}

/* http://kevin.vanzonneveld.net/techblog/article/create_short_ids_with_php_like_youtube_or_tinyurl/ */
function shortAlphaNumber_enc($number,$index="abcdfghjklmonpqrstvwxyz_012345-ABCDFGHJKLMNOPQRSTVWXYZ~6789"){
	$base=strlen($index);
	
	$result = "";
	for ($t = floor(log($number, $base)); $t >= 0; $t--) {
		$bcp = bcpow($base, $t);
		$a   = floor($number / $bcp) % $base;
		$result = $result . substr($index, $a, 1);
		$number  = $number - ($a * $bcp);
	}
	return strrev($result); // reverse
}
function shortAlphaNumber_dec($string,$index="abcdfghjklmonpqrstvwxyz_012345-ABCDFGHJKLMNOPQRSTVWXYZ~6789"){
	$base=strlen($index);
	
	$number  = strrev($string);
	$result = 0;
	$len = strlen($string) - 1;
	for ($t = 0; $t <= $len; $t++) {
		$bcpow = bcpow($base, $len - $t);
		$result   = $result + strpos($index, substr($number, $t, 1)) * $bcpow;
	}

	$result = sprintf('%F', $result);
	return substr($result, 0, strpos($result, '.'));
}
