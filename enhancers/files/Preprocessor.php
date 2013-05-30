<?php
class Preprocessor{
	private $type;
	public function __construct($type){
		$this->type=$type;
	}
	public function process($defines,$data,$isBrowser=false,$baseDir=null,$ignoreDefines=array()){
		if(empty($defines)) $defines=array();
		if($isBrowser!==null){ $defines['NODE']=!$isBrowser; $defines['BROWSER']=!!$isBrowser; }
		if(($countKeys=count($keys=array_intersect_key($defines,array_flip(array('i','j','k','true','false')))))!==0)
			throw new Exception('Restricted key'.($countKeys===1?'':'s').': '.implode(',',array_keys($keys)));
		$defines['false']=false; $defines['true']=false;
		
		$lastIndex=0; $stack=array();
		while(preg_match('/([ ]*)\/\*[ ]*#(include|ifn?def|if|\/if|endif|else|elif|eval|value|val)[ ]*([^\*]*)[ ]*\*\\\\?\//Uu',$data,$m,PREG_OFFSET_CAPTURE,$lastIndex)){
			$index=$m[0][1]; $lastIndex=$index+strlen($m[0][0]);
			$indent=$m[1][0]; $instruction=$m[2][0]; $content=trim($m[3][0]);
			
			switch($instruction){
				case 'eval':
				case 'value': case 'val':
					if($instruction==='eval'){
						$val='';eval('$val='.$content.';');
						if($val==='') exit(print_r($m,true));
						$include=UPhp::exportCode($val);
					}else{
						$include=$defines[$content];
						if($include===false) $include='false';
						elseif($include===true) $include='true';
						elseif($include===null) $include='null';
					}
					
					$removeAfterLength=0;
					if(substr($data,$lastIndex,2)==='0 ') $removeAfterLength=2;
					elseif(substr($data,$lastIndex,2)==='0;') $removeAfterLength=1;
					elseif(substr($data,$lastIndex,3)==="'' ") $removeAfterLength=3;
					elseif(substr($data,$lastIndex,3)==="'';") $removeAfterLength=2;
					elseif(substr($data,$lastIndex,6)==='false ') $removeAfterLength=6;
					elseif(substr($data,$lastIndex,6)==='false;') $removeAfterLength=5;
					elseif(substr($data,$lastIndex,5)==='true ') $removeAfterLength=5;
					elseif(substr($data,$lastIndex,5)==='true;') $removeAfterLength=4;
					
					$data=substr($data,0,$index).$indent.$include.substr($data,$lastIndex+$removeAfterLength);
					$lastIndex=$index+strlen($include);
					break;
				case 'ifdef': case 'ifndef': case 'if':
					$ignore=false;
					if($negation=$instruction==='if' && substr($content,0,1)==='!')
						$content=trim(substr($content,1));
						
					if(isset($ignoreDefines[$content])){ $ignore=true; $include=''; }
					elseif($instruction==='ifdef') $include=array_key_exists($content,$defines);
					elseif($instruction==='ifndef') $include=!array_key_exists($content,$defines);
					else{
						if(preg_match('/^(.*) then (.*)$/Uu',$content,$m2)){
							if(isset($ignoreDefines[$m2[1]])) break;
							
							$include=$defines[$m2[1]] ? $m2[2] : '';
							$data=substr($data,0,$index).$indent.$include.substr($data,$lastIndex);
							break;
						}else{
							if(!isset($defines[$content])) throw new Exception('Undefined constant "'.$content.'": '.substr($data,$index,$index+100));
							$include=$defines[$content];
						}
					}
					if($negation) $include=!$include;
					
					$stack[]=array('ignore'=>$ignore,'include'=>$include,'index'=>$index,'lastIndex'=>$lastIndex, 'indent'=>$indent);
					break;
				case '/if': case 'endif': case 'else': case 'elif': case 'elseif':
					if(empty($stack))
						throw new Exception('Unexpected '.$instruction.': '.substr($data,$index,$index+100));
					$before=array_pop($stack);
					if(!$before['ignore']){
						$include=substr($data,$before['lastIndex'],$index-$before['lastIndex']);
						if(!$before['include']) $include='';
						
						/*print_r(array('match'=>$m,'before'=>$before,'data'=>$data,'include'=>$include,
								'beforeData'=>substr($data,0,$before['index']),'afterData'=>substr($data,$lastIndex)));
						echo "\n";*/
						
						$data=substr($data,0,$before['index']).$before['indent'].$include.substr($data,$lastIndex);
						$lastIndex=$before['index']+strlen($include);
						
						
					}
					if($instruction==='else'||$instruction==='elif'||$instruction==='elseif'){
						$ignore=false;
						if($before['ignore']){ $ignore=true; $include=''; }
						elseif($instruction==='else') $include=!$before['include'];
						else{
							if(substr($content,0,1)==='!') $include=!$defines[trim(substr($content,1))];
							else $include=$defines[$content];
						}
						$stack[]=array('ignore'=>$ignore, 'include'=>$include, 'index'=>$lastIndex, 'lastIndex'=>$lastIndex, 'indent'=>$indent);
					}
					break;
			}
			
		}
		if(!empty($stack)) throw new Exception('Still have stack : missing endif');
		return $data;
	}
	
	private static function ident($str,$ident){
		$lines = explode("\n",$str);
		foreach($lines as $i=>$line){
			$lines[$i]=$ident+$lines[$i];
		}
		return implode("\n",$lines);
	}
}
