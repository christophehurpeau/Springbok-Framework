<?php
/**
 * @deprecated
 */
class DBTrigger{
	public function __construct(&$schemas,$triggers){
		DB::pingAll();
		$iTriggers=array();
		foreach($triggers as $trigger){
			if(!is_array($trigger['modelName'])) $trigger['modelName']=array($trigger['modelName']);
			foreach($trigger['modelName'] as $modelName){
				$triggerName=$modelName.'_'.$trigger['name'];
				$iTriggers[$modelName][$triggerName]=
					array('start'=>'CREATE TRIGGER `'.$triggerName.'` '.$trigger['time'].' '.$trigger['event'].' ON '.$modelName::_fullTableName().' FOR EACH ROW',
						'statement'=>'BEGIN '.$trigger['stmt'].' END');
			}
		}
		
		foreach($schemas as $modeName=>&$schema){
			if(empty($iTriggers[$modeName])) $iTriggers[$modeName]=array();
			$currentTriggers=$schema->getTriggers();
			
			//debugVar($modeName,$currentTriggers,$triggers[$modeName]);
			
			// del
			foreach($a1=array_diff_key($currentTriggers,$iTriggers[$modeName]) as $name=>$trigger){
				//die('WANT TO DELETE TRIGGER : '.print_r($trigger,true));
			}
			
			// add
			foreach($a2=array_diff_key($iTriggers[$modeName],$currentTriggers) as $name=>$createSql){
				$schema->getDB()->doUpdate($createSql['start'].' '.$createSql['statement']);
			}
			
			foreach(array_diff_key($iTriggers[$modeName],$a1,$a2) as $name=>$createSql){
				if($createSql['statement']!==$currentTriggers[$name]['Statement']){
					$schema->getDB()->doUpdate('DROP TRIGGER `'.$name.'`');
					$schema->getDB()->doUpdate($createSql['start'].' '.$createSql['statement']);
				}
			}
		}
	}
}
