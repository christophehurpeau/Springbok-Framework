<?php
class JsFile extends EnhancerFile{
	//private $_realSrcContent;
	public static $CACHE_PATH='js_8.0.6';

	private $devProdDiff;
	public function loadContent($srcContent){
		if($this->fileName()==='jsapp.js'){
			$srcContent="includeCore('springbok.jsapp');"
				.'App.jsapp('.json_encode($this->enhanced->appConfig('projectName')).','.time().');' // force également à toujours refaire le fichier
				.('S.router.init(includeJsAppConfig(\'routes\')'/*.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes.js'),7,-1)*/.','
						.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes-langs.js'),6,-1).');')
				.$srcContent
				.'App.run();';
			//debugCode($srcContent);
		}
		
		
		//$this->_realSrcContent=$srcContent;
		$includes=array();
		$srcContent=self::includes($srcContent,dirname($this->srcFile()->getPath()),$this->enhanced->getAppDir(),$includes,$this->enhanced);
		//$srcContent=str_replace('coreDeclareApp();','S.app=new App('.json_encode(self::$APP_CONFIG['projectName']).','.time().');',$srcContent);
		
		$this->devProdDiff= (strpos($srcContent,'/* DEV */')!==false||strpos($this->_srcContent,'/* PROD */')!==false);
		
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
			
			foreach($constantes as $const=>$replacement)
				$c=str_replace($const,$replacement,$c);
			$c=$this->hardConfig($c);
			
			
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
			
			$jsFiles=array('global.js','jsapp.js');

			if(!empty($this->enhanced->config['entries'])) foreach(($entries=$this->enhanced->config['entries']) as $entry) $jsFiles[]=$entry.'.js';
			else $entries=array();
			if(in_array($this->fileName(),$jsFiles))
				$this->_srcContent="var basedir='".(defined('BASE_URL')?BASE_URL:'').(!$this->enhanced->devConfigExist('dev_prefixed_routes') && in_array(substr($this->fileName(),0,-3),$entries)?'/'.substr($this->fileName(),0,-3):'')."/'"
					./*",baseurl=basedir".($this->fileName()==='admin.js'?'admin/':'').*/",staticUrl=basedir+'web/',webUrl=staticUrl+'./',imgUrl=webUrl+'img/';\n".$this->_srcContent;
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
			$content=$this->_srcContent;
			
			if($this->devProdDiff){
				$content=preg_replace('/\/\*\s+PROD\s+\*\/.*\/\*\s+\/PROD\s+\*\//Ums','',$content);
				$content=str_replace('/* DEV */','',str_replace('/* /DEV */','',$content));
			}
			
			if(substr($this->fileName(),0,7)==='tinymce') self::executeCompressor($this->enhanced->getTmpDir(),$content,$devFile->getPath(),true);
			else self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$devFile->getPath());
			
			
			
			//self::executeCompressor($this->enhanced->getTmpDir(),$content,$devFile->getPath(),true);
			//self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$devFile->getPath().'_googleclosure.js');
			//self::uglify($content,$devFile->getPath().'_uglify.js');
		}
		return true;
	}
	public function writeProdFile($prodFile){
		//if(in_array($this->fileName(),array('global.js','mobile.js','admin.js','jsapp.js')))
		//	$this->_srcContent="var basedir='/',webdir=basedir+'web/',imgdir=webdir+'img/',jsdir=webdir+'js/';\n".$this->_srcContent;
		//$jsFiles=array('global.js','jsapp.js');
		/*if(!empty($this->config['entries'])) foreach(($entries=$this->config['entries']) as $entry) $jsFiles[]=$entry.'.js';
		else $entries=array();
		if(in_array($this->fileName(),$jsFiles)){
			$this->_srcContent="(function(window,document,Object,Array,Math,undefined){window.basedir='".(defined('BASE_URL')?str_replace('/dev/','/prod/',BASE_URL.'/'):'/')
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
				$content=preg_replace('/\/\*\s+DEV\s+\*\/.*\/\*\s+\/DEV\s+\*\//Ums','',$this->_srcContent);
				$content=str_replace('/* PROD */','',str_replace('/* /PROD */','',$content));
				
				
				if(substr($this->fileName(),0,7)==='tinymce') self::executeCompressor($this->enhanced->getTmpDir(),$content,$prodFile->getPath(),true);
				else self::executeGoogleCompressor($this->enhanced->getTmpDir(),$this->enhanced,$content,$prodFile->getPath());
			}else{
				copy($this->getDevFile()->getPath(),$prodFile->getPath());
			}
			
			//self::executeCompressor($this->enhanced->getTmpDir(),$content,$prodFile->getPath());
			//self::executeGoogleCompressor($content,$prodFile->getPath().'_googleclosure.js');
			//self::uglify($content,$prodFile->getPath().'_uglify.js');
		}
		return true;
	}
	
	public static function executeCompressor($tmpDir,&$content,$destination,$nomunge=false){
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
	
	public static function executeGoogleCompressor($tmpDir,$enhancer,&$content,$destination){
		$dest=$destination?$destination:tempnam($tmpDir,'gclosuredest');
		$javaExecutable = 'java';
		$jarFile=CLIBS.'ClosureCompiler/_gclosure.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --compilation_level SIMPLE_OPTIMIZATIONS --language_in=ECMASCRIPT5_STRICT --js_output_file '.escapeshellarg($dest).' --js ';
		$tmpfname = tempnam($tmpDir,'gclosure');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		if(!empty($res)){
			if(preg_match('/:\s+ERROR\s+-\s+(.*)\n(.*)\n/',$res,$m)){
				debugCode($destination."\n".$res,false);
				prettyDebug(HText::highlightLine($content,null,(int)$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')),false);
			}/*else h($content);*/
			
			if(preg_match_all('/:\s+WARNING\s+-\s+(.*)\n(.*)\n/',$res,$m)){
				foreach($m[0] as $i=>$abcd)$enhancer->warnings[]=array($m[1][$i],$m[2][$i]);
			}
		}
		unlink($tmpfname);
		chmod($dest,0777);
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
		$content=preg_replace_callback('/include(Core|Lib|JsAppConfig|Plugin)?\(\'([\w\s\._\-\/\&\+]+)\'\)\;?\n?/mi',function($matches) use(&$currentPath,&$appPath,&$includes,&$enhanced){
			if(isset($includes[$matches[1]][$matches[2]])) return '';
			$includes[$matches[1]][$matches[2]]=1;
			
			if($matches[1]==='JsAppConfig') $path=$appPath.'src/jsapp';
			elseif($matches[1]==='Lib') $path=dirname(CORE).(file_exists(dirname(CORE).'/includes/js/'.$matches[2].'.js')?'/includes/js':'/includes');
			elseif($matches[1]==='Plugin'){
				list($pluginKey,$fileName)=explode('/',$matches[2],2);
				$path=$enhanced->pluginPathFromKey($pluginKey).'web/js';
				$matches[2]=$fileName;
			}else $path=CORE.(file_exists(CORE.'includes/js/'.$matches[2].'.js')?'includes/js':'includes');
			
			$fileContent=file_get_contents((empty($matches[1])?$currentPath:$path).DS.$matches[2].'.js');
			
			return $matches[1]==='JsAppConfig'?substr($fileContent,$start=strpos($fileContent,'=')+1,strrpos($fileContent,';')-$start):JsFile::includes($fileContent,$currentPath,$appPath,$includes,$enhanced);
		},$content);
		return $content;
	}
}
