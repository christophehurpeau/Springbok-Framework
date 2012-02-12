<?php
class JsAppFile extends JsFile{
	public static function viewToJavascript($content,$varName=false){
		//$view=preg_replace('/\n+/','";'.$varName.'+="',$view);
		
		$content=preg_replace_callback('/{menu(Top|Left|Right)\s*([^\n]*)\n+(.*)}/Us',function(&$m){
			return'+++<$$.menu.'.strtolower($m[1]).'({'.rtrim(implode(',',array_map(function(&$link){return implode(':',explode(':',trim($link),2));},preg_split('/,?\n\s*/',trim($m[3],' \t\n\r\0\x0B,')))),',').'}'
				.(empty($m[2])?'':',{'.implode(':',array_map('trim',explode(':',$m[2]))).'}').')>+++';
			},$content);
		
		$content=trim($content);
		return implode('+',array_map(function(&$c){return $c[0]==='<'&&substr($c,-1)==='>'?trim(substr($c,1,-1)):json_encode($c);},explode('+++',$content)));
	}
}
