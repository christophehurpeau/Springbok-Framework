<?php
class JobFile extends PhpFile{
	public static $CACHE_PATH='jobs_8.0';
	
	public function enhancePhpContent($phpContent,$false=false){
		self::$_changes=true;
		$matches=array();
		preg_match('/\/\*\*([^{]*)\*\/\s+class ([A-Za-z_]+)Job/',$phpContent,$matches);//debug($matches);
		if(empty($matches[2])) return parent::enhancePhpContent($phpContent);
		$annotations=PhpFile::parseAnnotations($matches[1],false,')');
		$className=$matches[2];
		$val=false;
		if(isset($annotations['Cron'])) $val=$annotations['Cron'][0];
		elseif(isset($annotations['Every'])){
			$val=$annotations['Every'][0]; $matches=array();
			if(preg_match('/^([0-9]+)d$/',$val,$matches))
				$val=$matches[1]==1?'* * 0 * *':('* * */'.(ceil(30/$matches[1])).' * *');
			elseif(preg_match('/^([0-9]+)h$/',$val,$matches))
				$val=$matches[1]==1?'* 0 * * *':('* */'.(ceil(60/$matches[1])).' * * *');
			elseif(preg_match('/^([0-9]+)mi?n$/',$val,$matches))
				$val=$matches[1]==1?'0 * * * *':('*/'.(ceil(60/$matches[1])).' * * * *');/* FAUX */

		}
		//if($val!==NULL)
			self::$_jobsConfig[$className]=$val;
		//else unset(self::$_jobsConfig[$className]);
		self::$_changes=true;
		
		return parent::enhancePhpContent($phpContent);
	}
	
	private static $_jobsConfig,$_changes=false;
	public static function initFolder($folder,$config){
		$f=new File($folder->getPath().'config/jobs.php');
		if($f->exists()){
			//$f->moveTo($tmpFolder.'jobs.php');
			self::$_jobsConfig=include $f->getPath();
		}else self::$_jobsConfig=array();
	}
	public static function fileDeleted($file){
		self::$_changes=true;
		$jobName=substr($file->getName(),0,-4);
		unset(self::$_jobsConfig[$jobName]);
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		if(self::$_changes){
			$content='<?php return '.UPhp::exportCode(self::$_jobsConfig).';';
			file_put_contents($dev->getPath().'config/jobs.php',$content);
			file_put_contents($prod->getPath().'config/jobs.php',$content);
		}/*elseif($hasOldDef){
			$f=new File($tmpDev.'jobs.php'); if(!$f->exists())return; $f->moveTo($dev->getPath().'config'.DS.'jobs.php');
			$f=new File($tmpProd.'jobs.php'); $f->moveTo($prod->getPath().'config'.DS.'jobs.php');
		}*/
	}
}
