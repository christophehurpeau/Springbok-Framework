<?php
class PhpFile extends EnhancerFile{
	public static $CACHE_PATH=false,$defaultExtension='php';
	protected $_devContent,$_prodContent;
		/** [0:'className', 1:'params', path:'path', content:'content'] */
	public $_traits;
	
	
	public static function init(){
		self::$preprocessor=new Preprocessor('js');
	}
	
	public static function regexpFunction($name='#'){
		return '/(?:public|private|protected)\s+(?:static\s+)?function\s+('.($name==='#'?'[a-zA-Z_]+':preg_quote($name)).')\s*\((.*)\)\s*{'
																		.'\s*(.*)\s*\n(?:\t|\040{2}|\040{4})}\n/Us';
	}
	
	public static function regexpArrayField($name='#'){
		return '/\s*public\s*(?:static)?\s*\$'.($name==='#'?'[a-zA-Z_]+':preg_quote($name)).'\s*=\s*((?:array\(.*\)|\[.*\]);)/Us';
	}
	
	protected function loadContent($srcContent){
		if($this->isCore() && !$this->isInLibDir()){
			$currentPath=dirname($this->srcFile()->getPath());
			$finalSrcContent=''; $require=false;
			$tokens=token_get_all($srcContent);
			foreach($tokens as $token){
				if(is_array($token)){
					list($tn,$string)=$token;
					switch($tn){
						case T_REQUIRE:
							$require='';
							//echo '<pre>'.htmlspecialchars(print_r($tokens,true),ENT_QUOTES,'UTF-8',true).'</pre>';
							break;
						case T_CONSTANT_ENCAPSED_STRING:
							if($require !== false) $require=$string;
							else $finalSrcContent.=$string;
							break;
						default:
							$finalSrcContent.=$string;
					}
				}else{
					if($token!==';' || $require === false) $finalSrcContent.=$token;
					elseif(!empty($require)){
						//echo '<pre>'.htmlspecialchars(print_r($finalSrcContent,true),ENT_QUOTES,'UTF-8',true).'</pre>';exit;
						$include_content=file_get_contents($currentPath.DS.trim($require,'"\''));
						//$this->loadContent($include_content); // internal require
						//$include_content=$this->_srcContent;
						$include_content=EnhancerFile::removeWS_B_E($include_content);
						if(substr($include_content,0,5)=='<?php') $include_content=substr($include_content,5);
						if(substr($include_content,-2)=='?>') $include_content=substr($include_content,0,-2);
						$finalSrcContent.=$include_content;
						$require=false;
					}
				}
			}
			$this->_srcContent=$finalSrcContent;
		}else $this->_srcContent=$srcContent;
	}

	public function getMd5Content(){
		$srcContent=$this->_srcContent;
		
		if(!$this->isCore()){
			$this->findAllTraits($srcContent);
			
			if(!empty($this->_traits)){
				foreach($this->_traits as &$trait){
					$trait['path']=$path=$this->findTraitPath($trait[0]);
					if(!file_exists($path)) $this->throwException('Trait "'.$trait[0].'" in '.$this->fileName().' does not exists ('.$path.')');
					
					$srcContent.=$trait['content']=file_get_contents($path);
					$srcContent.=$trait['content_build']=UFile::getContents(substr($path,0,-4).'_build.php');
				}
			}
		}
		
		return $this->md5=(md5($srcContent).$this->enhanced->md5EnhanceConfig());
	}

	public function checkContent($content){
		/*parsekit_compile_string($content,$errors);
		if(!empty($errors)) throw new Exception(print_r($errors,true));*/
		//token_get_all($content);
	}

	public function findAllTraits($srcContent,$check=false){
		if($check){ $oldTraits=$this->_traits; $this->_traits=array(); }
		$tokens=token_get_all($this->_srcContent);
			
		/* http://stackoverflow.com/questions/9895502/easiest-way-to-detect-remove-unused-use-statements-from-php-codebase */
		$useStatements = array();
		$level = 0; $useNumber=0;
		
		foreach($tokens as $key => $token) {
			if(is_string($token)){
				if ($token === '{') $level++;
				elseif ($token === '}') $level--;
			}elseif($token[0] == T_USE && $level === 1){
				$i = $key; $useStatements[$useNumber] = '';
				$stopChar=';';
				
				do{
					++$i;
					if(is_array($tokens[$i])){
						list($tn,$string)=$tokens[$i];
						if($tn == T_COMMENT || $tn == T_DOC_COMMENT) continue;
					}else $string=$tokens[$i];
					if ($string == '(') {
						unset($useStatements[$useNumber]);
						goto endTokenUse;
					}elseif($string==='{') $stopChar='}';
					//elseif($char==='}') $stopChar=';';
					
					$useStatements[$useNumber] .= $string;
				}while(/*$stopChar===true || */$string != $stopChar);
				$useNumber++;
			}
			endTokenUse:
		}
		
		foreach($useStatements as $fullStmt) {
			$fullStmt = rtrim($fullStmt, ';');
			$fullStmt = explode(',', $fullStmt);
			foreach($fullStmt as $singleStmt){
				$trait=array_map('trim',explode('{',$singleStmt,2));
				$this->_traits[$trait[0]] = $trait;
				if($check){
					foreach(array('path','content','content_build') as $key)
						$this->_traits[$trait[0]][$key]=$oldTraits[$trait[0]][$key];
				}
			}
		}
		
	}

	protected function findTraitPath($traitName){ return Springbok::findPath($traitName); }

	protected function loadTraits(){
		throw new Exception;
		foreach($this->_traits as $trait)
			if(!trait_exists($trait[0],false)) include $trait['path'];
	}
	
	public function enhanceContent(){
		/*$content=preg_replace_callback('/(<\?php[ |\n](?:.*)(?:[ |\n])?\?>)/Ums', array($this,'enhancePhpContent'.$suffix),$content);
		$content=preg_replace_callback('/(<\?php[ |\n](?:.*))$/ms', array($this,'enhancePhpContent'.$suffix),$content);
		//ini_set('memory_limit', '512M');*/
		$this->_srcContent=$this->preprocessor($this->_srcContent);
		if(empty($this->_srcContent) || trim($this->_srcContent)==='<?php'){
			$this->_devContent=$this->_prodContent=false;
			return;
		}
		if(!$this->isCore()) $this->findAllTraits($this->_srcContent,true);
		
		
		$tokens=token_get_all($this->_srcContent); $isPhp=false; $phpContent=$finalDevContent=$finalProdContent=$finalContent=''; 
		foreach($tokens as $token){
			if(is_array($token)){
				list($tn,$string)=$token;
				//$tname = token_name($tn);
				
				switch($tn){
					case T_OPEN_TAG:
					case T_OPEN_TAG_WITH_ECHO:
						$isPhp=true;
						$phpContent=$string;
						break;
					case T_CLOSE_TAG:
						$isPhp=false;
						$this->_phpContent=$this->enhancePhpContent($phpContent.$string);
						$finalDevContent.=$finalContent.$this->getEnhancedDevPhpContent();
						$finalProdContent.=$finalContent.$this->getEnhancedProdPhpContent();
						$phpContent=$finalContent='';
						break;
					default: $isPhp? $phpContent.=$string : $finalContent.=$string;
				}
			}else $isPhp? $phpContent.=$token : $finalContent.=$token;
		}
		if($isPhp){
			$this->_phpContent=$this->enhancePhpContent($phpContent);
			$finalDevContent.=$finalContent.$this->getEnhancedDevPhpContent();
			$finalProdContent.=$finalContent.$this->getEnhancedProdPhpContent();
		}else{
			$finalDevContent.=$finalContent;
			$finalProdContent.=$finalContent;
		}
		$this->_devContent=$this->enhanceFinalContent($finalDevContent);
		$this->_prodContent=$this->enhanceFinalContent($finalProdContent);
	}

	public function enhanceFinalContent($finalContent){
		/* $finalContent=preg_replace_callback('/(;)?\s*\?><\?php\s* /',function(&$matches){return empty($matches[1])?';':'';},$finalContent); */
		//$finalContent=$this->optimiseFinalContentPlace($finalContent);
		return $finalContent;
	}
	
	public function getEnhancedDevContent(){
		$content=$this->_devContent;
		if($content===false) return false;
		
		if((!$this->isCore() || ($this->fileName()!=='base.php' && $this->fileName()!=='springbok.php' && substr($this->srcFile()->getPath(),0,strlen(CORE_SRC.'includes'.DS))!==CORE_SRC.'includes'.DS))){
			//([\s+|=|\.|\(|\|\||\&\&])
			$content=preg_replace('/\bpreg_filter\(/','dev_preg_filter(',$content);
			$content=preg_replace('/\bpreg_grep\(/','dev_preg_grep(',$content);
			$content=preg_replace('/\bpreg_match_all\(/','dev_preg_match_all(',$content);
			$content=preg_replace('/\bpreg_match\(/','dev_preg_match(',$content);
			$content=preg_replace('/\bpreg_replace\(/','dev_preg_replace(',$content);
			$content=preg_replace('/\bpreg_replace_callback\(/','dev_preg_replace_callback(',$content);
			$content=preg_replace('/\bpreg_split\(/','dev_preg_split(',$content);
			$content=preg_replace('/\eval\(/','dev_eval(',$content);
		}
		return $content;
	}
	public function getEnhancedProdContent(){
		if($this->_prodContent===false) return false;
		$content=EnhancerFile::removeWS_B_E($this->_prodContent);

		$content=preg_replace_callback('/<pre(.*)<\/pre>/Us',function(&$matches){
			return '<pre'.preg_replace_callback('/(\n)(\t+)/',function(&$matches){return $matches[1].'PHP_FILE_ENHANCER_REPLACE_TO_NOTHING'.str_repeat('PHP_FILE_ENHANCER_REPLACE_TO_TAB',strlen($matches[2]));},$matches[1]).'</pre>';
		},$content);
		// indentation tabs to nothing
		$content=preg_replace('/\n\t+/',PHP_EOL,$content);
		
		// reduce non-newline whitespaces to one
		$content=preg_replace('/[ \f\t]+/',' ',$content);

		// newlines (preceded by any whitespace) to a newline
		$content=preg_replace('/\s*\n+/',PHP_EOL,$content);
		
		$content=str_replace('PHP_FILE_ENHANCER_REPLACE_TO_NOTHING','',$content);
		$content=str_replace('PHP_FILE_ENHANCER_REPLACE_TO_TAB',"\t",$content);
		
		$content=preg_replace('/<\?php\s+\?>/','',$content);
		
		return $content;
	}
	
	
	protected $_phpContent;
	public function enhancePhpContent($phpContent,$overrideClassAnnotations=false){
		/* $phpContent=preg_replace_callback('/(?:\/\*\*([^{};]*)\*\/\s+)?class ([A-Za-z_]+)([^{]*)?{/ms',function($matches) use(&$overrideClassAnnotations){
			/*if($overrideClassAnnotations!==false) $annotations=$overrideClassAnnotations;
			else $annotations=PhpFile::parseAnnotations($matches[1]);/*
				$annotations=array();
				if(!empty($matches[1])){$amatches=array();
					preg_match_all('/[\*\s]*[\s]+@([A-Za-z0-9_]+)(?:\(([^\)]*)\))?/ms',$matches[1],$amatches);
					//matches : 1:annotationName, 2:args
					foreach($amatches[1] as $key=>$pname)
						$annotations[]="'".$amatches[1][$key]."'=>".(empty($amatches[2][$key]) ? 'false': "array(".$amatches[2][$key].")");
				}
			}*/
			//$annotations=empty($annotations)?'':'protected static $_classAnnotations='.UPhp::exportCode($annotations).';';
			/*return 'class '.$matches[2].(empty($matches[3])?'':$matches[3]).'{'/*.$annotations;
		},$phpContent);*/
		
		// add properties
		if(!$this->isCore()){
			$matches=array();
			preg_match('/class ([A-Za-z_]+)(?:[^{]*){((?:[^{]*)(?:{[^{]*})?(?:[^{]*))\n}/',$phpContent,$matches);
			if(!empty($matches[1])){
				$properties='';
	
				$pmatches=array();
				preg_match_all('/\/\*\*(.*)\*\/.*[(public)|(private)|(protected)][\s]+\$([A-Za-z0-9\s_]+)([\s]*=[^;]*[\s]*)?;/Ums',$matches[2],$pmatches);
				foreach($pmatches[2] as $key=>$pname){
					$ptype=array();
					if(preg_match('/[\s]*@var[\s]*([a-zA-Z0-9\[\]]+)[\s]*/',$pmatches[1][$key],$ptype)){
						$ptype=$ptype[1];
						if(!empty($ptype)){
							$properties.="'".$pname."'=>array('type'=>'".$ptype."'";
							$annotations=self::parseAnnotations($pmatches[1][$key]);
							if(!empty($annotations)) $properties.=",'annotations'=>".PhpFile::var_export($annotations);
							$properties.="),";
						}
					}
				}
				
				if(!empty($properties)) $phpContent=preg_replace('/class ([A-Za-z\s_]+)([^{]*){(.+)/ms','class $1$2{public static $__PROP_DEF=array('.rtrim($properties,',').');$3',$phpContent);
			}
		}
		
		$phpContent=preg_replace_callback('/\/\*\s+EVAL\s+(.*)\s+\/EVAL\s+\*\/(\\\'\\\'|0)/Us',
			function($matches){$val='';eval('$val='.$matches[1].';');if($val==='') exit(print_r($matches,true));return UPhp::exportCode($val);}
			,$phpContent);
		$phpContent=preg_replace_callback('/\/\*\s+EVAL\s+(.*)\s+\/EVAL\s+\*\//Us',
			function($matches){$val='';eval('$val='.$matches[1].';');if($val==='') exit(print_r($matches,true));return UPhp::exportCode($val);}
			,$phpContent);
		$phpContent=preg_replace('/return !new [^;]+;/','',$phpContent);
		$phpContent=preg_replace('/\bSF\:\:onlyOnce\(\)\;/','',$phpContent);
		
		// short methods
		$phpContent=preg_replace('/notFoundIfFalse\(([^)]+)\);/','if($1===false)notFound();',$phpContent);
		$phpContent=preg_replace('/(=|;|\s|\(|\)|,)e\(([^,]+),(array\([^)]*\)|[^)]+)\)/','$1(empty($2)?$3:$2)',$phpContent);
		
		//autoexecute
		$phpContent=$this->addExecuteToQueries($phpContent);
		
		$phpContent=EnhancerFile::removeWS_B_E($phpContent);
		
		// Tabs to nothing (usally used for indentation)
		$phpContent=preg_replace('/\t/','',$phpContent);

		// reduce non-newline whitespaces to one
		$phpContent=preg_replace('/[ \f]+/',' ',$phpContent);

		// newlines (preceded by any whitespace) to a newline
		$phpContent=preg_replace('/\s*\n+/m',PHP_EOL,$phpContent);

		// remove WS and at end of file
		/* $content=preg_replace('/\s*(\?>)?\s*$/','',$content); */
		// Strip // comments
		//$content=preg_replace('/\/\/(.*)?\n/','',$content);
		// Strip /* */ comments
		//$content=preg_replace('/\/\*[\s\S]*?\*\//m','',$content);
		
		return $phpContent;
	}

	public function addExecuteToQueries(&$phpContent,$isModelFile=false){
/*		if(!$this->isCore()) debugCode($phpContent);
*/		$recursifPattern='[^()]*(?:\([^()]+\)[^()]*)*';
		$i=5;while($i-- > 0) $recursifPattern=str_replace('[^()]+',$recursifPattern,$recursifPattern);//echo $recursifPattern;
		$phpFile=&$this;
		$newPhpContent=preg_replace_callback('/((?:\$([^={}\(\)]+)\s*=\s*!?\s*|\s+(?:self::)?set(?:ForLayout)?\([^,]+,|\=\>|if\(!?|&&\s*!?|\|\|\s*!?|\s*\?|'
									.'foreach\(|implode\(\'[^\']+\',|json_encode\(|renderJSON\(|return|else|;|}|\:\:mToArray\(|((?:(?:CTable(?:One)?|CPagination[^\:]*)\:\:create|\->query)\(\s*)?\n)\s*'
				.($isModelFile?'(?:self|parent|(?:[A-Z][a-z][A-Za-z0-9_]*|E[A-Z]{2}[a-z][A-Za-z0-9_]*|\$[A-Za-z0-9_]+))':'(?:[A-Z][a-z][A-Za-z0-9_]*|E[A-Z]{2}[a-z][A-Za-z0-9_]*|\$[A-Za-z0-9_]+)')
				.'\:\:(?:ById|ByIdAndStatus|ByIdAndType|QCount|QDeleteAll|QDeleteOne|QExist|QAll|QListAll|QListName|QListRows|QList|QOne|QValue|QValues|QInsert|QInsertSelect|QLoadData|QReplace|QUnion|QUpdate|QUpdateOne|QUpdateOneField|QRows|QRow)'
											.str_replace('\([^()]*\)','\([^()]*(?:\([^()]*\)[^()]*)*\)','\([^()]*(?:\([^()]*\)[^()]*)*\)')//.'\([^()]*(?:\([^()]*(?:\([^()]+\)[^()]*)*\)[^()]*)*\)'
				/* .'(?:\s*\-\>[^\(\)]+\('.(/*(?:(?>[^()]*)|(?R))**//*$recursifPattern).'\))+\s*' */
				//.'(?:\s*\->([^()]+)\(([^()]*(?3)[^()]*)*\))*'
				.'(?:\s*(?:\/[\/|\*]\s*)?\->([^()]+)\(('.$recursifPattern.')\)(?:\s*\/\*)?)*'
				/* .'(?:\s*\->([^()]+)\('.(/*(?:(?>[^()]*)|(?R))**//*'(?:[^()]*(?:\((?:[^()]*(?:\((?:[^()]*(?:\((?:[^()]*(?:\((?:[^()]*(?:\([^()]*\))?)*\))?)*\))?)*\))?)*\))?)*').'\))*\s*'*/
				.')/',function($matches) use(&$phpFile,&$isModelFile){
/*			echo '<div style="text-align:left;background:#FFDDAA;color:#333;border:1px solid #E07308;overflow:auto;padding:1px 2px;">';
			echo '<pre style="text-align:left;margin:0;overflow:auto;font-size:9pt">'.print_r($matches,true).'=>'.($matches[2]=='query'||$matches[3]=='callback'?$matches[1].$matches[4]:$matches[1].'->execute()'.$matches[4]).'</pre>';
			echo '</div></div><br />';
			ob_flush();
*/	
//debug($matches);
			$matches[1]=preg_replace_callback('/->fields\([\'\"]([a-zA-Z0-9\_\,]+)[\'\"]\)/',
					function($mF){return '->setFields('.UPhp::exportCode(explode(',',$mF[1])).')';},$matches[1]);
			
			if(!empty($matches[4])){
				//if($matches[4]=='execute' && $this->enhanced->getMinCoreVersion() < 4)
				//	$phpFile->addWarning('double execute : '.$phpFile->srcFile()->getPath().' ==> '.$matches[1]);
				if($matches[4]=='callback'||$matches[4]=='forEach') return str_replace($matches[5],$phpFile->addExecuteToQueries($matches[5],$isModelFile),$matches[1]);
			}
			if((!empty($matches[2]) && substr($matches[2],0,5)=='query')||
						(!empty($matches[4]) && ($matches[4]=='_execute_'||$matches[4]=='execute'||$matches[4]=='fetch'||$matches[4]=='refetch'||$matches[4]=='mustFetch'
										||$matches[4]==='forEachModel'||$matches[4]==='forEachValue'||$matches[4]==='forEachRow'
										||$matches[4]==='toArray'||$matches[4]==='notFoundIfFalse'/*||$matches[4]==='paginate'*/)) || (!empty($matches[3]))) return $matches[1];
			if($this->enhanced->getMinCoreVersion() >= 4)
				$phpFile->addWarning('missing fetch() or execute() on your query !'."\n".$matches[1]);
			return $matches[1].'->_execute_()';
		},$phpContent);
/*		if($newPhpContent===NULL) echo "NULL !!!";
		if(!$this->isCore()) echo '<br /><br /><br /><br /><br />';
			ob_flush();
*/		return $newPhpContent===NULL ? $phpContent : $newPhpContent;
	}

	private function afterEnhancePhpContent($content){
		$content=self::compress_php_src(self::removeWS_B_E($content));
		$content=$this->optimisePhpPlace($content);
		return $content;
	}
	
	public function getEnhancedDevPhpContent(){
		if($this->isCore() && $this->fileName()==='PhpFile.php') return $this->_phpContent;
		if(preg_match('/\/\*\s+\/?(PROD|DEV)\s+\*\//',$this->_phpContent,$m))
			$this->throwException('Use the new Preprocessor now (found '.$m[1].')');
		$content=$this->preprocessor_devprod($this->_phpContent,true);
		//$content=$this->optimisePhpPlace($content);
		return $this->afterEnhancePhpContent($content);
	}
	public function getEnhancedProdPhpContent(){
		$content=$this->preprocessor_devprod($this->_phpContent,false);
		return $this->afterEnhancePhpContent($content);
	}
	
	public function optimisePhpPlace($phpContent){
		$phpContent=preg_replace('/for\s*\(\s*\;\s*(?:\;\s*)?\)/','while(true)',$phpContent);
		// remove comments
		$phpContent=substr(self::removePhpComments('<?php '.$phpContent),6);
		
		// }\s+} => }}
		$phpContent=preg_replace_callback('/(\s*)((?:\s*})+)(\s*)/',function(&$matches){return $matches[1].preg_replace('/\s+/','',$matches[2]).$matches[3];},$phpContent);
		
		//
		$phpContent=preg_replace('/\s+\->/','->',$phpContent);
		$phpContent=preg_replace('/\'\.\s+\.\'/','',$phpContent);
		//$phpContent=preg_replace('/\s*,\s+/',',',$phpContent);
		
		return $phpContent;
	}

	public function optimiseFinalContentPlace($content){//if(function_exists('debugCode')) debugCode($content);
		$content=preg_replace('/<\?php\s*\?>/','',$content);
		
		$content=self::optimiseEmptyBetweenTwoTags($content);
		
		// <?php if(...): ?\> ...<?php endif; ?\>=> <?php if(...) echo ...; ?\>
		$content=preg_replace_callback('/<\?php\s+(if\(.+\)):\s+\?>([^\n\?]+)<\?php\s+endif;\s+\?>/U',function(&$matches){return '<?php '.$matches[1].' echo '.UPhp::exportString($matches[2]).' ?>';},$content);
		
		//take <?php echo ?\>\s*<?php echo ?\> and merge with space inside
		$content=preg_replace_callback('/(?:<\?php echo [^;=]+;? ?>\s*){2,}/',function(&$matches){
			$matches=$matches[0];
			$matches=preg_replace_callback('/\s*;?\s*\?>(\s+)<\?php\s+echo\s*/',function(&$space){$space=$space[1];return preg_match('/^\n+$/',$space)?'.':'.\''.trim($space,"\n\r").'\'.';},$matches);
			return $matches;
		},$content);
		
		$content=self::optimiseIfEndif($content);
		$content=self::optimiseEmptyBetweenTwoTags($content);
		
		$content=self::optimiseReduceNewLines($content);
		$blockList='div|p|ul|li|table|h1|h2|h3|h4|h5|html|head||meta|body|br|footer|menu';
		$content=preg_replace_callback('/\s*\n+(<\/?(?:'.$blockList.')(?:\s+[^>]+)?>(?:[^<\n]*<\/(?:'.$blockList.')>)?)\n+\s*(<\?php\s*)?/',function(&$m){return $m[1].(empty($m[2])?'':'<?php'.PHP_EOL);},$content);
		
		$content=self::optimiseEmptyBetweenTwoTags($content);
		$content=self::optimiseReduceNewLines($content);
		$content=self::optimiseIfEndif($content);
		
		return $content;
	}

	private static function optimiseReduceNewLines(&$content){ return preg_replace('/[\s;]*\?>\n+<\?php\s*/',';'.PHP_EOL,$content); }
	private static function optimiseEmptyBetweenTwoTags(&$content){ return preg_replace_callback('/(;|:)?\s*\?><\?php\s*/',function(&$matches){return (empty($matches[1])?';':($matches[1]===':'?': ':$matches[1])).PHP_EOL;},$content); }
	
	// if(...): ...; endif; => if(...) ...;
	private static function optimiseIfEndif(&$content){return preg_replace('/<\?php\s+(if\(.+\)):\s*([^;]+;)\s*endif;\s+\?>/U','<?php $1$2 ?>',$content);}

	/*
	public function enhanceContent($content,$suffix){
		$suffix=ucfirst($suffix);
		$content=preg_replace_callback('/(<\?php[ |\n](?:.*)(?:[ |\n])?\?>)/Ums', array($this,'enhancePhpContent'.$suffix),$content);
		$content=preg_replace_callback('/(<\?php[ |\n](?:.*))$/ms', array($this,'enhancePhpContent'.$suffix),$content);
		return $this->afterEnhancePhpContent($content);
	}
	
	private final function enhancePhpContentDev($matches){return $this->enhancePhpContent($matches,false);}
	private final function enhancePhpContentProd($matches){return $this->enhancePhpContent($matches,true);}
	
	private final function enhancePhpContent($matches,$compress){
		$content=isset($matches[1]) ? $matches[1] : $matches[0];
		$content=$this->enhancePhp($content,$compress);
		return $content;!
	}
	*/
	

	


	/* STATIC */
/*
	public static function includes($content,$currentPath){
		$content=preg_replace_callback('/require\s+\'([\w\s\._\-\/]+)\'\;\n?/mi',function($matches) use($currentPath){
			$include_content=file_get_contents($currentPath.DS.$matches[1]);
			$include_content=EnhancerFile::removeWS_B_E($include_content);
			if(substr($include_content,0,5)=='<?php') $include_content=substr($include_content,5);
			if(substr($include_content,-2)=='?>') $include_content=substr($include_content,0,-2);
			return PhpFile::includes($include_content,$currentPath);
		},$content);
		return $content;
	}

*/

	public static function parseAnnotations($content,$multiple=false,$forbiddenChars=NULL,$withVarAnnotation=false){
		if($forbiddenChars===NULL) $forbiddenChars='*@';
		$annotations=array();$matches=array();
		preg_match_all('/[\*\s]*@([A-Za-z0-9\s_]+)(?:\(([^'.preg_quote($forbiddenChars).']*)\))?/ms',$content,$matches);//if(function_exists('debug')) debug($matches);
		foreach($matches[1] as $key=>$pname){
			$pname=trim($pname);
			if(empty($pname)) continue;
			if(substr($pname,0,3)=='var'){
				if($withVarAnnotation!==false) $multiple? $annotations['var'][]=substr($pname,4) : $annotations['var']=substr($pname,4);
				continue;
			}
			eval('$eval='.(!isset($matches[2][$key])||$matches[2][$key]==='' ? 'false': "array(".$matches[2][$key].")").';');
			if(!isset($eval))
				throw new Exception('Error eval : '.$matches[2][$key]);
			$multiple? $annotations[$pname][]=$eval : $annotations[$pname]=$eval;
		}
		return $annotations;
	}
/*
	public static function var_export($var){
		return var_export($var,true);
	}
	
	public static function recursiveArray(&$content,&$array,$start=true){
		if(!is_array($array)) self::addRecusiveArrayVar($content,$array);
		else{
			$content.='array(';
			foreach($array as $key=>$val){
				self::addRecusiveArrayVar($content,$key);
				$content.='=>';
				$content=self::recursiveArray($content,$val,false);
				$content=rtrim($content,',');
				$content.=',';
			}
			$content=rtrim($content,',');
			$content.='),';
		}
		return $start?rtrim($content,','):$content;
	}
	
	public static function addRecusiveArrayVar(&$content,&$var){
		if(is_string($var)) $content.= var_export($var,true);
		elseif(is_bool($var)) $content.= $var ? 'true' : 'false';
		elseif(is_numeric($var)) $content.= $var;
		elseif(is_null($var)) $content.='NULL';
		else die('ERROR - UNKNOWN : '.print_r($var,true));
	}
*/

	/*public static function replace_simple_url($content){
		return preg_replace_callback('/([HHtml\:\:link\|]\(/ms',function($matches){
		},$content);
	}*/

	
	public static function removePhpComments($content){
		$finalContent='';
		$tokens=token_get_all($content);
		foreach($tokens as $token){
			if(is_array($token)){
				list($tn,$string)=$token;
				if($tn == T_COMMENT || $tn == T_DOC_COMMENT) continue;
				$finalContent.=$string;
			}else $finalContent.=$token;
		}
		return $finalContent;
	}
	


	public static function compress_php_src($content){
		set_time_limit(0); ini_set('memory_limit', '512M');
		//return $content;
		
		// Whitespaces left and right from this signs can be ignored
		$IW = array(
			T_CONCAT_EQUAL,			 // .=
			T_DOUBLE_ARROW,			 // =>
			T_BOOLEAN_AND,			  // &&
			T_BOOLEAN_OR,			   // ||
			T_IS_EQUAL,				 // ==
			T_IS_NOT_EQUAL,			 // != or <>
			T_IS_SMALLER_OR_EQUAL,	  // <=
			T_IS_GREATER_OR_EQUAL,	  // >=
			T_INC,					  // ++
			T_DEC,					  // --
			T_PLUS_EQUAL,			   // +=
			T_MINUS_EQUAL,			  // -=
			T_MUL_EQUAL,				// *=
			T_DIV_EQUAL,				// /=
			T_IS_IDENTICAL,			 // ===
			T_IS_NOT_IDENTICAL,		 // !==
			T_DOUBLE_COLON,			 // ::
			T_PAAMAYIM_NEKUDOTAYIM,	 // ::
			T_OBJECT_OPERATOR,		  // ->
			T_DOLLAR_OPEN_CURLY_BRACES, // ${
			T_AND_EQUAL,				// &=
			T_MOD_EQUAL,				// %=
			T_XOR_EQUAL,				// ^=
			T_OR_EQUAL,				 // |=
			T_SL,					   // <<
			T_SR,					   // >>
			T_SL_EQUAL,				 // <<=
			T_SR_EQUAL,				 // >>=
		);
		$tokens = token_get_all($content);

		$new = "";
		$c = sizeof($tokens);
		$ignoreWhitespace=$inHeredoc=false;
		$lastSign = "";
		$openTag = null;
		
		
		for($i = 0; $i < $c; $i++){
			$token = $tokens[$i];
			if(is_array($token)){
				list($tn, $ts) = $token; // tokens: number, string, line
				$tname = token_name($tn);
				if($tn == T_INLINE_HTML){
					$new .= $ts;
					$ignoreWhitespace=false;
				}else{
					if($tn == T_OPEN_TAG){
						if(strpos($ts, " ") || strpos($ts, "\n") || strpos($ts, "\t") || strpos($ts, "\r")) $ts = rtrim($ts);
						$ts .= " ";
						$new .= $ts;
						$openTag = T_OPEN_TAG;
						$ignoreWhitespace = true;
					}elseif($tn == T_OPEN_TAG_WITH_ECHO){
						$new .= $ts;
						$openTag = T_OPEN_TAG_WITH_ECHO;
						$ignoreWhitespace = true;
					}elseif($tn == T_CLOSE_TAG){
						if($openTag == T_OPEN_TAG_WITH_ECHO) $new = rtrim($new, "; ");
						/*elseif(isset($tokens[$i+1]) && is_array($tokens[$i+1]) && $tokens[$i+1][0]==T_OPEN_TAG){
							$i++;
							continue;
						}*/else $ts = " ".$ts;
						$new .= $ts;
						$openTag = null;
						$ignoreWhitespace = false;
					}elseif(in_array($tn, $IW)){
						$new .= $ts;
						$ignoreWhitespace = true;
					}elseif($tn == T_CONSTANT_ENCAPSED_STRING || $tn == T_ENCAPSED_AND_WHITESPACE){
						if(!empty($ts) && $ts[0] == '"') $ts = addcslashes($ts, "\n\t\r");
						$new .= $ts;
						$ignoreWhitespace = true;
					}elseif($tn == T_WHITESPACE){
						$nt = @$tokens[$i+1];
						//if(!$ignoreWhitespace && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) $new .= " ";
						/* ajouté */ $new .= $ts;
						$ignoreWhitespace = false;
					}elseif($tn == T_START_HEREDOC){
						$new .= "<<<S\n";
						$ignoreWhitespace = false;
						$inHeredoc = true; // in HEREDOC
					}elseif($tn == T_END_HEREDOC){
						$new .= "S;";
						$ignoreWhitespace = true;
						$inHeredoc = false; // in HEREDOC
						for($j = $i+1; $j < $c; $j++) {
						if(is_string($tokens[$j]) && $tokens[$j] == ";"){
							$i = $j;
							break;
						}else if($tokens[$j][0] == T_CLOSE_TAG){
							break;
						}
					}
				}elseif($tn == T_COMMENT || $tn == T_DOC_COMMENT){
					$ignoreWhitespace = true;
				}else{
					//if(!$inHeredoc) $ts = strtolower($ts);
					$new .= $ts;
					$ignoreWhitespace = false;
					}
				}
				$lastSign = "";
			}else{
			if(($token != ";" && $token != ":") || $lastSign != $token){
				$new .= $token;
				$lastSign = $token;
			}
			$ignoreWhitespace = true;
			}
		}
		return $new;
	}
}
PhpFile::init();
