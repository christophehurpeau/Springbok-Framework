<?php
class ViewFile extends PhpFile{
	
	protected function loadContent($content){
		parent::loadContent($content);
		$content=$this->_srcContent;
		$content=preg_replace('/{include\s+(APP\.[^}]+)\s*\}/','<?php include $1; ?>',$content);
		
		/*
		$parentFolder=dirname($this->srcFile()->getPath()); $viewsFolder=$this->enhanced->getAppDir().'src/views/';
		for($i=0;$i++<2;)
			$content=preg_replace_callback('/{include\s+([^}]+)\s*\}/',function(&$matches) use(&$parentFolder,&$viewsFolder){
					return file_get_contents(substr($matches[1],0,6)==='VIEWS/'?$viewsFolder.substr($matches[1],6):$parentFolder.DS.$matches[1]);
			},$content);*/
		$content=self::includes($content,dirname($this->srcFile()->getPath()),$this->enhanced->getAppDir().'src/views/',$this->enhanced);
		$this->_srcContent=$content;
	}
	
	public static function &includes($content,$currentPath,$viewsFolder,&$enhanced){
		$content=preg_replace_callback('/{include(Core|Plugin)?\s+([^}]+)\s*\}/i',function($matches) use($currentPath,&$enhanced,&$viewsFolder){
			if(!endsWith($matches[2],'.php')) $matches[2].='.php';
			if(empty($matches[1])){
				if(substr($matches[2],0,6)==='VIEWS/') $filename=$viewsFolder.substr($matches[2],6);
				elseif($matches[2][0]==='/') $filename=$enhanced->getAppDir().substr($matches[2],1);
				else $filename=$currentPath.'/'.$matches[2];
			}else{
				if($matches[1]==='Plugin'){
					list($pluginKey,$fileName)=explode('/',$matches[2],2);
					$filename=$enhanced->pluginPathFromKey($pluginKey);
					$matches[2]=$fileName;
				}else{
					$filename=$core.'includes/views/';
					if(file_exists($filename.$folderName.$matches[2])) $filename.=$folderName;
				}
				$filename.=$matches[2];
			}
			
			return ViewFile::includes(file_get_contents($filename),$currentPath,$viewsFolder,$enhanced);
		},$content);
		return $content;
	}
	
	
	public function enhanceFinalContent($content){
		$content=preg_replace('/<\?=\s*(\$[a-zA-Z0-9_]+)\s*;?\s*\?>/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/<\?=\s*(\$[a-zA-Z0-9_]+(?:\->[a-zA-Z0-9_\(\)]+)+)\s*;?\s*\?>/','<?php echo h2($1) ?>',$content);
		$content=preg_replace('/<\?=\s*(.+)\s*;?\s*\?>/U','<?php echo h2($1) ?>',$content);
		$content=preg_replace('/{(\$[a-zA-Z0-9_]+(?:\[[a-zA-Z0-9_\'\"\->\$]+\])*)}/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/{(\$[a-zA-Z0-9_]+(?:\->[a-zA-Z0-9_\(\),\$]+)+)\}/mU','<?php echo h2($1) ?>',$content);
		$content=preg_replace('/{=\s*([^$]+?)\s*;?\s*\}/','<?php echo h2($1) ?>',$content);
		$content=preg_replace('/<\?\s+(.+)\s*;?\s+\?>/Us','<?php echo $1 ?>',$content);
		
		//Exception à la règle
		$jusqualafin='\$[^}]+(?:{[^}]+}[^}]*)?';
		$content=preg_replace('/{=('.$jusqualafin.')\}/U','<?php echo $1 ?>',$content);
		
		$content=preg_replace('/{\?\s+([^:]+)\s+=>\s+([^}]+)\s+:\s+([^}]+)\s*}/','<?php echo $1 ? $2 : $3 ?>',$content);
		$content=preg_replace('/{=\?\s+([^:]+)\s+=>\s+([^}]+)\s+:\s+([^}]+)\s*}/','<?php echo $1 ? h($2) : h($3) ?>',$content);
		
		$content=preg_replace('/{\?e\s+([^:]+)\s+:\s+([^}]+)\s*}/','<?php echo empty($1) ? $2 : $1 ?>',$content);
		$content=preg_replace('/{=\?e\s+([^:]+)\s+:\s+([^}]+)\s*}/','<?php echo empty($1) ? h($2) : h($1) ?>',$content);
		
		$content=preg_replace('/{if\s+([^}]+?)\s*\}/','<?php if($1): ?>',$content);
		$content=preg_replace('/{ife\s+([^}]+?)\s*\}/','<?php if(empty($1)): ?>',$content);
		$content=preg_replace('/{if!e\s+([^}]+?)\s*\}/','<?php if(!empty($1)): ?>',$content);
		$content=preg_replace('/{else}/','<?php else: ?>',$content);
		$content=preg_replace('/{elseif\s+([^}]+?)\s*\}/','<?php elseif($1): ?>',$content);
		$content=preg_replace('/{elseife\s+([^}]+?)\s*\}/','<?php elseif(empty($1)): ?>',$content);
		$content=preg_replace('/{elseif!e\s+([^}]+?)\s*\}/','<?php elseif(!empty($1)): ?>',$content);
		$content=preg_replace('/{\/if}/','<?php endif; ?>',$content);
		$content=preg_replace('/{ifnull\s+([^}]+?)\s*\}/','<?php if($1===null): ?>',$content);
		$content=preg_replace('/{if!null\s+([^}]+?)\s*\}/','<?php if($1!==null): ?>',$content);
		

		$content=preg_replace('/{f\s+([^}]+?)\s*\}/','<?php foreach($1): ?>',$content);
		$content=preg_replace('/{\/f}/','<?php endforeach; ?>',$content);
		$content=preg_replace('/{while\s+([^}]+?)\s*\}/','<?php while($1): ?>',$content);
		$content=preg_replace('/{\/while}/','<?php endwhile; ?>',$content);
		
		
		$content=preg_replace_callback('/\s*{table(?:\s+([^}]+))?}\s*(.+)\s*{\/table}\s*/Us',function(&$m){
			$isAlternate=true;
			return '<table'.(empty($m[1])?'':' '.implode(' ',array_map(function($p){$p=explode(':',$p,2);return $p[0].'="'.h2(trim($p[1],'\'')).'"';},explode(' ',$m[1])))).'>'//TODO parser
				.preg_replace_callback('#{row}(.*){/row}#Us',function($mr) use(&$isAlternate){return '<tr'.(($isAlternate=!$isAlternate)?' class="alternate"':'').'>'.$mr[1].'</tr>';},$m[2])
				.'</table>';
		},$content);
		
		
		$content=preg_replace('/{\*/','<?php /* ',$content);
		$content=preg_replace('/\*}/',' */ ?>',$content);
		$tmpDir=$this->enhanced->getTmpDir();
		$content=preg_replace_callback('/{jsInline}\s*(.*)\s*{\/jsInline}/Us',function(&$m) use(&$tmpDir){return '<script type="text/javascript">
//<![CDATA[
'.JsFile::executeCompressor($tmpDir,$m[1],false).'
//]]>
</script>';},$content);
		
		$content=preg_replace('/{debug\s+([^}]+?)\s*\}/','<?php debug($1,false) ?>',$content);
		
		$content=preg_replace_callback('/{recursiveFunction\s+([^}]+)\s*(?:use\(([^)]+)\)\s*)\}(.*){\/recursiveFunction}/Us',function(&$m){
			return '<?php UPhp::recursive(function(&$callback,&'.$m[1].')'.(empty($m[2])?'':' use(&'.implode(',&',explode(',',$m[2])).')').'{ ?>'.$m[3].'<?php },'.$m[1].') ?>';
		},$content);
		
		$content=preg_replace('/{icon\s+([^}]+)\s*\}/','<span class="icon $1"></span>',$content);
		
		$content=preg_replace('/{(t|tF|tC)\s+([^}]+)\s*}/U','<?php echo h2(_$1($2)) ?>',$content);
		
		/* HELPERS */
		$content=preg_replace('/{(link|iconLink|iconAction|iconBlockLink|img|imgLink)\s+([^}]+)\s*}/U','<?php echo HHtml::$1($2) ?>',$content);
		$content=preg_replace('/{menuLink\s+([^}]+)\s*}/U','<?php echo HMenu::link($1) ?>',$content);
		$content=preg_replace('/{price\s+([^}]+)\s*}/U','<?php echo HFormat::price($1) ?>',$content);
		$content=preg_replace_callback('/{menu(Top|Left|Right)\s*([^\n]*)\n+(.*)}/Us',function(&$m){
			return'<?php echo HMenu::'.strtolower($m[1]).'(array('.rtrim(implode(',',array_map(function(&$link){return implode('=>',explode(':',trim($link),2));},preg_split('/,\n\s*/',trim($m[3],' \t\n\r\0\x0B,')))),',').')'
				.(empty($m[2])?'':',array('.implode('=>',array_map('trim',explode(':',$m[2]))).')').') ?'.'>';
			},$content);
		
		
		if(strpos($content,'<?=')){ debugCode($content); exit(htmlspecialchars('<?= still exist !')); }
		
		if(preg_match('/^\s*<\?php\s+(.*)(?:\$v\=\s*)?new (?:[A-Za-z]*)View\(.*/Us',$content)>0){
			$content=preg_replace('/^\s*<\?php\s+(.*)(?:\$v\=\s*)?new\s+([A-Za-z]*)View\(/Us',
				'<?php $1$v=new $2View(',$content)
				.'<?php $v->render();?>';
		}
		
		return parent::enhanceFinalContent($content);
	}
	
	public function getEnhancedDevContent(){
		//if(!strpos($this->_devContent,($replace='<footer>')))
		//	$replace='</body>';
		$replace='</html>';
		$this->_devContent=str_replace($replace,'<?php HDev::springbokBar() ?>'.$replace,$this->_devContent);
		/*$replace='<body>';
		$this->_devContent=str_replace($replace,$replace.'<?php HDev::springbokBar() ?>',$this->_devContent);*/
		return parent::getEnhancedDevContent();
	}
}