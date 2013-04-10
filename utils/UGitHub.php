<?php
class UGitHub extends UGit{
	public static function open($repoPath){
		return parent::open(Config::$repositories_path.'github-repositories/'.$repoPath);
	}
	
	public static function check($repoPath,$create=true){
		if(!($gitUrl=CGitHub::exists($repoPath))) return false;
		$localRepo=Config::$repositories_path.'github-repositories/'.$repoPath;
		$parentLocalRepo=dirname($localRepo);
		if(!file_exists($parentLocalRepo))
			mkdir($parentLocalRepo,0755,true);
		if(!parent::exists($localRepo))
			return $create!==true ? false : (parent::create($localRepo,$gitUrl,true) ? true : false);
		return true;
	}
}