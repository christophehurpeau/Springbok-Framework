<?php
class UGitHub extends UGit{
	public static function check($repo_path){
		if(!parent::exists($repo_path)){
			if(!CGitHub::exists($repo_path)) return false;
			$localRepo=Config::$repositories_path.'github-repositories/'.$repo_path;
			parent::create($localRepo,$repo_path,true);
		}
		return true;
	}
}