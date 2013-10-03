<?php
/** 
 * ACL Component. You can override the checkAccess method if you need something else
 * 
 * Example for a project management
 * <code>
 * class ACAcl extends CAcl{
 * 	public static function checkAccess($permission,$projectId=null){
 *		if(CSecure::isConnected()){
 *			if(CSecure::isAdmin()) return true;
 *			
 *			$roleId=AclGroup::BASIC_USER;
 *			
 *			if($projectId===null)
 *				$role_id=ProjectMemberRole::QValues()->field('DISTINCT role_id')->with('ProjectMember',false)
 *						->where(array('mbr.user_id'=>CSecure::connected()));
 *			else
 *				$role_id=ProjectMemberRole::QValues()->field('role_id')->with('ProjectMember',false)
 *						->where(array('mbr.project_id'=>$projectId,'mbr.user_id'=>CSecure::connected()));
 *			
 *			if($role_id!==false){
 *				$roleId=$role_id;
 *				$roleId[]=AclGroup::GUEST;
 *				$roleId[]=AclGroup::BASIC_USER;
 *			}
 *		}else $roleId=AclGroup::GUEST;
 *		return AclGroupPerm::QExist()->where(array('granted'=>true,'group_id'=>&$roleId,'permission'=>&$permission));
 *	}
 * }
 * </code>
 * 
 * 
 * Use case :
 * <code>
 * function(int $id){
 * 	$project = Project::ById($id)->mustFetch();
 * 	ACAcl::requireAccess('ManageMembers',$project->id);
 * }
 * </code>
 */
class CAcl{
	/**
	 * Check permission
	 * 
	 * @param string
	 * @return bool
	 */
	public static function checkAccess($permission){
		if(CSecure::isConnected()){
			$groupId=AclGroup::BASIC_USER;
			
			if(CSecure::isAdmin()) return true;
			$group_id=CSecure::user()->group_id;
			if($group_id!==null) $groupId=$group_id;
		}else $groupId=AclGroup::GUEST;
		return AclGroupPerm::QExist()->where(array('granted'=>true,'group_id'=>$groupId,'permission'=>$permission));
	}
	
	/**
	 * Check if has access, else throw forbidden if connected or redirecto to login page if not
	 * 
	 * @return void
	 */
	public static function requireAccess(){
		if(false===call_user_func_array(array('static','checkAccess'),func_get_args()))
			CSecure::isConnected() ? forbidden() : CSecure::redirectToLogin();
	}
}
