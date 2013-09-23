<?php
class CssFile extends EnhancerFile{
	public static $CACHE_PATH='css_8.0.1';
	
	private $_devSrcContent,$_config=array('compress'=>true,'autoBrowsersCompatibility'=>true);

	public function setConfig($name,$val){$this->_config[$name]=$val;}
	
	public function loadContent($srcContent){
		if(!$this->isCore()){
			if(file_exists($filename=dirname($this->srcFile()->getPath()).DS.'_functions.css'))
				$srcContent=file_get_contents($filename).$srcContent;
			$srcContent=file_get_contents(CORE_SRC.'includes/_functions.css').$srcContent;
		}elseif($this->fileName()!='_functions.css' && $this->fileName()!='_default_const.css')
			$srcContent=file_get_contents(CORE_SRC.'includes/_functions.css').$srcContent;
		
		$currentPath=dirname($this->srcFile()->getPath());
		$this->_devSrcContent=self::includes($srcContent,$currentPath,true);
		$this->_srcContent=self::includes($this->_devSrcContent,$currentPath,false);
		$this->_srcContent=self::includes($this->_srcContent,$currentPath,true);
		$this->_srcContent=self::includes($this->_srcContent,$currentPath,false);
	}

	public function writeDevFile($devFile){
		if($this->_config['compress']) self::executeCompressor($this->enhanced->getTmpDir(),$this->getEnhancedDevContent(),$devFile->getPath(),true);
		else $devFile->write($this->getEnhancedDevContent());
		if(($appDir=$this->enhanced->getAppDir()) && !$this->isCore()){
			if(!file_exists($appDir.'tmp/compiledcss/dev/')) mkdir($appDir.'tmp/compiledcss/dev/',0755,true);
			$devFile->copyTo($appDir.'tmp/compiledcss/dev/'.$devFile->getName());
		}
		return true;
	}
	public function writeProdFile($prodFile){
		if($this->_config['compress']) self::executeCompressor($this->enhanced->getTmpDir(),$this->getEnhancedProdContent(),$prodFile->getPath());
		else $prodFile->write($this->getEnhancedProdContent());
		if(($appDir=$this->enhanced->getAppDir())){
			if(!file_exists($appDir.'tmp/compiledcss/prod/')) mkdir($appDir.'tmp/compiledcss/prod/',0755);
			$prodFile->copyTo($appDir.'tmp/compiledcss/prod/'.$prodFile->getName());
		}
		return true;
	}
	
	public static function executeCompressor($tmpDir,$content,$destination,$nomunge=false){
		$dest=$destination?$destination:tempnam($tmpDir,'yuidest');
		$javaExecutable = 'java';
		$jarFile=CORE_SRC.'libs/yuicompressor-2.4.8.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --type css'.($nomunge?' --nomunge':'').' --line-break 8000 -o '.escapeshellarg($dest);
		$tmpfname = tempnam($tmpDir,'yui');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		if(!empty($res)){
			debugVar($res);
			if(preg_match('/^\s*\[ERROR\]\s+([0-9]+)\:([0-9]+)\:/',$res,$m)){
				prettyDebug(HText::highlightLine($content,null,$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')));
			}
			throw new Exception('Error while executing css compressor');
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
////				$content=preg_replace('/\s*({.*)\s*:\s*(.*})\s*/m','$1:$2',$content);

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
		$content=$this->browsersSupport($content);
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
			'VARIABLE_AUTO_CONTENT_PADDING'=>'true',
			
			'IE6_VARIABLE_WIDTH'=>'700px',
			
			'PAGE_SHADOWED_BOX_BACKGROUND'=>'#FFF',
			'PAGE_SHADOWED_BOX_BORDER'=>'false',
			'PAGE_SHADOWED_BOX_SHADOW_COLOR'=>'#888',
			
			'BREADCRUMBS_IN_VARIABLE'=>'false',
			'BREADCRUMBS_BACKGROUND'=>'false',
			'BREADCRUMBS_FONTSIZE'=>'8pt',
			
			// Interactions
			'INTERACTION_DEFAULT_TEXT_SHADOW'=>'false',
			'INTERACTION_DEFAULT_BACKGROUND_COLOR'=>'false',
			'INTERACTION_HOVER_BACKGROUND_COLOR'=>'false',
			'INTERACTION_CURRENT_BACKGROUND_COLOR'=>'false',
			
			
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
			
			/* types.css */
			'TYPES_BLOCK_BORDERRADIUS'=>'false'
		);
		for($i=1;$i<=10;$i++){
			$constantes['TYPE'.$i.'_COLOR']='false';
			$constantes['TYPE'.$i.'_BACKGROUND']='false';
			$constantes['TYPE'.$i.'_BACKGROUND_TB']='false';
			$constantes['TYPE'.$i.'_BORDER']='false';
			
			$constantes['TYPE'.$i.'_HOVER_BACKGROUND']='false';
			$constantes['TYPE'.$i.'_HOVER_BACKGROUND_TB']='false';
			$constantes['TYPE'.$i.'_HOVER_COLOR']='false';
			$constantes['TYPE'.$i.'_HOVER_BORDER']='false';
			
			$constantes['TYPE'.$i.'_FOCUS_BACKGROUND']='false';
			$constantes['TYPE'.$i.'_FOCUS_BACKGROUND_TB']='false';
			$constantes['TYPE'.$i.'_FOCUS_COLOR']='false';
			$constantes['TYPE'.$i.'_FOCUS_BORDER']='false';
			
			$constantes['TYPE'.$i.'_BLOCK_BACKGROUND']='false';
			$constantes['TYPE'.$i.'_BLOCK_BACKGROUND_TB']='false';
			$constantes['TYPE'.$i.'_BLOCK_COLOR']='false';
			$constantes['TYPE'.$i.'_BLOCK_BORDER']='false';
			$constantes['TYPE'.$i.'_BLOCK_BORDERRADIUS']='false';
			
			$constantes['TYPE'.$i.'_BUTTON_COLOR']='false';
			$constantes['TYPE'.$i.'_BUTTON_BACKGROUND']='false';
			$constantes['TYPE'.$i.'_BUTTON_BACKGROUND_TB']='false';
			$constantes['TYPE'.$i.'_BUTTON_BORDER']='false';
			$constantes['TYPE'.$i.'_BUTTON_TEXTSHADOW']='false';
			$constantes['TYPE'.$i.'_BUTTON_BOXSHADOW']='false';
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
	
	public function browsersSupport($content){
		if(!$this->_config['autoBrowsersCompatibility']) return $content;
		$rules=array(
			'transition'=>array('-moz-transition','-webkit-transition','-o-transition'),
			'border-radius'=>array('-moz-border-radius','-webkit-border-radius','-ms-border-radius'),
			'border-top-right-radius'=>array('-moz-border-radius-topright','-webkit-border-top-right-radius'),
			'border-top-left-radius'=>array('-moz-border-radius-topleft','-webkit-border-top-left-radius'),
			'border-bottom-right-radius'=>array('-moz-border-radius-bottomright','-webkit-border-bottom-right-radius'),
			'border-bottom-left-radius'=>array('-moz-border-radius-bottomleft','-webkit-border-bottom-left-radius'),
			'box-shadow'=>array('-moz-box-shadow','-webkit-box-shadow'),
			'box-sizing'=>array('-moz-box-sizing','-webkit-box-sizing','-ms-box-sizing'),
			'appearance'=>array('-moz-appearance','-webkit-appearance'),
			'backface-visibility'=>array('-moz-backface-visibility','-webkit-backface-visibility')
		);
		foreach($rules as $rule=>$copyRules){
			$content=preg_replace_callback('/'.preg_quote($rule).':\s*([^;]+);/',function(&$m) use(&$rule,&$copyRules){
				$return='';
				foreach($copyRules as $copyRule) $return.=$copyRule.':'.$m[1].';';
				if(in_array($rule,array('border-radius','border-top-right-radius','border-top-left-radius','border-bottom-right-radius',
					'border-bottom-left-radius','box-shadow'))) $return.='behavior:url(/web/css/PIE.htc);';
				return $return.$m[0];
			},$content);
		}
		return $content;
	}
	
	public function &recursive_selectors($content){
		$content=preg_replace_callback('/([^{};\/]+){((?:[^{}]+(?:{[^}]*}\s*)?)*)}/',function($matches){
			$selectors=trim($matches[1]);
			if($selectors[0]==='@') return $matches[0];
			$foundInternalSelectors=array();$selector=explode(',',$selectors);
			$internalContent=preg_replace_callback('/(;|}|\n)([^{;}\/@]+){([^}]*)}/',function($matches) use(&$foundInternalSelectors,&$selector){
				$internalSelector=explode(',',trim($matches[2]));
				$finalInternalSelector=array();
				foreach($selector as $selctr){ $selctr=trim($selctr);
					foreach($internalSelector as $iselctr){
						$finalInternalSelector[]=$selctr.' '.trim($iselctr);
					}
				}
				$finalInternalSelector=implode(',',$finalInternalSelector);
				$foundInternalSelectors[]=$finalInternalSelector.'{'.$matches[3].'}';
				return $matches[1];
			},$matches[2]);
			return $matches[1].'{'.rtrim($internalContent,'; ').'}'.implode(PHP_EOL,$foundInternalSelectors);
		},$content);
		return $content;
	}
	
	
	private static $spriteGenDone=NULL;
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		//debugVar($enhanced->hasChanges('Css'),$enhanced->hasChanges('Img'),$enhanced->hasChanges('Scss'));
		if(self::$spriteGenDone===NULL/* && ($enhanced->hasChanges('Css') || $enhanced->hasChanges('Img') || $enhanced->hasChanges('Scss'))*/){
			self::$spriteGenDone=true;
			$compiledCssFolder=($tmpDir=$enhanced->getTmpDir()).'compiledcss/';//prod/';
			if(!file_exists($cacheFolder=$tmpDir.'sprites_8.0.4/')) mkdir($cacheFolder,0775);
			if(!file_exists($tmpFolder=$tmpDir.'imgssprites_8.0.4/')) mkdir($tmpFolder,0755,true);
			
			$logger=$enhanced->getLogger();
			
			$cssProdFolder=new Folder($prod->getPath().'web/css/');
			$imgDir=$enhanced->getAppDir().'src/web/img/';
			
			foreach($cssProdFolder->listFiles() as $prodFile){
				if(substr($prodFile->getName(),-4)!=='.css') continue;
				
				$cssImgs=array(); $spritename=substr($prodFile->getName(),0,-4).'.png';
				$logger->log('ImgSprite: '.$spritename);
				$fileContent=file_get_contents($compiledCssFolder.$prodFile->getName());
				$matches=$md5CssImgs=array();
				if(preg_match_all('/background(\-image)?\s*:\s*([^ ]+)?\s*url\(([^)]+)\)/U',$fileContent,$matches)){
					foreach($matches[3] as $i=>$url){
						$url=trim($url,' \'"');
						if(substr($url,0,8)==='COREIMG/'){
							$cssImgs[]=$url;
							$md5CssImgs[$url]=$url;
						}else{
							if((!empty($matches[2][$i]) && ($trimMatches2=trim($matches[2][$i])) && ($trimMatches2==='transparent' || (strlen($trimMatches2)===7) && $trimMatches2[0]==='#'))
										|| substr($url,-4) === '.jpg'
										|| substr($url,0,7) !== '../img/' || substr($url,-4)==='.gif' || $url=='../img/'.$spritename
										|| substr($url,0,7+8) ==='../img/fancybox' || substr($url,0,7+6) ==='../img/mobile'
										|| substr($url,0,7+8) === '../img/filetree' || substr($url,0,7+6) === '../img/jquery') continue;
							$cssImgs[]=$imgPath=substr($url,7);
							if(!isset($md5CssImgs[$imgPath])) $md5CssImgs[$imgPath]=md5_file($imgDir.$imgPath);
						}
					}
				}


				if(!empty($cssImgs)){
					$cssImgs=array_unique($cssImgs);
					ksort($md5CssImgs);
					$md5CssImgs=md5(implode('#',array_keys($md5CssImgs)).'###'.implode('#',$md5CssImgs));
					
					$spritePath=$enhanced->getAppDir().'src/web/sprites/'.$spritename;
					
					if(file_exists($cacheFolder.$md5CssImgs) && file_exists($cacheFolder.$md5CssImgs.'_imgs')){
						$spritePath=$cacheFolder.$md5CssImgs;
						$cssRules=json_decode(file_get_contents($cacheFolder.$md5CssImgs.'_imgs'),true);
					}else{
						include_once CORE.'enhancers/CssSpriteGen.php';
						$cssSpriteGen=new CssSpriteGen($tmpFolder);
						$cssRules=$cssSpriteGen->CreateSprite($imgDir,$cssImgs,$cacheFolder.$md5CssImgs);
						copy($cacheFolder.$md5CssImgs,$spritePath);
						file_put_contents($cacheFolder.$md5CssImgs.'_imgs',json_encode($cssRules));
					}
					/*if(file_exists($imgDir.$spritename)) */copy($spritePath,$dev->getPath().'web/sprites/'.$spritename);
					copy($spritePath,$prod->getPath().'web/sprites/'.$spritename);
					//debug($cssRules);
					
					/* background: background-color background-image background-repeat background-attachment background-position
					 * It does not matter if one of the property values is missing, as long as the ones that are present are in this order. 
					 */
					$content=preg_replace_callback('/background(\-image)?\s*:\s*([^ ]+)?\s*url\(([^)]+)\)([^;}{]*[;|}])?/U',function(&$matches) use(&$cssRules,&$spritename){
						$url=trim($matches[3],' \'"');
						if(substr($url,0,8)==='COREIMG/'){
							$key=$url;
						}else{
							if((!empty($matches[2]) && ($trimMatches2=trim($matches[2])) && ($trimMatches2==='transparent' || $trimMatches2==='/**/' || (strlen($trimMatches2)===7) && $trimMatches2[0]==='#'))
									|| substr($url,-4) === '.jpg'
									|| substr($url,0,7) !== '../img/' || substr($url,-4)==='.gif' || substr($url,0,7+8) ==='../img/fancybox' || substr($url,0,7+6) ==='../img/mobile'
									|| substr($url,0,7+8) === '../img/filetree' || substr($url,0,7+6) === '../img/jquery'
									|| $url==='../sprites/'.$spritename){
								return 'background'.$matches[1].':'.(empty($matches[2])?' ':$matches[2].' ').'url(\''.$url.'\')'.(empty($matches[4])?'':$matches[4]);
							}
							$key=substr($url,7);
						}
						if(!isset($cssRules[$key])){
							throw new Exception('Sprite creator : Css Rule not found for : '.$url."\nCss Rules:\n".print_r($cssRules,true));
							return 'background'.$matches[1].':'.(empty($matches[2])?' ':$matches[2].' ').'url(\''.$url.'\')'.(empty($matches[4])?'':$matches[4]);
						}
						$val=$cssRules[$key];
						
						return 'background:'.(empty($matches[2])?' ':$matches[2].' ').'url(\'../sprites/'.$spritename.'\')'.(empty($matches[4])?' ':rtrim($matches[4],'}').' ').$val['position']
							.(substr($key,0,8)==='actions/'||substr($key,0,6)==='icons/'||substr($url,0,8)==='COREIMG/'?'':';width:'.$val['width'].';height:'.$val['height'])
							.(!empty($matches[4]) && substr($matches[4],-1)==='}'?'}':'');
					},$fileContent);
					//debugCode($content);
					
					foreach(array($prodFile,new File($dev->getPath().'web/css/'.$prodFile->getName())) as $file){
						$file->write($content);
					}
				}
			}
			
			/*if(file_exists($enhanced->getAppDir().'imgSprite_md5') && file_exists($prod->getPath().'web/img/img-sprite.png')){
				$md5=file_get_contents($enhanced->getAppDir().'imgSprite_md5');
				if($md5===$md5CssImgs) return;
			}
			file_put_contents($enhanced->getAppDir().'imgSprite_md5',$md5CssImgs);
			*/
			
				
				/*foreach($cssImgs as $imgKey=>&$cssImg)
					if(!file_exists($imgDir.$cssImg)){
						throw new Exception('Img does NOT exist : '.$cssImg);
						unset($cssImgs[$imgKey]);
					}
				*/
				
				
				/* background: background-color background-image background-repeat background-attachment background-position
				 * It does not matter if one of the property values is missing, as long as the ones that are present are in this order. 
				 *//*
				foreach(array(new Folder($prod->getPath().'web/css'),new Folder($dev->getPath().'web/css')) as $cssFolder){
					foreach($cssFolder->listFiles() as $file){
						
					}
				}
			}*/
		}
	}
}