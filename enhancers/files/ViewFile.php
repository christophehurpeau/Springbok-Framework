<?php
class ViewFile extends PhpFile{
	public static $CACHE_PATH='views_8.6';
	
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
				elseif($matches[2][0]==='/') $filename=$enhanced->getAppDir().'src/'.substr($matches[2],1);
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
			
			return ViewFile::includes('<?php /* FILE : '.replaceAppAndCoreInFile($filename).' */ ?>'."\n".file_get_contents($filename),$currentPath,$viewsFolder,$enhanced);
		},$content);
		return $content;
	}
	
	
	public function enhanceFinalContent($content){
		$content=preg_replace('/{=include ([^}]+)\}/U','<?php echo "<?php include $1 ?>" ?>',$content);
		
		$jusqualafin='[^}]+(?:{[^}]+}[^}]*)?'; $t=$this;
		
		
		foreach(array(''=>function($c){return '<?php '.$c.' ?>';},
					'='=>function($c){return '<?php echo \'<?php '.str_replace("'",'\\\'',$c).' ?>\' ?>';}) as $prfx=>$callback){
			$callbackCreator=function($string) use($callback){ return function($m) use($callback,$string){
					if(isset($m[1])) $string=str_replace('$1',$m[1],$string);
					if(isset($m[2])) $string=str_replace('$2',$m[2],$string);
					return $callback($string);}; };
			
			$content=preg_replace_callback('/{'.$prfx.'(else)?if\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?ife\s+([^}]+?)\s*\}/',function($m) use($callback){
				$m[2]=implode(')&&empty(',preg_split('/\s*\&\&\s*/',$m[2]));
				$m[2]=implode(')||empty(',preg_split('/\s*\|\|\s*/',$m[2]));
				return $callback((empty($m[1])?'':$m[1]).'if(empty('.$m[2].')):');
			},$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?if!e\s+([^}]+?)\s*\}/',function($m) use($callback){
				$m[2]=implode(')&&!empty(',preg_split('/\s*\&\&\s*/',$m[2]));
				$m[2]=implode(')||!empty(',preg_split('/\s*\|\|\s*/',$m[2]));
				return $callback((empty($m[1])?'':$m[1]).'if(!empty('.$m[2].')):');
			},$content);
			$content=preg_replace('/{'.$prfx.'else}/',$callback('else:'),$content);
			$content=preg_replace('/{'.$prfx.'\/if}/',$callback('endif;'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?ifnull\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2===null):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?if!null\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2!==null):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?ifTrue\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2===true):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?if!True\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2!==true):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?ifFalse\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2===false):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'(else)?if!False\s+([^}]+?)\s*\}/',$callbackCreator('$1if($2!==false):'),$content);
			
	
			$content=preg_replace_callback('/{'.$prfx.'f\s+([^}]+?)\s*\}/',$callbackCreator('foreach($1):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'\/f}/',$callbackCreator('endforeach;'),$content);
			$content=preg_replace_callback('/{'.$prfx.'while\s+([^}]+?)\s*\}/',$callbackCreator('while($1):'),$content);
			$content=preg_replace_callback('/{'.$prfx.'\/while}/',$callbackCreator('endwhile;'),$content);
			
			$content=preg_replace_callback('/{'.$prfx.'debug\s+([^}]+?)\s*\}/',$callbackCreator('debugNoFlush($1)'),$content);
			$content=preg_replace_callback('/{'.$prfx.'debugVar\s+([^}]+?)\s*\}/',$callbackCreator('debugVarNoFlush($1)'),$content);
			$content=preg_replace_callback('/{'.$prfx.'jsReady\}/',$callbackCreator('HHtml::jsReadyStart()'),$content);
			$content=preg_replace_callback('/{'.$prfx.'\/jsReady\}/',$callbackCreator('HHtml::jsReadyEnd()'),$content);
		}
		
		
		
		$content=preg_replace('/<\?=\s*(\$[a-zA-Z0-9_]+)\s*;?\s*\?>/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/<\?=\s*(\$[a-zA-Z0-9_]+(?:\->[a-zA-Z0-9_\(\)]+)+)\s*;?\s*\?>/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/<\?=\s*(.+)\s*;?\s*\?>/U','<?php echo h($1) ?>',$content);
		$content=preg_replace('/{(\$[a-zA-Z0-9_]+(?:\[[a-zA-Z0-9_\'\"\->\$]+\])*)}/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/{(\$[a-zA-Z0-9_]+(?:\->[a-zA-Z0-9_\(\),\$]+)+)\}/mU','<?php echo h($1) ?>',$content);
		$content=preg_replace('/{=\s*([^$]+?)\s*;?\s*\}/','<?php echo h($1) ?>',$content);
		$content=preg_replace('/<\?\s+(.+)\s*;?\s+\?>/Us','<?php echo $1 ?>',$content);
		
		//Exception à la règle
		$content=preg_replace_callback('/{=(\$'.$jusqualafin.')\}/U',function($m) use($t){
				return '<?php echo '.$t->enhancePhpContent(substr($m[1],0,2)==='E.'?'HElement::'.substr($m[1],2):$m[1]).' ?>';},$content);
		
		$content=preg_replace('/{\?\s+([^:]+)\s+=>\s+([^}]+)\s+:\s+([^}]+)\s*}/','<?php echo $1 ? $2 : $3 ?>',$content);
		$content=preg_replace('/{=\?\s+([^:]+)\s+=>\s+([^}]+)\s+:\s+([^}]+)\s*}/','<?php echo $1 ? h($2) : h($3) ?>',$content);
		
		$content=preg_replace('/{\?e\s+([^\s]+)\s+=>\s+([^}]+)\s+:\s+([^}]+)\s*}/','<?php echo empty($1) ? $2 : $3 ?>',$content);
		$content=preg_replace('/{\?e\s+([^:]+)\s+:\s+([^}]+)\s*}/','<?php echo empty($1) ? $2 : $1 ?>',$content);
		$content=preg_replace('/{=\?e\s+([^:]+)\s+:\s+([^}]+)\s*}/','<?php echo empty($1) ? h($2) : h($1) ?>',$content);
		
		$content=preg_replace_callback('/\s*{table(?:\s+([^}]+))?}\s*(.+)\s*{\/table}\s*/Us',function(&$m){
			return '<?php $itable=0; ?><table'.ViewFile::params($m).'>'
				.preg_replace_callback('#\s*{row(?:\s+((?:[^}]+(?:{[^}]+})*)*))?}\s*(.*)\s*{/row}\s*#Us',function($mr) use(&$isAlternateRow){
						return '<tr<?php if($itable++%2) echo \' class="alternate"\' ?>'.ViewFile::params($mr).'>'.$mr[2].'</tr>';},$m[2])
				.'</table>';
		},$content);
		
		
		$content=preg_replace('/{\*/','<?php /* ',$content);
		$content=preg_replace('/\*}/',' */ ?>',$content);
		$tmpDir=$this->enhanced->getTmpDir();
		$content=preg_replace_callback('/{jsInline}\s*(.*)\s*{\/jsInline}/Us',function($m) use($tmpDir,$t){return '<script type="text/javascript">//<![CDATA[
'.JsFile::executeCompressor($tmpDir,preg_replace('/{icon\s+([^}]+)\s*\}/','<span class="icon $1"></span>',$m[1]),false).'
//]]>
</script>';},$content);
		
		
		$content=preg_replace_callback('/{recursiveFunction\s+([^}]+)\s*(?:use\(([^)]+)\)\s*)?\}(.*){\/recursiveFunction}/Us',function($m){
			return '<?php UPhp::recursive(function($callback,'.$m[1].')'.(empty($m[2])?'':' use('.$m[2].')').'{ ?>'.$m[3].'<?php },'.$m[1].') ?>';
		},$content);
		
		$content=preg_replace('/{icon(32|)\s+([^}]+)\s*\}/','<span class="icon$1 $2"></span>',$content);
		
		$content=preg_replace('/{(t|tF|tC)\s+([^}]+)\s*}/U','<?php echo h(_$1($2)) ?>',$content);
		
		/* HELPERS *///TODO use $jusqualafin
		$content=preg_replace('/{(link|linkHtml|iconLink|iconLinkHtml|iconAction|iconBlockLink|img|imgLink|cutLink)\s+([^}]+)\s*}/U','<?php echo HHtml::$1($2) ?>',$content);
		$content=preg_replace('/{menuLink\s+([^}]+)\s*}/U','<?php echo HMenu::link($1) ?>',$content);
		$content=preg_replace('/{price\s+([^}]+)\s*}/U','<?php echo HFormat::price($1) ?>',$content);
		$content=preg_replace_callback('/{menu(topHtml|Top|LeftHtml|Left|Right)\s*([^\n]*)\n+(.*)}/Us',function(&$m){
			$lines=array_map(function(&$link){return implode('=>',explode(':',trim($link),2));},preg_split('/,\n\s*/',trim($m[3],' \t\n\r\0\x0B,')));
			return'<?php echo HMenu::'.lcfirst($m[1]).'(array('.rtrim(implode(',',$lines),',').')'
				.(empty($m[2])?'':',array('.implode('=>',array_map('trim',explode(':',$m[2]))).')').') ?'.'>';
		},$content);
		
		
		if(strpos($content,'<?=')){ debugCode($content); exit(htmlspecialchars('<?= still exist !')); }
		
		if(preg_match('/^\s*<\?php\s+(.*)(?:\$v\=\s*)?new (?:[A-Za-z]*)View\(.*/Us',$content)>0){
			$content=preg_replace('/^\s*<\?php\s+(.*)(?:\$v\=\s*)?new\s+([A-Za-z]*)View\(/Us',
				'<?php $1$v=new $2View(',$content)
				.'<?php $v->render();?>';
		}
		$content=str_replace('</body>','<?php HHtml::displayJsReady() ?></body>',$content);
		return parent::enhanceFinalContent($content);
	}

	public static function params(&$m){//TODO parser
		//debugVar($m);
		return empty($m[1])?'':(' '.implode(' ',array_map(function($p){$p=explode(':',$p,2);return $p[0].'="'.h(trim($p[1],'\'')).'"';},explode(' ',$m[1]))));
	}
	
	public function getEnhancedDevContent(){
		//if(!strpos($this->_devContent,($replace='<footer>')))
		//	$replace='</body>';
		if(strpos($this->fileName(),'mails')===false && basename(dirname($this->srcFile()->getPath()))!=='mails'){
			$replace='</html>';
			$this->_devContent=str_replace($replace,'<?php HDev::springbokBar() ?>'.$replace,$this->_devContent);
			$replace='<body>';
			$this->_devContent=str_replace($replace,'<?php HDev::body() ?>'.$replace,$this->_devContent);
		}
		/*$replace='<body>';
		$this->_devContent=str_replace($replace,$replace.'<?php HDev::springbokBar() ?>',$this->_devContent);*/
		return parent::getEnhancedDevContent();
	}
}