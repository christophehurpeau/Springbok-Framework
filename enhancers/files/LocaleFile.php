<?php
include dirname(__DIR__).'/Translations.php';
class LocaleFile extends EnhancerFile{
	public static $CACHE_PATH=false,$defaultExtension='yml';
	
	public function enhanceContent(){
		//$dirname=dirname($this->srcFile()->getPath());
		$isModels=substr($this->fileName(),-(7+4))==='_models.yml';
		$isPlugins=substr($this->fileName(),-(8+4))==='_plugins.yml';
		try{
			$yaml=$this->_srcContent;
			if(empty($yaml)) $yaml=array();
			else{
				$yaml=str_replace("\t",'  ',$yaml);
				$yaml=yaml_parse($yaml,/*$isPlugins ? -1 : */0,$nbDocs);
			}
		}catch(ErrorException $e){
			$this->throwException($e->getMessage());
		}
		
		$lang=substr($this->fileName(),0,-4);
		if($isModels) $lang=substr($lang,0,-7);
		elseif($isPlugins) $lang=substr($lang,0,-8);
		
		
		$db=SpringbokTranslations::loadDbLang($this->enhanced->getAppDir().'db/',$lang);
		SpringbokTranslations::checkDb($db);
		$db->beginTransaction();
		
		if($isModels){
			foreach($yaml as $modelName=>$translations){
				foreach($translations as $string=>$translation){
					SpringbokTranslations::saveOneModelField($db,$modelName,$string,$translation);
				}
			}
			
		}elseif($isPlugins){
			foreach($yaml as $pluginName=>$translations){
				foreach($translations as $string=>$translation){
					$string='plugin.'.$pluginName.'.'.$string;
					if(is_array($translation))
						SpringbokTranslations::saveOneSP($db,$string,$translation['one'],$translation['other']);
					else
						SpringbokTranslations::saveOne($db,$string,$translation);
				}
			}
		}else{
			foreach($yaml as $string=>$translation){
				if(is_array($translation))
					SpringbokTranslations::saveOneSP($db,$string,$translation['one'],$translation['other']);
				else
					SpringbokTranslations::saveOne($db,$string,$translation);
			}
		}
		$db->commit();
		$db->close();
	}
	
	public function getEnhancedDevContent(){
		return '';
	}
	
	public function getEnhancedProdContent(){
		return '';
	}
}
