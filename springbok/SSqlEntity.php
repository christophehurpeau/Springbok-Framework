<?php
/**
 * An Entity is a SSqlModel contained in a single-letter named folder
 */
class SSqlEntity extends SSqlModel{
	public static function init($modelName){
		$modelName::$__modelDb=DB::init(static::$__dbName);
		self::$__loadedModels[]=$modelName;
		$modelName::$__modelInfos=include APP.'models/infos/'.$modelName;
		$modelName::$_relations=&$modelName::$__modelInfos['relations'];
		$modelName::$__PROP_DEF=&$modelName::$__modelInfos['props'];
	}
}