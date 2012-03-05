<?php
class CAcl{
	public static function checkAccess($permission){
		$groupId=0; //Guest
		if(CSecure::isConnected()){
			if(CSecure::user()->isAdmin()) return true;
			$group_id=CSecure::user()->group_id;
			if($group_id!==null) $groupId=$group_id;
		}
		return GroupPerm::QExist()->where(array('granted'=>true,'group_id'=>&$groupId,'permission'=>&$permission));
	}
}
