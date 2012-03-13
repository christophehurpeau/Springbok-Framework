<?php
class CAcl{
	public static function checkAccess($permission){
		$groupId=0; //Guest
		if(CSecure::isConnected()){
			if(CSecure::isAdmin()) return true;
			$group_id=CSecure::user()->group_id;
			if($group_id!==null) $groupId=$group_id;
		}
		return AclGroupPerm::QExist()->where(array('granted'=>true,'group_id'=>&$groupId,'permission'=>&$permission));
	}
}