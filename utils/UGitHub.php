<?php
class UGitHub extends UGit{
	public static function open($repo_path){
		return parent::open(Config::$repositories_path.'github-repositories/'.$repo_path);
	}
	
	public static function check($repo_path){
		if(!($gitUrl=CGitHub::exists($repo_path))) return false;
		$localRepo=Config::$repositories_path.'github-repositories/'.$repo_path;
		$parentLocalRepo=dirname($localRepo);
		if(!file_exists($parentLocalRepo))
			mkdir($parentLocalRepo,0755,true);
		if(!parent::exists($localRepo))
			parent::create($localRepo,$gitUrl,true);
		return true;
	}
}