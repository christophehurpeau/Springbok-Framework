<?php
class CssFile extends EnhancerFile{
	private $_devSrcContent,$_config=array('compress'=>true);

	public function setConfig($name,$val){$this->_config[$name]=$val;}
	
	public function loadContent($srcContent){
		if(!$this->isCore()){
			if(file_exists($filename=dirname($this->srcFile()->getPath()).DS.'_functions.css'))
				$srcContent=file_get_contents($filename).$srcContent;
			$srcContent=file_get_contents(CORE.'includes/_functions.css').$srcContent;
		}elseif($this->fileName()!='_functions.css' && $this->fileName()!='_default_const.css')
			$srcContent=file_get_contents(CORE_SRC.'includes/_functions.css').$srcContent;
		
		$currentPath=dirname($this->srcFile()->getPath());
		$this->_devSrcContent=self::includes($srcContent,$currentPath,true);
		$this->_srcContent=self::includes($this->_devSrcContent,$currentPath,false);
		$this->_srcContent=self::includes($this->_srcContent,$currentPath,true);
		$this->_srcContent=self::includes($this->_srcContent,$currentPath,false);
	}

	public function writeDevFile($devFile){
		if($this->_config['compress']) self::executeCompressor($this->getEnhancedDevContent(),$devFile->getPath(),true);
		else $devFile->write($this->getEnhancedDevContent());
		if(!empty(self::$APP_DIR) && !$this->isCore()){
			if(!file_exists(self::$APP_DIR.'tmp/compiledcss/dev/')) mkdir(self::$APP_DIR.'tmp/compiledcss/dev/',0755,true);
			$devFile->copyTo(self::$APP_DIR.'tmp/compiledcss/'.$devFile->getName());
		}
	}
	public function writeProdFile($prodFile){
		if($this->_config['compress']) self::executeCompressor($this->getEnhancedProdContent(),$prodFile->getPath());
		else $prodFile->write($this->getEnhancedProdContent());
		if(!empty(self::$APP_DIR)){
			if(!file_exists(self::$APP_DIR.'tmp/compiledcss/prod/')) mkdir(self::$APP_DIR.'tmp/compiledcss/prod/',0755);
			$prodFile->copyTo(self::$APP_DIR.'tmp/compiledcss/prod/'.$prodFile->getName());
		}
	}
	
	public static function executeCompressor($content,$destination,$nomunge=false){
		$dest=$destination?$destination:tempnam('/tmp','yuidest');
		$javaExecutable = 'java';
		$jarFile=CLIBS.'_yuicompressor-2.4.7.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --type css'.($nomunge?' --nomunge':'').' --line-break 8000 -o '.escapeshellarg($dest);
		$tmpfname = tempnam('/tmp','yui');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		if(!empty($res)){
			debugVar($res);
			if(preg_match('/^\s*\[ERROR\]\s+([0-9]+)\:([0-9]+)\:/',$res,$m)){
				prettyDebug(HText::highlightLine($content,null,$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')));
			}
			exit;
		}
		unlink($tmpfname);
		//chmod($dest,0777);
		if(!$destination){
			$destination=file_get_contents($dest);
			unlink($dest);
			return $destination;
		}
	}

	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){
		return $this->enhanceCssContent($this->_devSrcContent);
	}
	public function getEnhancedProdContent(){
		$content=$this->enhanceCssContent($this->_srcContent);
		// Strip // comments
		//$content=preg_replace('/\/\/(.*)?\n/','',$content);
		// Strip /* */ comments
		//$content=preg_replace('/\/\*[\s\S]*?\*\//m','',$content);

		//$content=preg_replace('/\s*}/','}',$content);
		//$content=preg_replace('/;+\s*/',';',$content);
		//$content=preg_replace('/\s*([{|,])\s*/','$1',$content);
		//$content=preg_replace_callback('/\s*{(.*)}\s*/m',function($matches){
		//	return '{'.preg_replace('/\s*:\s*/m',':',$matches[1]).'}';
		//},$content);
////                $content=preg_replace('/\s*({.*)\s*:\s*(.*})\s*/m','$1:$2',$content);

		// reduce non-newline whitespace to one
		//$content=preg_replace('/[ \f]+/',' ',$content);
		// newlines (preceded by any whitespace) to a whitespace (rare)
		//$content=preg_replace('/\s*\n+/m',' ',$content);
		//$content=str_replace(';;',';',$content);
		//$content=str_replace(';}','}',$content);

		/*$content=str_replace(':white;',':#FFF;',$content);
		$content=str_replace('border:none','border:0',$content);*/
//		$content=preg_replace('/([ |:]0)[px|pt|em]/im','$1',$content);

		//$content=$this->removeWS_B_E($content);
		
		return $content;
	}

	private function enhanceCssContent($content){
		if($this->fileName()=='_default_const.css') return $content;
		$content=$this->instr_for($content); // avant constantes.
		$content=$this->constantes($content);
		$content=$this->if_endif($content);
		$content=$this->functions($content);
		$content=$this->phpfunctions($content);
		//$content=$this->comments($content);
		$content=$this->recursive_selectors($content);

		// Tabs to nothing (usally used for indentation)
		$content=preg_replace('/\t/','',$content);
		return trim($content);
	}

	public static function &includes($content,$currentPath,$include){
		$content=preg_replace_callback('/@'.($include?'include':'import').'(Core|Lib)?\s+\'([\w\s\._\-\/]+)\'\;/Ui',function($matches) use($currentPath,$include){
			/*if(!empty($matches[1]) && $matches[1]==='Core') */$core=defined('CORE')?CORE:CORE_SRC;
			if(empty($matches[1])) $filename=$currentPath.'/';
			else{
				$filename=$matches[1]==='Lib' ? dirname($core).'/' : $core;
				$filename.='includes/';
				if(file_exists($filename.'css/'.$matches[2])) $filename.='css/';
			}
			$filename.=$matches[2];
			
			return CssFile::includes(file_get_contents($filename),$currentPath,$include);
		},$content);
		return $content;
	}


	public function &constantes(&$content){
		$matches=array();
		$constantes=array(
			/* _global.css */
			'SEP_BORDER'=>'1px solid #BBB',
		
			/* _page.css */
			'PAGE_FIXED'=>'false',
			'HEADER_BACKGROUND'=>'false',
			'HEADER_COLOR'=>'false',
			'HEADER_BORDER_RADIUS'=>'false',
			
			'FIXED_VARIABLE_SHADOW'=>'false',
			'FIXED_BACKGROUND'=>'false',
			'FIXED_BORDER'=>'false',
			'FIXED_VARIABLE_MARGIN'=>'false',
			'VARIABLE_BACKGROUND'=>'false',
			'VARIABLE_BORDER'=>'false',
			'VARIABLE_SHADOW'=>'false',
			'FIXED_VARIABLE_BORDER'=>'false',
			'VARIABLE_H1_COLOR'=>'#FFF',
			'VARIABLE_H1_BACKGROUND'=>'false',
			'VARIABLE_H1_TEXT_SHADOW'=>'false',
			'VARIABLE_CONTENT_BACKGROUND'=>'false',
			
			'IE6_VARIABLE_WIDTH'=>'700px',
			
			'PAGE_SHADOWED_BOX_BACKGROUND'=>'#FFF',
			'PAGE_SHADOWED_BOX_BORDER'=>'false',
			'PAGE_SHADOWED_BOX_SHADOW_COLOR'=>'#888',
			
			'BREADCRUMBS_IN_VARIABLE'=>'false',
			'BREADCRUMBS_BACKGROUND'=>'false',
			'BREADCRUMBS_FONTSIZE'=>'8pt',
			
			'INTERACTION_DEFAULT_TEXT_SHADOW'=>'false',
			
			/* _menu.css */
			'HEADER_MENU_SHADOW'=>'false',
			'HEADER_MENU_BORDER_RADIUS'=>'false',
			'HEADER_MENU_BORDER'=>'false',
			'HEADER_MENU_BACKGROUND'=>'false',
			'HEADER_MENU_BACKGROUND_TB'=>'false',
			'HEADER_MENU_COLOR'=>'false',
			
			'HEADER_MENU_A_BORDER'=>'false',
			'HEADER_MENU_A_BORDER_BOTTOM'=>'false',
			'HEADER_MENU_A_BACKGROUND'=>'false',
			'HEADER_MENU_A_BACKGROUND_TB'=>'false',
			'HEADER_MENU_A_BORDER_RADIUS'=>'3px',
			'HEADER_MENU_A_SHADOW'=>'FALSE',
			'HEADER_MENU_A_TEXT_SHADOW'=>'FALSE',
			'HEADER_MENU_A_CURRENT_BACKGROUND'=>'false',
			'HEADER_MENU_A_CURRENT_BACKGROUND_TB'=>'false',
			'HEADER_MENU_A_CURRENT_COLOR'=>'false',
			'HEADER_MENU_A_CURRENT_BORDER_BOTTOM'=>'false',
			'HEADER_MENU_A_HOVER_BACKGROUND'=>'false',
			'HEADER_MENU_A_HOVER_BACKGROUND_TB'=>'false',
			'HEADER_MENU_A_HOVER_COLOR'=>'false',
			'HEADER_MENU_A_HOVER_SHADOW'=>'false',
			'HEADER_MENU_A_HOVER_BORDER_BOTTOM'=>'false',
			'HEADER_MENU_A_CURRENTHOVER_COLOR'=>'false',
			'HEADER_MENU_A_CURRENTHOVER_BACKGROUND'=>'false',
			'HEADER_MENU_A_CURRENTHOVER_BACKGROUND_TB'=>'false',
			'HEADER_MENU_A_CURRENT_HOVER_BACKGROUND'=>'false',
			'HEADER_MENU_A_CURRENT_HOVER_BACKGROUND_TB'=>'false',
			'HEADER_MENU_A_CURRENT_HOVER_COLOR'=>'false',
			'HEADER_MENU_A_CURRENT_HOVER_SHADOW'=>'false',
			'VARIABLE_MENU_COLOR'=>'false',
			'VARIABLE_MENU_CURRENT_BACKGROUND'=>'false',

			/*  _form.css */
			'FORM_LEGEND_COLOR'=>'false',
			'FORM_SMALL_LABEL_WIDTH'=>'60px',
			'FORM_MEDIUM_LABEL_WIDTH'=>'90px',
			'FORM_LABEL_WIDTH'=>'180px',
			'FORM_LARGE_LABEL_WIDTH'=>'280px',
			
			/* table.css */
			'COLOR_COMPARE_SAME'=>'#464',
			'COLOR_COMPARE_DIFF'=>'#644',
			
			/* buttons.css */
			'BUTTON_BACKGROUND_TB'=>'#555555,#444444',
			'BUTTON_BOXSHADOW'=>'#777777',
			'BUTTON_BORDER'=>'1px solid #111',
			'BUTTON_COLOR'=>'#EEE',
			'BUTTON_TEXTSHADOW'=>'#999',
			'BUTTON_HOVER_BACKGROUND_TB'=>'#666666,#555555',
			'BUTTON_HOVER_BORDER'=>'1px solid #333333',
			'BUTTON_HOVER_COLOR'=>'#FFF',
			'BUTTON_FOCUS_BACKGROUND_TB'=>'#555555,#777777',
			'BUTTON_FOCUS_COLOR'=>'#FFF',
			'BUTTON_TEXTSHADOW'=>'#555',
		);
		for($i=1;$i<=5;$i++){
			$constantes['COLOR'.$i]='false';
			$constantes['BACKGROUND_COLOR'.$i]='false';
			$constantes['BACKGROUND_TB_COLOR'.$i]='false';
			$constantes['BLOCK_COLOR'.$i.'_BACKGROUND']='false';
			$constantes['BLOCK_COLOR'.$i.'_COLOR']='false';
			$constantes['BLOCK_COLOR'.$i.'_BORDER']='false';
			$constantes['BUTTON_COLOR'.$i.'_COLOR']='false';
			$constantes['BUTTON_COLOR'.$i.'_BORDER']='false';
			$constantes['BUTTON_COLOR'.$i.'_TEXTSHADOW']='false';
			$constantes['BUTTON_COLOR'.$i.'_BOXSHADOW']='false';
		}
		
		$content=preg_replace_callback('/@CONST\s+([0-9\w_-]+)\s*=\s*\'?([0-9\w\s\._\-\#\,]+)\'?;/Ui',function($matches) use(&$constantes){
			$constantes[$matches[1]]=$matches[2];
			return '';
		},$content);
		uksort($constantes,function(&$k1,&$k2){return strlen($k1)<strlen($k2);}); // trie les constantes du plus grd au moins grd pour éviter de remplacer des bouts de constantes
		
		foreach($constantes as $const=>$replacement)
			$content=str_replace(array('$'.$const,$const),$replacement,$content);
		return $content;
	}
	
	public function if_endif(&$content,$i=4){
		$expr='IF(?:NOT)?\([^)]+\).*ENDIF';
		for($j=0;$j<$i;$j++) $expr=str_replace('IF(?:NOT)?\([^)]+\).*ENDIF','IF(NOT)?\(([^)]+)\)(.*(?:IF(?:NOT)?\([^)]+\).*ENDIF.*)*)ENDIF',$expr);
		
		//'/IF(NOT)?\(([^)]+)\)(.*(?:IF(?:NOT)?\([^)]+\).*ENDIF.*)*)ENDIF/Us'
		$isCore=$this->isCore();
		$content=preg_replace_callback('/'.$expr.'/Us',function($matches) use(&$isCore){
			$vals=explode(' ',$matches[2]);
			foreach($vals as $key=>&$val){
				$val=trim($val);
				if(in_array($val,array('false','true','&&','||','AND','OR','FALSE','TRUE','==','===','!=','!=='))) continue;
				$lastChar=strlen($val)-1;
				if(preg_match('/^[A-Z_]+$/',$val)){
					if(!$isCore) debug('Constant undefined : '.$val);
					return $matches[2]; //une constante
				}
				//$vals[$key]='true';
				$vals[$key]="'".$vals[$key]."'";
			}
			if($matches[0]==='IF(BACKGROUND_TB_COLOR1) bg-gradient-tb(BACKGROUND_TB_COLOR$I); ENDIF') debugVar($vals);
			//debugVar($matches[2].' => '.'return '.implode(' ',$vals).';');
			$eval='return '.implode(' ',$vals).';';
			//$eval=str_replace('true true','true',str_replace('true true','true',$eval));
			$eval=str_replace("' '",' ',str_replace("' '",' ',$eval));
			$eval=eval($eval);
			return (empty($matches[1])?$eval:!$eval)?$matches[3]:'';
		}, $content);
		if($i>1) return $this->if_endif($content,$i-1);
		return $content;
	}

	public function instr_for(&$content){
		return preg_replace_callback('/FOR\(([^)]+)\)(.*)ENDFOR/Us',function($matches){
			//I=0;I<10;I+1
			$for=explode(';',$matches[1]);
			list($var,$initVal)=explode('=',$for[0],2);
			
			$strRet='';
			$currentVal=$initVal;
			while(eval('return '.str_replace($var,$currentVal,$for[1]).';')){
				$strRet.=str_replace('$'.$var,$currentVal,$matches[2]);
				$currentVal=eval('return '.str_replace($var,$currentVal,$for[2]).';');
			}
			return $strRet;
		}, $content);
	}

	public function &functions(&$content){
		$functions=array();$matches=array();
		$content=preg_replace_callback('/function\s+([\w_-]+)\(([\w_\-\,\$]*)\)\s*\{\s*((?:.*(?:\{[^}]*\})*.*)+)\s*\}\n?/Us',function($matches) use(&$functions){
			$fcont=$matches[3];
			$params=explode(',',trim($matches[2]));
			$replace=array();$num=0;
			foreach($params as $param) $replace[$param]='$'.$num++;
			$fcont=str_replace(array_keys($replace),array_values($replace),$fcont);
			$functions[$matches[1]]=$fcont;
			return '';
		},$content);
		foreach($functions as $fname=>$fcontent){
			$content=preg_replace_callback('/'.$fname.'\((.*)\)([;|}])/',function($matches) use(&$fcontent){
				$params=explode(',',trim($matches[1]));
				$replace=array();$num=0;
				foreach($params as $param) $replace['$'.$num++]=$param;
				return rtrim(str_replace(array_keys($replace),array_values($replace),$fcontent),';').$matches[2];
			},$content);
		}
		return $content;
	}
	
	public function &phpfunctions(&$content){
		$functions=array('lighten'=>array('UColors','_lighten'),'darken'=>array('UColors','_darken'),'findBestFgColor'=>array('UColors','findBestFgColor'));
		foreach($functions as $fname=>$callback){
			$content=preg_replace_callback('/'.$fname.'\((.*)\)([;|}])/',function($matches) use(&$fcontent,&$callback){
				$params=explode(',',trim($matches[1]));
				return call_user_func_array($callback,$params).$matches[2];
			},$content);
		}
		return $content;
	}
	
	public function &comments(&$content){
		$content=preg_replace('#/\*.*?\*/#s','',$content);
		$content=preg_replace('#(?<!:)(//.*)#','',$content);
		return $content;
	}
	
	public function &recursive_selectors($content){
		$content=preg_replace_callback('/([^{};\/]+){((?:[^{}]+(?:{[^}]*}\s*)?)*)}/',function($matches){
			$foundInternalSelectors=array();$selector=explode(',',trim($matches[1]));
			$internalContent=preg_replace_callback('/[;|}|\n]([^{;}\/]+){([^}]*)}/',function($matches) use(&$foundInternalSelectors,&$selector){
				$internalSelector=explode(',',trim($matches[1]));
				$finalInternalSelector=array();
				foreach($selector as $selctr){ $selctr=trim($selctr);
					foreach($internalSelector as $iselctr){
						$finalInternalSelector[]=$selctr.' '.trim($iselctr);
					}
				}
				$finalInternalSelector=implode(',',$finalInternalSelector);
				$foundInternalSelectors[]=$finalInternalSelector.'{'.$matches[2].'}';
				return ';';
			},$matches[2]);
			return $matches[1].'{'.rtrim($internalContent,'; ').'}'.implode(PHP_EOL,$foundInternalSelectors);
		},$content);
		return $content;
	}
	
	
	private static $spriteGenDone=NULL;
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		if(self::$spriteGenDone===NULL && ($enhanced->hasChanges('Css') || $enhanced->hasChanges('Img'))){
			self::$spriteGenDone=true;$cssImgs=array(); $spritename='img-sprite.png';
			$compiledCssFolder=new Folder($enhanced->getAppDir().'tmp/compiledcss/prod/');
			//
			foreach($compiledCssFolder->listFiles() as $file){
				$fileContent=file_get_contents($file->getPath());
				$matches=array();
				if(preg_match_all('/background(\-image)?\s*:\s*([^ ]+)?\s*url\(([^)]+)\)/U',$fileContent,$matches)){
					foreach($matches[3] as $i=>$url){
						$url=trim($url,' \'');
						if(substr($url,0,8)==='COREIMG/'){
							$cssImgs[]=$url;
						}else{
							if((!empty($matches[2][$i]) && trim($matches[2][$i])==='transparent') || substr($url,0,7) !== '../img/' || substr($url,-4)==='.gif' || $url=='../img/'.$spritename
										|| substr($url,0,7+8) ==='../img/fancybox' || substr($url,0,7+6) ==='../img/mobile'
										|| substr($url,0,7+8) === '../img/filetree' || substr($url,0,7+9) === '../img/jquery-ui') continue;
							$cssImgs[]=substr($url,7);
						}
					}
				}
			}
			$cssImgs=array_unique($cssImgs);
			
			if(!empty($cssImgs)){
				$imgDir=$prod->getPath().'web/img/';
				/*foreach($cssImgs as $imgKey=>&$cssImg)
					if(!file_exists($imgDir.$cssImg)){
						throw new Exception('Img does NOT exist : '.$cssImg);
						unset($cssImgs[$imgKey]);
					}
				*/
				
				include_once CORE.'enhancers/CssSpriteGen.php';
				$cssSpriteGen=new CssSpriteGen();
				$cssRules=$cssSpriteGen->CreateSprite($imgDir,$cssImgs,$spritename);
				/*if(file_exists($imgDir.$spritename)) */copy($imgDir.$spritename,$dev->getPath().'web/img/'.$spritename);
				copy($imgDir.$spritename,$enhanced->getAppDir().'src/web/img/'.$spritename);
				//debug($cssRules);
				
				/* background: background-color background-image background-repeat background-attachment background-position
				 * It does not matter if one of the property values is missing, as long as the ones that are present are in this order. 
				 */
				foreach(array(new Folder($prod->getPath().'web/css'),new Folder($dev->getPath().'web/css')) as $cssFolder){
					foreach($cssFolder->listFiles() as $file){
						$content=$file->read();
						$content=preg_replace_callback('/background(\-image)?\s*:\s*([^ ]+)?\s*url\(([^)]+)\)([^;}{]*[;|}])?/U',function(&$matches) use(&$cssRules,&$spritename){
							$url=trim($matches[3],' \'"');
							if(substr($url,0,8)==='COREIMG/'){
								$key=$url;
							}else{
								if((!empty($matches[2]) && trim($matches[2])==='transparent') || substr($url,0,7) !== '../img/' || substr($url,-4)==='.gif' || substr($url,0,7+8) ==='../img/fancybox' || substr($url,0,7+6) ==='../img/mobile'
										|| substr($url,0,7+8) === '../img/filetree' || substr($url,0,7+9) === '../img/jquery-ui'
										|| $url==='../img/'.$spritename)
									return 'background'.$matches[1].':'.(empty($matches[2])?' ':$matches[2].' ').'url(\''.$url.'\')'.(empty($matches[4])?'':$matches[4]);
								$key=substr($url,7);
							}
							if(!isset($cssRules[$key]))
								return 'background'.$matches[1].':'.(empty($matches[2])?' ':$matches[2].' ').'url(\''.$url.'\')'.(empty($matches[4])?'':$matches[4]);
							$val=$cssRules[$key];
							
							return 'background:'.(empty($matches[2])?' ':$matches[2].' ').'url(\'../img/'.$spritename.'\')'.(empty($matches[4])?' ':rtrim($matches[4],'}').' ').$val['position']
								.(substr($key,0,8)==='actions/'||substr($key,0,6)==='icons/'||substr($url,0,8)==='COREIMG/'?'':';width:'.$val['width'].';height:'.$val['height'])
								.(!empty($matches[4]) && substr($matches[4],-1)==='}'?'}':'');
						},$content);
						//debugCode($content);
						$file->write($content);
					}
				}
			}
		}
	}
}