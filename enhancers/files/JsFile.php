<?php
class JsFile extends EnhancerFile{
	//private $_realSrcContent;

	public function loadContent($srcContent){
		if($this->fileName()==='jsapp.js'){
			$srcContent="includeCore('springbok.jsapp');"
				.'S.app.jsapp('.json_encode($this->enhanced->appConfig('projectName')).','.time().');' // force également à toujours refaire le fichier
				.('S.router.init('.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes.js'),7,-1).','
						.substr(file_get_contents($this->enhanced->getAppDir().'src/jsapp/routes-langs.js'),6,-1).');')
				.$srcContent
				.'S.app.run();';
			//debugCode($srcContent);
		}
		
		
		//$this->_realSrcContent=$srcContent;
		$includes=array();
		$srcContent=self::includes($srcContent,dirname($this->srcFile()->getPath()),$includes);
		//$srcContent=str_replace('coreDeclareApp();','S.app=new App('.json_encode(self::$APP_CONFIG['projectName']).','.time().');',$srcContent);
		
		$this->_srcContent=$srcContent;
		//if($this->fileName()==='jsapp.js') debug($srcContent);
		$jsFiles=array('global.js','jsapp.js');
		if(!empty($this->enhanced->config['entrances'])) foreach(($entrances=$this->enhanced->config['entrances']) as $entrance) $jsFiles[]=$entrance.'.js';
		else $entrances=array();
		if(in_array($this->fileName(),$jsFiles))
			$this->_srcContent="var basedir='".(defined('BASE_URL')?BASE_URL:'').(in_array(substr($this->fileName(),0,-3),$entrances)?'/'.substr($this->fileName(),0,-3):'')."/'"
				./*",baseurl=basedir".($this->fileName()==='admin.js'?'admin/':'').*/",webdir=basedir+'web/',staticUrl=webdir,imgdir=webdir+'img/',jsdir=webdir+'js/';\n".$this->_srcContent;
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
		return md5($md5);
	}*/
	
	public function enhanceContent(){
		$c=$this->_srcContent;
		$appDir=$this->enhanced->getAppDir();
		
		$c=preg_replace_callback('/initSpringbokRoutes\(([^)]+)?\)/',function(&$m) use(&$appDir){
			$suffix=(empty($m[1])?'':'_'.substr($m[1],1,-1));
			return 'S.router.init('.json_encode(include $appDir.'src/config/routes'.$suffix.'.php').','.json_encode(include $appDir.'src/config/routes-langs'.$suffix.'.php').');';
		},$c);
		
		$this->_srcContent=$c;
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
			$content=preg_replace('/\/\*\s+PROD\s+\*\/.*\/\*\s+\/PROD\s+\*\//Ums','',$this->_srcContent);
			$content=str_replace('/* DEV */','',str_replace('/* /DEV */','',$content));
			
			self::executeCompressor($this->_srcContent,$devFile->getPath(),true);
			//self::executeGoogleCompressor($this->_srcContent,$devFile->getPath().'_googleclosure.js');
			//self::uglify($this->_srcContent,$devFile->getPath().'_uglify.js');
		}
	}
	public function writeProdFile($prodFile){
		//if(in_array($this->fileName(),array('global.js','mobile.js','admin.js','jsapp.js')))
		//	$this->_srcContent="var basedir='/',webdir=basedir+'web/',imgdir=webdir+'img/',jsdir=webdir+'js/';\n".$this->_srcContent;
		$jsFiles=array('global.js','jsapp.js');
		if(!empty($this->config['entrances'])) foreach(($entrances=$this->config['entrances']) as $entrance) $jsFiles[]=$entrance.'.js';
		else $entrances=array();
		if(in_array($this->fileName(),$jsFiles)){
			$this->_srcContent="var basedir='".(defined('BASE_URL')?str_replace('/dev/','/prod/',BASE_URL.'/'):'/')
				.(in_array(substr($this->fileName(),0,-3),$entrances)?substr($this->fileName(),0,-3).'/':'')."'"
				.substr($this->_srcContent,strpos($this->_srcContent,','));
		}
		//if($this->fileName()==='jsapp.js')
		//	$this->_srcContent.='S.app='.json_encode(array('name'=>self::$APP_CONFIG['projectName'],'version'=>time()));
		
		//$this->_srcContent=preg_replace('/\/\*\!?\s+[^\(?:\*\/)]*\s+\*\//mU','',$this->_srcContent);
		if(substr($this->fileName(),-7,-3)==='.min' || basename(dirname($prodFile->getPath()))==='ace') $prodFile->write($this->_srcContent);
		else{
			$content=preg_replace('/\/\*\s+DEV\s+\*\/.*\/\*\s+\/DEV\s+\*\//Ums','',$this->_srcContent);
			$content=str_replace('/* PROD */','',str_replace('/* /PROD */','',$content));
			
			self::executeCompressor($this->_srcContent,$prodFile->getPath());
			//self::executeGoogleCompressor($this->_srcContent,$prodFile->getPath().'_googleclosure.js');
			//self::uglify($this->_srcContent,$prodFile->getPath().'_uglify.js');
		}
	}
	
	public static function executeCompressor(&$content,$destination,$nomunge=false){
		$dest=$destination?$destination:tempnam('/tmp','yuidest');
		$javaExecutable = 'java';
		$jarFile=CLIBS.'_yuicompressor-2.4.7.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --type js'.($nomunge?' --nomunge':'').' --line-break 8000 -o '.escapeshellarg($dest);
		$tmpfname = tempnam('/tmp','yui');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		if(!empty($res)){
			debugCode($destination."\n".$res,false);
			if(preg_match('/\[ERROR\]\s+([0-9]+)\:([0-9]+)/',$res,$m)){
				prettyDebug(HText::highlightLine($content,null,$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')),false);
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
	
	public static function executeGoogleCompressor(&$content,$destination){
		$dest=$destination?$destination:tempnam('/tmp','gclosuredest');
		$javaExecutable = 'java';
		$jarFile=CLIBS.'ClosureCompiler/_gclosure.jar';
		$cmd = $javaExecutable.' -jar '.escapeshellarg($jarFile).' --language_in=ECMASCRIPT5_STRICT --js_output_file '.escapeshellarg($dest).' --js ';
		$tmpfname = tempnam('/tmp','gclosure');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && '.$cmd.' '.escapeshellarg($tmpfname).' 2>&1');
		if(!empty($res)){
			debugCode($destination."\n".$res,false);
			if(preg_match('/\[ERROR\]\s+([0-9]+)\:([0-9]+)/',$res,$m)){
				prettyDebug(HText::highlightLine($content,null,$m[1],false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;')),false);
			}/*else h($content);*/
		}
		//unlink($tmpfname);
		chmod($dest,0777);
		if(!$destination){
			$destination=file_get_contents($dest);
			unlink($dest);
			return $destination;
		}
	}
	
	public static function uglify(&$content,$dest,$beautify=false){
		$tmpfname = tempnam('/tmp','uglify');
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd / && uglifyjs --lift-vars'.($beautify?' -b':'').' -o '.escapeshellarg($dest).' '.escapeshellarg($tmpfname).' 2>&1');
		debugCode($dest."\n".$res,false);
		chmod($dest,0777);
	}
	
	public function getEnhancedProdContent(){}

	public static function includes($content,$currentPath,&$includes){
		$content=preg_replace_callback('/include(Core|Lib)?\(\'([\w\s\._\-\/\&\+]+)\'\)\;?\n?/mi',function($matches) use(&$currentPath,&$includes){
			if(isset($includes[$matches[1]][$matches[2]])) return '';
			$includes[$matches[1]][$matches[2]]=1;
			return JsFile::includes(file_get_contents(
					(empty($matches[1])?$currentPath:($matches[1]==='Lib'?dirname(CORE).(file_exists(dirname(CORE).'/includes/js/'.$matches[2].'.js')?'/includes/js':'/includes')
							:CORE.(file_exists(CORE.'includes/js/'.$matches[2].'.js')?'includes/js':'includes')))
				.DS.$matches[2].'.js'),$currentPath,$includes);
		},$content);
		return $content;
	}
}
