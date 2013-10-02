<?php
/**
 * Behavior Lang (build)
 * 
 * Create a new Model with Lang suffix and translatable fields
 */
class BLang_build extends SBehaviorBuilder{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		if(!isset($annotations['Translatable'])) throw new Exception('You must use @Translatable too');
		
		$contentClassAnnotations='@TableAlias(\''.$annotations['TableAlias'][0][0].'l\')';
		foreach(array('Seo','Slug','UniqueSlug','IndexSlug',
				'Normalized','UniqueNormalized',
				'TextContent') as $a)
			if(isset($annotations[$a])) $contentClassAnnotations.=' '.UPhp::toAnnotation($a,$annotations[$a]);
		
		
		$content=''; $useTraits=false;
		
		foreach(array('BSeo','BSlug','BNormalized','BTextContent') as $traitName){
			if(isset($modelFile->_traits[$traitName])){
				if($useTraits===false){
					$useTraits=true;
					$content.="\n\tuse ";
				}
				$content.=$traitName.',';
			}
		}
		$content=substr($content,0,-1).';'."\n";
		
		
		
		
		$content.='public 
		/** @Pk @SqlType(\'CHAR(2)\') @NotNull */ $lang,';
		$pkFields=array();
		
		foreach($modelFile->_fields as $fieldName=>$fieldAnnotations){
			if(isset($fieldAnnotations['Pk'])){
				$fas='';
				foreach($fieldAnnotations as $k=>$v) $fas.=' '.UPhp::toAnnotation($k,$v);
				$content.="\n\t".'/** '.$fas.' */ $'.$fieldName.',';
				$pkFields[]=$fieldName;
			}elseif(isset($fieldAnnotations['Translatable'])){
				$fa=$fieldAnnotations; unset($fa['Translatable']);
				$fas='';
				foreach($fa as $k=>$v) $fas.=' '.UPhp::toAnnotation($k,$v);
				$content.="\n\t".'/** '.$fas.' */ $'.$fieldName.',';
			}
			
		}
		
		$contentClassAnnotations.=' @Index(\''.implode("','",$pkFields).'\')';
		
		$content=substr($content,0,-1).';';
		
		
		self::createModel($modelFile,$modelFile->_className.'Lang',$content,$contentClassAnnotations);
	}

	public static function afterBuild($modelFile){
		foreach($modelFile->_fields as $fieldName=>$fieldAnnotations){
			if(isset($fieldAnnotations['Translatable']))
				unset($modelFile->_fields[$fieldName]);
		}
	}
}