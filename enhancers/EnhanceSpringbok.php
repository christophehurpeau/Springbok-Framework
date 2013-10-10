<?php
include CORE_SRC.'utils/UFile.php';
include CORE_SRC.'utils/UPhp.php';
include CORE_SRC.'utils/UExec.php';
include CORE_SRC.'utils/UEncoding.php';
include CORE_SRC.'enhancers/AEnhancerFile.php';
include CORE_SRC.'enhancers/Enhanced.php';
include CORE_SRC.'enhancers/EnhancedCore.php';
include CORE_SRC.'enhancers/files/Preprocessor.php';
include CORE_SRC.'enhancers/files/PhpFile.php';
include CORE_SRC.'enhancers/files/CssFile.php';
include CORE_SRC.'enhancers/Translations.php';
if(file_exists(dirname(CORE_SRC).'/prod/db/DB.php')){
	class Config{public static $db=array();}
	include dirname(CORE_SRC).'/prod/db/DB.php';
	include dirname(CORE_SRC).'/prod/db/DBSql.php';
	include dirname(CORE_SRC).'/prod/db/DBSQLite.php';
}

class EnhanceSpringbok{
	private $inLibs;
	protected $enhanced;

	public function process($dirname,$inLibs=false){
		$this->enhanced=new EnhancedCore('core',$dirname);
		set_time_limit(0); ini_set('memory_limit', '512M');

		$this->enhanced->loadFileDef($force=isset($_GET['force']));

		$this->inLibs=$inLibs;

		$dev=new Folder($dirname.'dev');
		if(!$dev->exists()) $dev->mkdir(0775);
		$prod=new Folder($dirname.'prod');
		if(!$prod->exists()) $prod->mkdir(0775);
		$this->recursiveDir(new Folder(rtrim($dirname,'/').'/src/'), $dev, $prod);
	
		$this->enhanced->writeFileDef($force);


		
		if($inLibs===false){
			$langs=new Folder($dirname.'src/i18n/langs'.DS);
			if($langs->exists())
				foreach($langs->listFilesPath() as $filename=>$file){
					//$db=SpringbokTranslations::loadDbLang(substr($filename,0,-3),'');
					//$app=$db->doSelectListValue('SELECT s,t FROM t');
					$app = include $filename;
					echo '<pre>'.yaml_emit($app, YAML_UTF8_ENCODING).'</pre>';
				}
		}
		
		return $this->enhanced->getChanges();
	}

	/**
	 * @param Dir $dir
	 */
	public function recursiveDir(Folder $dir,Folder $devDir,$prodDir){
		$dirs=$dir->listDirs(false);
		foreach(array_diff_key($devDir->listDirs(false),$dirs) as $d) $d->delete();
		if($prodDir!==false) foreach(array_diff_key($prodDir->listDirs(false),$dirs) as $d) $d->delete();

		foreach($dirs as $d){
			$dirname=$d->getName();
			if($dirname[0]==='.' || $d->getPath()==CORE_SRC.'includes/') continue;
			
			$newDevDir=new Folder($devDir->getPath().$dirname); $newDevDir->mkdir(0775);
			if($prodDir===false) $newProdDir=false;
			else{ $newProdDir=new Folder($prodDir->getPath().$dirname); $newProdDir->mkdir(0775); }


			if($d->getPath() === CORE_SRC.'locales/'){
				foreach($d->listFilesPath() as $filepath=>$filename){
					$yaml=file_get_contents($filepath);
					$yaml=str_replace("\t",'  ',$yaml);
					$yaml=yaml_parse($yaml,0,$nbDocs);
					
					/*
					$lang = $file->getName();
					$filenamedb = substr($lang,0,-4).'.db';
					$db=SpringbokTranslations::loadDbLang($devDir->getPath().$filenamedb,$lang);
					SpringbokTranslations::checkDb($db);
					$db->beginTransaction();
					*/
					foreach($yaml as $string=>$translation){
						if(is_array($translation))
						//	SpringbokTranslations::saveOneSP($db,$string,$translation['one'],$translation['other']);
							throw new Exception;
						//	else SpringbokTranslations::saveOne($db,$string,$translation);
					}
					
					/*
					$db->commit();
					$db->close();
					*/
					
					$string = '<?php return '.UPhp::exportCode($yaml).';';
					$filename = 'locales/'.substr($filename,0,-3).'php';
					file_put_contents($devDir->getPath().$filename, $string);
					file_put_contents($prodDir->getPath().$filename, $string);
					
					$string = 'window.i18nc='.json_encode($yaml,JSON_UNESCAPED_UNICODE).';';
					$ffilepath = CORE_SRC.'includes/js/langs/core-'.substr($filename,8	,-3).'js';
					file_put_contents($ffilepath, $string);
				}
				continue;
			}

			if($dirname=='enhancers'||$dirname=='controllers'||$dirname==='tests'/*||$dirname=='includes'*/){
				$this->simpleRecursiveEnhanceFiles($dirname,$d,$newDevDir);
				$this->simpleRecursiveEnhanceFiles($dirname,$d,$newProdDir);
			}/*elseif(){
				$newProdDir=new Folder($prodDir->getPath().$dirname); $newProdDir->delete();
				$this->recursiveDir($d,$newDevDir,false);
			}*/else{
				//$this->enhanceFiles($d,$newDevDir,$newProdDir);
				$this->recursiveDir($d,$newDevDir,$newProdDir);
			}
		}

		$this->enhanceFiles($dir, $devDir,$prodDir);
	}

	private function simpleRecursiveEnhanceFiles($dirname,&$d,&$newDevDir){
		$files=$d->listFiles(false); $change=false;
		foreach(array_diff_key($newDevDir->listFiles(false),$files) as $f) $f->delete();
		foreach($files as $file){
			$filename=$file->getName();
			if(substr($filename,0,1)=='.') continue;
			$srcMD5=md5_file($file->getPath());
			if(!(file_exists($newDevDir->getPath().$filename)
					&& isset($this->enhanced->oldDef['files'][$file->getPath()])
					&& $this->enhanced->oldDef['files'][$file->getPath()]==$srcMD5)){
				$file->copyTo($newDevDir->getPath().$filename);
				$change=true;
				$this->enhanced->newDef['changes'][]=$file->getPath();
			}
			$this->enhanced->newDef['files'][$file->getPath()]=$srcMD5;
		}
		if($change) $this->enhanced->newDef['LAST_CHANGE_IN_ENHANCERS']=time();
		foreach($d->listDirs(false) as $childDir){
			$newChildDevDir=new Folder($newDevDir->getPath().$childDir->getName()); $newChildDevDir->mkdir(0775);
			$this->simpleRecursiveEnhanceFiles($childDir->getName(),$childDir,$newChildDevDir);
		}
	}
	
	private function enhanceFiles(Folder $dir,Folder $devDir,$prodDir,$class='PhpFile'){
		if(substr($dir->getName(),0,1)==='.') return;
		$files=$dir->listFiles(false);

		foreach(array_diff_key($devDir->listFiles(false),$files) as $f) $f->delete();
		if($prodDir!==false) foreach(array_diff_key($prodDir->listFiles(false),$files) as $f) $f->delete();
		
		foreach($files as $file){
			$filename=$file->getName();
			if(substr($filename,0,1)=='.') continue;

			$ext=substr($filename,-4);
			/*if($ext==='.css'){
				if($filename[0]=='_') $justDev=true;
				$class='CssFile';
			}*/
			if($ext!=='.php'/* && !($class==='CssFile' && $filename[0]!=='_')*/){
				$srcMD5=md5_file($file->getPath());
				if(!(file_exists($devDir->getPath().$filename) && ($prodDir===false || $filename[0]==='_' || file_exists($prodDir->getPath().$filename))
						&& isset($this->enhanced->oldDef['files'][$file->getPath()])
						&& $this->enhanced->oldDef['files'][$file->getPath()]==$srcMD5)){
					copy($file->getPath(),$devDir->getPath().$filename);
					if($prodDir!==false && substr($filename,0,1)!=='_') copy($file->getPath(),$prodDir->getPath().$filename);

					$this->enhanced->newDef['changes'][]=$file->getPath();
				}
				$this->enhanced->newDef['files'][$file->getPath()]=$srcMD5;
				continue;
			}
			
			if(substr($filename,0,2)=='__') continue;
			
			$nf=new $class($this->enhanced,$file->getPath(),true,$this->inLibs);
			$srcMD5=$nf->getMd5Content();
			if(($filename === 'springbok.php' && !empty($this->enhanced->newDef['changes']))
				 || !(file_exists($devDir->getPath().$filename) && ($prodDir===false || file_exists($prodDir->getPath().$filename))
					&& isset($this->enhanced->oldDef['files'][$file->getPath()])
					&& $this->enhanced->oldDef['files'][$file->getPath()]==$srcMD5)){
				if($class==='CssFile'){
					$devFile=new File($devDir->getPath().$filename); $prodFile=$prodDir===false?false:new File($prodDir->getPath().$filename);
					$nf->currentDestFile=$prodFile; $this->_isProd=true;
					$nf->enhanceContent();
					$nf->writeProdFile($devFile); if($prodFile!==false) $nf->writeProdFile($prodFile);
				}else{
					$nf->processEhancing($devDir->getPath().$filename,$prodDir===false?false:$prodDir->getPath().$filename);
				}
				if($filename !== 'springbok.php') $this->enhanced->newDef['changes'][]=$file->getPath();
			}
			$this->enhanced->newDef['files'][$file->getPath()]=$srcMD5;
		}
	}
}