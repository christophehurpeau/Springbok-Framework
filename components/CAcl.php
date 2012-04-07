<?php
class CAcl{
	public static function checkAccess($permission){
		if(CSecure::isConnected()){
			$groupId=AclGroup::BASIC_USER;
			
			if(CSecure::isAdmin()) return true;
			$group_id=CSecure::user()->group_id;
			if($group_id!==null) $groupId=$group_id;
		}else $groupId=AclGroup::GUEST;
		return AclGroupPerm::QExist()->where(array('granted'=>true,'group_id'=>&$groupId,'permission'=>&$permission));
	}
}
