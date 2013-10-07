<?php
class JsFile extends EnhancerFile{
	//private $_realSrcContent;
	public static $CACHE_PATH='js_8.7.4',$defaultExtension='js';

	public static function init(){
		self::$preprocessor=new Preprocessor('js');
	}
	
	private $devProdDiff,$includes=array(),$_srcContentOldIe;
	public function loadContent($srcContent){
		if(UString::firstLine($srcContent)==="includeCore('springbok.jsapp');"){
			$filename=substr($this->fileName(),0,-3);
			$prefix=$filename==='jsapp'?'':$filename.'/';
			$srcContent="var INCLPREFIX='".$prefix."';includeCore('springbok.jsapp');"
				.'App.jsapp('.json_encode($this->enhanced->appConfig('projectName')).',__SPRINGBOK_COMPILED_TIME__);'
				.('S.router.init(includeJsAppConfig(\''.$prefix.'routes\')'/*.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes.js'),7,-1)*/.','
						.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/'.$prefix.'routes-langs.js'),6,-1).');')
				.substr($srcContent,strpos($srcContent,"\n")+1)
				.'App.run();';
			//debugCode($srcContent);
		}
		
		
		//$this->_realSrcContent=$srcContent;
		$srcContent=self::includes($srcContent,dirname($this->srcFile()->getPath()),$this->enhanced->getAppDir(),$this->includes,$this->enhanced);
		//$srcContent=str_replace('coreDeclareApp();','S.app=new App('.json_encode(self::$APP_CONFIG['projectName']).','.time().');',$srcContent);
		
		$this->devProdDiff= preg_match('~/\*\#if(then)?\s+(\!\s*)?(DEV|PROD)~',$srcContent);
		
		$this->_srcContent=$srcContent;
		//if($this->fileName()==='jsapp.js') debug($srcContent);
	}
	/*
	public function getMd5Content(){
		$md5=$this->_srcContent;
		debugVar($md5);
		if($this->fileName()==='jsapp.js'){
			$md5.=file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes.js')
				.file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes-langs.js');
		}elseif(preg_match('/initSpringbokRoutes\(([^)]+)?\)/',$md5,$m)){
			$suffix=(empty($m[1])?'':'_'.substr($m[1],1,-1));
			$md5.=file_get_contents($this->enhanced->getAppDir().'src/config/routes'.$suffix.'.php')
				.file_get_contents($this->enhanced->getAppDir().'src/config/routes-langs'.$suffix.'.php');
		}
		return $this->md5=md5($md5);
	}*/
	
	public function enhanceContent(){
		$c=$this->_srcContent;
		$appDir=$this->enhanced->getAppDir();
		
		if(substr($this->fileName(),-7,-3)!=='.min'){
			$c=preg_replace_callback('/initSpringbokRoutes\(([^)]+)?\)/',function(&$m) use(&$appDir){
				$suffix=(empty($m[1])?'':'_'.substr($m[1],1,-1));
				return 'S.router.init('.json_encode(include $appDir.'src/config/routes'.$suffix.'.php').','.json_encode(include $appDir.'src/config/routes-langs'.$suffix.'.php').');';
			},$c);
			
			$constantes=array();
			$c=preg_replace_callback('/\bdefineDefault\(\'([0-9\w_-]+)\',\'?([0-9\w\s\._\-\#\,]+)\'?\);/Ui',function($matches) use(&$constantes){
				$constantes[$matches[1]]=$matches[2];
				return '';
			},$c);
			$c=preg_replace_callback('/\bdefine\(\'([0-9\w_-]+)\',\'?([0-9\w\s\._\-\#\,]+)\'?\);/Ui',function($matches) use(&$constantes){
				$constantes[$matches[1]]=$matches[2];
				return '';
			},$c);
			uksort($constantes,function($k1,$k2){return strlen($k1)<strlen($k2);}); // trie les constantes du plus grd au moins grd pour éviter de remplacer des bouts de constantes
			
			$includes=$this->includes;
			$c=preg_replace_callback('/\bincluded(Core|Lib|JsAppConfig|Plugin)?\(\'([\w\s\._\-\/\&\+]+)\'\)/Ui',function($matches) use($includes){
				return isset($includes[$matches[1]][$matches[2]]) ? 'true' : 'false';
			},$c);
			
			foreach($constantes as $const=>$replacement)
				$c=str_replace($const,$replacement,$c);
			$c=$this->preprocessor($c,true);
			
			$c=str_replace('__SPRINGBOK_COMPILED_TIME__',time(),$c);
			
			//if(preg_match('/\'{t(c)? (.*)}\'/',$c,$mI))
			//	debugVar($mI);
			$c=preg_replace('/\'{t(f|c|)\s+([^}]+)\s*}\'/U','i18n$1[\'$2\']',$c);
			//$c=preg_replace('/\'{t(c)? (.*)}\'/U','i18n$1[\'$2\']',$c);
			
			
			if(strpos(dirname($this->srcFile()->getPath()),'app')===false && substr($this->fileName(),0,5)!=='i18n-'){
				/*$after='';
				$c=preg_replace_callback('/\/\*\s+AFTER\s+\*\/(.*)\/\*\s+\/AFTER\s+\*\//Ums',function($m) use(&$after){$after.=$m[1]; return '';},$c);
				*/
				$this->_srcContent="(function(window,document,Object,Array,Math,undefined){".$c.'})(window,document,Object,Array,Math);'/*.$after*/;
			}else $this->_srcContent=$c;
			
			$jsFiles=array('global.js','index.js','jsapp.js');

			if(!empty($this->enhanced->config['entries'])) foreach(($entries=$this->enhanced->config['entries']) as $entry) $jsFiles[]=$entry.'.js';
			else $entries=array();
			if(in_array($this->fileName(),$jsFiles) || $this->fileName()==='dev.js')
				$this->_srcContent="var baseUrl='".(defined('BASE_URL')?BASE_URL:'')
					.(rtrim($this->enhanced->devConfig['siteUrl']['index'],'/')==='http://localhost' && in_array(substr($this->fileName(),0,-3),$entries)?'/'.substr($this->fileName(),0,-3):'')."/'"
					/*",baseUrl=baseUrl".($this->fileName()==='admin.js'?'admin/':'').*/
					.",staticUrl=baseUrl+'web/',webUrl=staticUrl+'./',imgUrl=webUrl+'img/'"
					.($this->fileName()==='admin.js'?',entryUrl='.json_encode($this->enhanced->devConfig['siteUrl'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):'')
					.";\n".$this->_srcContent;
			
			$srcContentNonIe=str_replace('OLD_IE','false',$this->_srcContent);
			if($srcContentNonIe!==$this->_srcContent){
				$this->_srcContentOldIe=str_replace('OLD_IE','true',$this->_srcContent);
				$this->_srcContent=$srcContentNonIe;
			}
			
		}
	}
	
	public function getEnhancedDevContent(){
		$content=$this->_srcContent;
		//if($this->fileName()==='jsapp.js')
		//	$content.='S.app='.json_encode(array('name'=>self::$APP_CONFIG['projectName'],'version'=>time()));
		//if($this->fileName()=='global.js')
		//	return 'function include(fileName){document.write(\'<script type="text/javascript" src="\'+jsdir+fileName+\'.js"></script>\');var notifier = new EventNotifier();setTimeout(notifier,100);notifier.wait->();}'.$this->_realSrcContent;
		//return $this->_realSrcContent;
		
		return $content;
	}
	
	public function writeDevFile($devFile){
		if(substr($this->fileName(),-7,-3)==='.min' || basename(dirname($devFile->getPath()))==='ace') $devFile->write($this->_srcContent);
		else{
			$content=$this->_srcContent; $contentOldIe=$this->_srcContentOldIe;
			
			if($this->devProdDiff){
				foreach(array('content','contentOldIe') as $varName){
					if(empty($$varName)) continue;
					$$varName=$this->preprocessor_devprod($$varName,true);
				}
			}
			
			if(substr($this->fileName(),0,7)==='tinymce') self::executeCompressor($this->enhanced->getTmpDir(),$content,$devFile->getPath(),true);
			else{
				self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$devFile->getPath(),true);
				if(!empty($contentOldIe)) self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$contentOldIe,substr($devFile->getPath(),0,-3).'.oldIe.js',false,true);
				else copy($devFile->getPath(),substr($devFile->getPath(),0,-3).'.oldIe.js');
			}
			
			
			
			//self::executeCompressor($this->enhanced->getTmpDir(),$content,$devFile->getPath(),true);
			//self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$devFile->getPath().'_googleclosure.js');
			//self::uglify($content,$devFile->getPath().'_uglify.js');
		}
		return true;
	}
	public function writeProdFile($prodFile){
		//if(in_array($this->fileName(),array('global.js','mobile.js','admin.js','jsapp.js')))
		//	$this->_srcContent="var baseUrl='/',webdir=baseUrl+'web/',imgdir=webdir+'img/',jsdir=webdir+'js/';\n".$this->_srcContent;
		//$jsFiles=array('global.js','jsapp.js');
		/*if(!empty($this->config['entries'])) foreach(($entries=$this->config['entries']) as $entry) $jsFiles[]=$entry.'.js';
		else $entries=array();
		if(in_array($this->fileName(),$jsFiles)){
			$this->_srcContent="(function(window,document,Object,Array,Math,undefined){window.baseUrl='".(defined('BASE_URL')?str_replace('/dev/','/prod/',BASE_URL.'/'):'/')
				.(in_array(substr($this->fileName(),0,-3),$entries)
							&&(!isset($this->enhanced->devConfig['dev_prefixed_routes'])||$this->enhanced->devConfig['dev_prefixed_routes']!==false)
								?substr($this->fileName(),0,-3).'/':'')."'"
				.substr($this->_srcContent,strpos($this->_srcContent,';',28));
		}*/
		//if($this->fileName()==='jsapp.js')
		//	$this->_srcContent.='S.app='.json_encode(array('name'=>self::$APP_CONFIG['projectName'],'version'=>time()));
		
		//$this->_srcContent=preg_replace('/\/\*\!?\s+[^\(?:\*\/)]*\s+\*\//mU','',$this->_srcContent);
		if(substr($this->fileName(),-7,-3)==='.min' || basename(dirname($prodFile->getPath()))==='ace') $prodFile->write($this->_srcContent);
		else{
			if($this->devProdDiff){
				$content=$this->_srcContent; $contentOldIe=$this->_srcContentOldIe;
				
				foreach(array('content','contentOldIe') as $varName){
					if(empty($$varName)) continue;
					$$varName=$this->preprocessor_devprod($$varName,false);
				}
				
				if(substr($this->fileName(),0,7)==='tinymce') self::executeCompressor($this->enhanced->getTmpDir(),$content,$prodFile->getPath(),true);
				else{
					self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$prodFile->getPath());
					if(!empty($contentOldIe)) self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$contentOldIe,substr($prodFile->getPath(),0,-3).'.oldIe.js',false,false);
					else copy($prodFile->getPath(),substr($prodFile->getPath(),0,-3).'.oldIe.js');
				}
			}else{
				copy($this->getDevFile()->getPath(),$prodFile->getPath());
				copy($this->getDevFile()->getPath(),substr($prodFile->getPath(),0,-3).'.oldIe.js');
			}
			
			//self::executeCompressor($this->enhanced->getTmpDir(),$content,$prodFile->getPath());
			//self::executeGoogleCompressor($content,$prodFile->getPath().'_googleclosure.js');
			//self::uglify($content,$prodFile->getPath().'_uglify.js');
		}
		return true;
	}

	protected function copyFromCache($cachefile,$devFile,$prodFile,$justDev){
		if(file_exists($cachefile.'_src')) copy($cachefile.'_src',substr($devFile->getPath(),0,-3).'.src.js');
		if(file_exists($cachefile.'_map')) copy($cachefile.'_map',$devFile->getPath().'.map');
		if(file_exists($cachefile.'_oldIe')){
			foreach($justDev?array($devFile):array($devFile,$prodFile) as $destFile)
				copy($cachefile.'_oldIe',substr(is_string($destFile)?$destFile:$destFile->getPath(),0,-3).'.oldIe.js');
		}
		parent::copyFromCache($cachefile,$devFile,$prodFile,$justDev);
	}
	
	protected function copyDevToCache($devFile,$cachefile){
		parent::copyDevToCache($devFile,$cachefile);
		$srcFile=substr($devFile->getPath(),0,-3).'.src.js';
		if(file_exists($srcFile)) copy($srcFile,$cachefile.'_src');
		else UFile::rm($cachefile.'_src');
		$srcFile=substr($devFile->getPath(),0,-3).'.oldIe.js';
		if(file_exists($srcFile)) copy($srcFile,$cachefile.'_oldIe');
		else UFile::rm($cachefile.'_oldIe');
		$srcFile=$devFile->getPath().'.map';
		if(file_exists($srcFile)) copy($srcFile,$cachefile.'_map');
		else UFile::rm($cachefile.'_map');
	}
	
	public static function executeCompressor($tmpDir,$content,$destination,$nomunge=false){
		$dest=$destination?$destination:tempnam($tmpDir,'yuidest');
		$javaExecutable = 'java';
		$jarFile=CLIBS.'_yuicompressor-2.4.7.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --type js'./*($nomunge?' --nomunge':'').*/' --line-break 8000 -o '.escapeshellarg($dest);
		$tmpfname = tempnam($tmpDir,'yui');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		//debugVar('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1',$destination,$nomunge,$tmpfname);
		if(!empty($res)){
			debugCode($destination."\n".$res,false);
			if(preg_match('/\[ERROR\]\s+([0-9]+)\:([0-9]+)/',$res,$m)){
				prettyDebug(HText::highlightLine($content,null,(int)$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')),false);
			}else h($content);
		}
		unlink($tmpfname);
		chmod($dest,0777);
		if(!$destination){
			$destination=file_get_contents($dest);
			unlink($dest);
			return $destination;
		}
	}
	
	public static function executeGoogleCompressor($tmpDir,$enhancer,&$content,$destination,$createSourceMap=false,$oldIE=false){
		$dest=$destination?$destination:tempnam($tmpDir,'gclosuredest');
		$javaExecutable = 'java';
		$jarFile=CORE.'libs/ClosureCompiler/compiler.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --compilation_level SIMPLE_OPTIMIZATIONS --language_in=ECMASCRIPT5_STRICT'
					//.' --jscomp_error undefinedVars'
					//--jscomp_off
					.' --js_output_file '.escapeshellarg($dest).' ';
		if($createSourceMap){
			$rawSrcFile=substr($destination,0,-3).'.src.js';
			$cmd.='--create_source_map '.escapeshellarg($destination.'.map').' --source_map_format=V3';
		}else $rawSrcFile=tempnam($tmpDir,'gclosure');
		//$content='/*jshint asi:true boss:true eqnull:true es5:true evil:true funcscope:true globalstrict:true lastsemic:true laxbreak:true shadow:true sub:true */'."\n".$content;
		//$content='/** @define {boolean} */ var OLD_IE = false;'.$content;
		file_put_contents($rawSrcFile,$content);
		//debug($rawSrcFile.' :'."\n".shell_exec('jshint '.escapeshellarg($rawSrcFile)));
		
		$res=shell_exec('cd '.escapeshellarg(dirname($rawSrcFile)).' && '.$cmd.' --js '.escapeshellarg(basename($rawSrcFile)).' 2>&1');
		if(!empty($res)){
			if(preg_match('/(?:\:([0-9]+))?:\s+ERROR\s+-\s+(.*)\n(.*)\n/',$res,$m)){
				debugCode($destination."\n".$res,false);
				prettyDebug(HText::highlightLine($content,'js',(int)$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')),false);
				throw new Exception('Error in js file '.$destination);
			}/*else h($content);*/
			//else debugCode($destination."\n".$res,false);
			
			if(preg_match_all('/:\s+WARNING\s+-\s+(.*)\n(.*)\n/',$res,$m)){
				foreach($m[0] as $i=>$abcd) $enhancer->addWarning($destination,array($m[1][$i],$m[2][$i]));
			}
		}
		if($createSourceMap) file_put_contents($destination,'//@ sourceMappingURL='./*(defined('BASE_URL')?BASE_URL.'/web/js/':'').*/basename($destination).'.map',FILE_APPEND);//(defined('BASE_URL')?BASE_URL:'')
		elseif(!$oldIE) unlink($rawSrcFile);
		try{
			chmod($dest,0777);
		}catch(ErrorException $ee){
			throw new Exception('JS compilation failed '.$destination."\nres:\n".$res."\ncontent:\n".$content);
		}
		if(!$destination){
			$destination=file_get_contents($dest);
			unlink($dest);
			return $destination;
		}
	}
	
	public static function uglify(&$content,$dest,$beautify=false){
		$tmpfname = tempnam($this->enhanced->getTmpDir(),'uglify');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && uglifyjs --lift-vars'.($beautify?' -b':'').' -o '.escapeshellarg($dest).' '.escapeshellarg($tmpfname).' 2>&1');
		debugCode($dest."\n".$res,false);
		chmod($dest,0777);
	}
	
	public function getEnhancedProdContent(){}

	public static function includes($content,$currentPath,$appPath,&$includes,&$enhanced){
		$content=preg_replace_callback('/include((?:Js)?Core(?:Utils)?|Lib|JsAppConfig|Plugin)?\(\'([\w\s\._\-\/\&\+]+)\'\)\;?\n?/mi',function($matches) use(&$currentPath,&$appPath,&$includes,&$enhanced){
			if(substr($matches[2],-1)==='/') $matches[2].=basename($matches[2]);
			if(isset($includes[$matches[1]][$matches[2]])) return '';
			$includes[$matches[1]][$matches[2]]=1;
			
			if(empty($matches[1])) $path=$currentPath;
			else if($matches[1]==='JsAppConfig') $path=$appPath.'src/jsapp';
			elseif($matches[1]==='Lib'){
				$libs=dirname(CORE_SRC).'/includes';
				$path=$libs.(file_exists($libs.'/js/'.$matches[2].'.js')?'/js':'');
			}elseif($matches[1]==='Plugin'){
				list($pluginKey,$fileName)=explode('/',$matches[2],2);
				$path=$enhanced->pluginPathFromKey($pluginKey).'web/js';
				$matches[2]=$fileName;
			}elseif($matches[1]==='CoreUtils'){
				$path=CORE_SRC.'includes/js-utils';
			}elseif($matches[1]==='JsCore'){
				$path=CORE_SRC.'includes/springbokjs/src';
			}else $path=CORE_SRC.(file_exists(CORE_SRC.'includes/js/'.$matches[2].'.js')?'includes/js':'includes');
			
			$fileContent=file_get_contents($path.DS.$matches[2].'.js');
			
			return $matches[1]==='JsAppConfig'?substr($fileContent,$start=strpos($fileContent,'=')+1,strrpos($fileContent,';')-$start):JsFile::includes($fileContent,$currentPath,$appPath,$includes,$enhanced)."\n";
		},$content);
		return $content;
	}
}
JsFile::init();
