<?php
class ScssFile extends EnhancerFile{
	public static $CACHE_PATH='scss_8.1';

	public static function init(){
		self::$preprocessor=new Preprocessor('scss');
		ScssFile::findSassPath();
	}

	public function loadContent($srcContent){
		if(!$this->isCore()){
			if(file_exists($filename=dirname($this->srcFile()->getPath()).'/_mixins.scss'))
				$srcContent=file_get_contents($filename).$srcContent;
			$srcContent=file_get_contents(CORE_SRC.'includes/scss/mixins.scss').
						file_get_contents(CORE_SRC.'includes/scss/functions.scss').$srcContent;
		}

		$currentPath=dirname($this->srcFile()->getPath());
		$includes=array('colors'=>false);
		$this->_srcContent=self::includes($srcContent,$currentPath,$includes,$this->enhanced,substr($this->srcFile()->getName(),0,-5));
	}


	public static function &includes($content,$currentPath,&$includes,&$enhanced,$scssFilename){
		$content=preg_replace_callback('/@include(Core|Lib|Plugin)?\s+\'([\w\s\._\-\/]+)\'\;/Ui',function($matches)
																				use($currentPath,&$includes,&$enhanced,$scssFilename){
			$isStylus = endsWith($matches[2],'.styl');
			if(!$isStylus && !endsWith($matches[2],'.css') && !endsWith($matches[2],'.scss')) $matches[2].='.scss';
			if($matches[2]==='base/buttonsOverride.scss') $matches[2]='base/buttons.scss';
			elseif(isset($includes[$matches[1]][$matches[2]])) return '';
			$includes[$matches[1]][$matches[2]]=1;

			$nextContent='';
			/*if(!empty($matches[1]) && $matches[1]==='Core') */;
			if(empty($matches[1])){
				$filename=$currentPath.'/';
				if($includes['colors']===false && $matches[2]==='_colors.scss' || substr($matches[2],-13)==='/_colors.scss'){
					$includes['colors']=true;
					foreach($enhanced->allPluginPaths() as $pluginPath){
						if(file_exists($ppath=($pluginPath.'web/css/'.$scssFilename.'/_colors.scss')))
							$nextContent.=file_get_contents($ppath);
					}
				}
			}else{
				if($matches[1]==='Plugin'){
					list($pluginKey,$fileName)=explode('/',$matches[2],2);
					$filename=$enhanced->pluginPathFromKey($pluginKey).'web/css/';
					$matches[2]=$fileName;
				}else{
					$filename=$matches[1]==='Lib' ? dirname(CORE_SRC).'/' : CORE_SRC;
					$filename.='includes/';

					$folderName=$isStylus ? 'styl' : ($matches[1]==='Lib'?'css/':'scss/');
					if(file_exists($filename.$folderName.$matches[2])) $filename.=$folderName;
				}
			}
			$filename.=$matches[2];

			return ScssFile::includes(file_get_contents($filename).$nextContent,$currentPath,$includes,$enhanced,$scssFilename);
		},$content);
		return $content;
	}


	public function enhanceContent(){
		$this->_srcContent=$this->preprocessor($this->_srcContent);
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
			$this->_srcContent=preg_replace_callback('/'.preg_quote($rule).':\s*([^;]+);/',function($m) use(&$rule,&$copyRules){
				$return='';
				foreach($copyRules as $copyRule) $return.=$copyRule.':'.$m[1].';';
				/*if(in_array($rule,array('border-radius','border-top-right-radius','border-top-left-radius','border-bottom-right-radius',
					'border-bottom-left-radius','box-shadow'))) $return.='@extend .iepie;';*/
				return $return.$m[0];
			},$this->_srcContent);
		}
	}

	public function getEnhancedDevContent(){}
	public function getEnhancedProdContent(){}

	public function writeDevFile($devFile){
		$this->callSass($this->_srcContent,$devFile->getPath(),true);
		if(($appDir=$this->enhanced->getAppDir()) && !$this->isCore()){
			if(!file_exists($appDir.'tmp/compiledcss/')) mkdir($appDir.'tmp/compiledcss/',0755,true);
			$devFile->copyTo($appDir.'tmp/compiledcss/'.$devFile->getName());
		}
		return true;
	}
	public function writeProdFile($prodFile){
		/*$this->callSass($this->getEnhancedProdContent(),$prodFile->getPath());
		if(($appDir=$this->enhanced->getAppDir())){
			if(!file_exists($appDir.'tmp/compiledcss/prod/')) mkdir($appDir.'tmp/compiledcss/prod/',0755);
			$prodFile->copyTo($appDir.'tmp/compiledcss/prod/'.$prodFile->getName());
		}*/
		$this->getDevFile()->copyTo($prodFile->getPath());
		return true;
	}


	protected function copyFromCache($cachefile,$devFile,$prodFile,$justDev){
		parent::copyFromCache($cachefile,$devFile,$prodFile,$justDev);
		$tmpDir=$this->enhanced->getTmpDir();
		if(!file_exists($tmpDir.'compiledcss/')) mkdir($tmpDir.'compiledcss/',0755,true);
		$devFile->copyTo($tmpDir.'compiledcss/'.$devFile->getName());
	}

	private static $sassExecutable='sass';
	public static function findSassPath(){
		if(file_exists('/var/lib/gems/1.8/bin/sass')) self::$sassExecutable='/var/lib/gems/1.8/bin/sass';
		$sassVersion = shell_exec(self::$sassExecutable.' --version');
		if(stripos($sassVersion,'error')) throw new Exception('Error in sass : '.$sassVersion);
		if(!preg_match('/(^|\s+)([0-9]+\.[0-9]+)/',$sassVersion,$sassMatchVersion) || empty($sassMatchVersion[2])) throw new Exception('Unable to find version : '.$sassVersion);
		$sassVersion = (float)$sassMatchVersion[2];
		if($sassVersion < 3.2) throw new Exception('Please update your sass version : sudo gem install sass compass ('.$sassVersion.')');
	}
	public function callSass($content,$destination){
		$dest=$destination?$destination:tempnam($this->enhanced->getTmpDir(),'scssdest');
		$tmpfname = tempnam($this->enhanced->getTmpDir(),'scss');
		$cmd = self::$sassExecutable.' -E "UTF-8" -C --trace --compass --scss -t compressed -r '.escapeshellarg(CORE_SRC.'includes/scss/module.rb')
										.' '.escapeshellarg($tmpfname).' '.escapeshellarg($dest);
		file_put_contents($tmpfname,$content);
		$res=shell_exec('cd '.escapeshellarg(dirname($this->srcFile()->getPath())).' ; '.$cmd.' 2>&1');
		if(!empty($res)){
			$this->throwException("Error in scss conversion to css : ".$this->fileName()."\n".$res);
		}
		unlink($tmpfname);
		chmod($dest,0777);
		$content=file_get_contents($dest);
		$content=preg_replace_callback('/filter\:progid\:DXImageTransform\.Microsoft\.gradient\(startColorstr=(?:\'|\")#([0-9A-F]{3,6})(?:\'|\"),endColorstr=(?:\'|\")#([0-9A-F]{3,6})(?:\'|\")([^)]*)/',
				function($m){return "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#".(strlen($m[1])===3?$m[1].$m[1]:$m[1])."',endColorstr='#".(strlen($m[2])===3?$m[2].$m[2]:$m[2])."'".$m[3]; },$content);
		CssFile::executeCompressor($this->enhanced->getTmpDir(),$content,$dest);

		if(!$destination){
			$destination=file_get_contents($dest);
			unlink($dest);
			return $destination;
		}
	}

	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		CssFile::afterEnhanceApp($enhanced,$dev,$prod);
	}
}
ScssFile::init();
