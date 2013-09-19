<?php
class_exists('URepository',true);
/* https://github.com/kbjr/Git.php/blob/master/Git.php */
/* & http://code.fealdia.org/viewgit/?a=viewblob&p=viewgit&h=d292901c58ccae33d8c249afcccc2cdd065b375c&hb=93bdbda834621af67d8f3d6bdeac8a81d3f05f33&f=inc/functions.php */
/* & Redmine */
class UGit{
	public static function create($repo_path,$source=null,$bare=false){
		if(self::exists($repo_path))
			throw new Exception('"'.$repo_path.'" is already a git repository');
		$repo=new GitRepository($repo_path,true,false);
		if($source !== null) $repo->clone_from($source,$bare);
		else $repo->run('init'.($bare?' --bare':''));
		return $repo;
	}
	
	public static function open($repoPath){
		return new GitRepository($repoPath);
	}
	
	public static function exists($repoPath){
		$repoPath=rtrim($repoPath,'/');
		return is_dir($repoPath) && (file_exists($repoPath."/.git") && is_dir($repoPath."/.git") || (file_exists($repoPath.'/HEAD')));
	}
	
	public static function check($repoPath,$source=null,$bare=false){
		if(self::exists($repoPath)) return true;
		if($source===null) return false;
		$parentLocalRepo=dirname($repoPath);
		if(!file_exists($parentLocalRepo))
			mkdir($parentLocalRepo,0755,true);
		return self::create($repoPath,$source,true) ? true : false;
	}
}

class GitRevision extends AbstractRepositoryRevision{
	public function identifier(){
		return substr($this->identifier,0,8);
	}
}

/**
* Git Repository Interface Class
*
* This class enables the creating, reading, and manipulation
* of a git repository
*/
class GitRepository{
	protected $repo_path=null,$_logName;
	
	public function __construct($repo_path=null,$create_new=false,$_init=true){
		if($repo_path!==null) $this->set_repo_path($repo_path,$create_new,$_init);
	}
	
	public function set_repo_path($repo_path,$create_new=false,$_init =true){
		if($new_path=realpath($repo_path)){
			$repo_path = $new_path;
			if(is_dir($repo_path)){
				if((file_exists($repo_path."/.git") && is_dir($repo_path."/.git")) || file_exists($repo_path.'/HEAD'))
					$this->repo_path = $repo_path;
				elseif($create_new){
					$this->repo_path = $repo_path;
					if ($_init) $this->run('init');
				}else throw new Exception('"'.$repo_path.'" is not a git repository');
			}else throw new Exception('"'.$repo_path.'" is not a git repository');
		}else{
			if($create_new){
				if($parent=realpath(dirname($repo_path))){
					mkdir($repo_path);
					$this->repo_path = $repo_path;
					if ($_init) $this->run('init');
				}else throw new Exception('cannot create repository in non-existent directory');
			}else throw new Exception('"'.$repo_path.'" does not exist');
		}
		$this->_logName=basename($this->repo_path).'-'.md5($this->repo_path);
	}
	
	/**
	 * Run a command
	 * 
	 * @param string
	 * @param string
	 * @return string
	 */
	public function run($command,$beforeCommand=''){
		$res=UExec::exec('cd '.escapeshellarg($this->repo_path).' && '.$beforeCommand.' git '.$command);
		$this->log($command."\n".$res);
		return $res;
	}
	
	
	public function log($log){
		CLogger::get('git-exec-'.$this->_logName)->log($log);
	}
	
	
	public function add($files='*'){
		if(is_array($files)) $files = implode(' ',array_map('escapeshellarg',$files));
		return $this->run('add '.$files.' -v');
	}
	
	public function commit($message,$files=NULL){
		if($files!==NULL) $this->add($files);
		return $this->run('commit -av -m '.escapeshellarg($message));
	}
	
	public function fetch(){
		$this->run('fetch origin master:master');
	}

	public function status(){
		/* https://www.kernel.org/pub/software/scm/git/docs/git-status.html */
		$o=$this->run('status --porcelain');
		return array_map(function($l){ return array('status'=>trim(substr($l,0,2)),'file'=>substr($l,3)); },explode("\n",$o));
	}
	
	public function isUpToDate(){
		return $this->run('fetch --dry-run 2>&1')==='';
	}
	
	public function getUntrackedFiles(){
		return $this->run('ls-files --others --exclude-standard');
	}
	
	public function stateWithRemote(){
		return $this->run('rev-list --left-right master...HEAD');
	}
	
	
	public function clone_to($target){
		return UExec::exec('git clone '.escapeshellarg($this->repo_path).' '.escapeshellarg($target));
	}

	public function clone_from($source,$bare=false){
		return UExec::exec('git clone'.($bare?' --bare':'').' '.escapeshellarg($source).' '.escapeshellarg($this->repo_path));
	}
	
	public function branch_create($name){
		return $this->run('branch '.escapeshellarg($name));
	}
	
	public function branch_delete($name,$force=false){
		return $this->run('branch -'.($force?'D':'d').' '.escapeshellarg($name));
	}
	
	public function branches(){
		$output=explode("\n",$this->run('branch --no-color --verbose --no-abbrev'));
		$branches=array();
		foreach($output as $i=>&$branch){
			if(preg_match('/^\s*\*?\s*(.*)\s+([0-9a-f]{40}).*$/',$branch,$m))
				$branches[$m[1]]=array('revision'=>$m[2],'identifier'=>$m[2]);
		}
		return $branches;
	}
	
	public function currentBranch(){
		return $this->run('rev-parse --abbrev-ref HEAD');
	}
	
	/** Get details of a commit: tree, parents, author/committer (name, mail, date), message */
	public function commitInfos($hash='HEAD'){
		$infos=array('name'=>$hash,'message'=>'');
		$output = $this->run('rev-list --header --max-count=1 '.$hash);
		// tree <h>
		// parent <h>
		// author <name> "<"<mail>">" <stamp> <timezone>
		// committer
		// <empty>
		// <message>
		$pattern='/^(author|committer) ([^<]+) <([^>]*)> ([0-9]+) (.*)$/';
		foreach($output as $line){
			if(substr($line,0,4) === 'tree') $infos['tree']=substr($line,5);
			// may be repeated multiple times for merge/octopus
			elseif(substr($line,0,6) === 'parent') $infos['parents'][] = substr($line,7);
			elseif(preg_match($pattern,$line,$matches) > 0){
				$infos[$matches[1] .'_name'] = $matches[2];
				$infos[$matches[1] .'_mail'] = $matches[3];
				$infos[$matches[1] .'_stamp'] = $matches[4] + ((intval($matches[5]) / 100.0) * 3600);
				$infos[$matches[1] .'_timezone'] = $matches[5];
				$infos[$matches[1] .'_utcstamp'] = $matches[4];
				 
				if(isset($conf['mail_filter'])) $info[$matches[1] .'_mail'] = $conf['mail_filter']($infos[$matches[1] .'_mail']);
			}
			// Lines starting with four spaces and empty lines after first such line are part of commit message
			elseif(substr($line, 0, 4) === ' ') $infos['message'].=substr($line,4)."\n";
			elseif(strlen($line) == 0 && isset($infos['message'])) $infos['message'].="\n";
			elseif(preg_match('/^[0-9a-f]{40}$/',$line) > 0) $infos['h'] = $line;
		}

		$infos['author_datetime'] = gmstrftime('%Y-%m-%d %H:%M:%S', $infos['author_utcstamp']);
		$infos['author_datetime_local'] = gmstrftime('%Y-%m-%d %H:%M:%S', $infos['author_stamp']) .' '. $infos['author_timezone'];
		$infos['committer_datetime'] = gmstrftime('%Y-%m-%d %H:%M:%S', $infos['committer_utcstamp']);
		$infos['committer_datetime_local'] = gmstrftime('%Y-%m-%d %H:%M:%S', $infos['committer_stamp']) .' '. $infos['committer_timezone'];
		
		return $infos;
	}

	/** */
	public function entries($path,$identifier=NULL){
		$entries=array();
		$output=explode("\n",$this->run('ls-tree -l '.($identifier===NULL?'HEAD':$identifier).':'.$path));
		foreach($output as $line){
			$matches=array();
			if(preg_match('/^\d+\s+(\w+)\s+([0-9a-f]{40})\s+([0-9-]+)\t(.+)$/',$line,$matches))
				$entries[$matches[1]==='tree'?'folders':'files'][$matches[4]]=array('type'=>$matches[1], 'hash'=>$matches[2], 'size'=>$matches[3]);
		}
		if(isset($entries['folders'])) UArray::knatsort($entries['folders']);
		if(isset($entries['files'])) UArray::knatsort($entries['files']);
		return $entries;
	}
	
	public function cat($path,$identifier=null){
		return $this->run('show '.($identifier===null?'HEAD':$identifier).':'.$path);
	}
	public function diff($path,$identifierFrom,$identifierTo=null,$branch=null){
		return $this->run(($identifierTo===NULL?'show ':'diff '.$identifierTo).' '.$identifierFrom.' -- '.$path);
	}
	
	public function checkout($branch){
		return $this->run('checkout '.escapeshellarg($branch));
	}
	
	const FORMAT_SHORT_REVISIONS='"%H %at%n%an%n%ae%n%s"';
	private static function parseShortRevisions($output){
		$revisions=array();
		if(empty($output)) return $revisions;
		$numLine=0;$revision=null;
		foreach(explode("\n",$output) as $line){
			if(preg_match('/^([0-9a-f]{40}) (.*)$/',$line,$m)){
				unset($revision);
				$revision=array('identifier'=>$m[1],'time'=>$m[2],'comment'=>'');
				$revisions[]=&$revision;
				$numLine=1;
			}elseif($numLine===1){
				$revision['authorName']=$line;
				$numLine++;
			}elseif($numLine===2){
				$revision['authorEmail']=$line;
				$numLine++;
			}elseif($numLine===3){
				$revision['comment'].=$line;
			}
		}
		return $revisions;
	}
	
	public function shortRevisions($path,$identifierFrom=null,$identifierTo=null,$options=array()){
		$output=$this->run('log --no-color --encoding=UTF-8 --format=format:'.self::FORMAT_SHORT_REVISIONS
			.(isset($options['reverse'])&&$options['reverse']?' --reverse':'')
			.(isset($options['all'])&&$options['all']?' --all':'')
			.(isset($options['limit'])?' -n'.$options['limit']:'')
			.($identifierFrom===null?'':$identifierFrom.'..')
			.($identifierTo===null?'':$identifierTo)
			.(!empty($path)?' -- '.$path:''));
		return self::parseShortRevisions($output);
	}
	
	
	public function revisions($path,$identifierFrom=null,$identifierTo=null,$options=array()){
		$output=$this->run('log --no-color --encoding=UTF-8 --raw --date=iso --pretty=fuller --parents'
			.(isset($options['reverse'])&&$options['reverse']?' --reverse':'')
			.(isset($options['all'])&&$options['all']?' --all':'')
			.(isset($options['limit'])?' -n'.$options['limit']:'')
			.($identifierFrom===null?'':' '.$identifierFrom.'..')
			.($identifierTo===null?'':' '.$identifierTo)
			.(!empty($path)?' --'.$path:''));
		$revisions=array();
		if(empty($output)) return $revisions;
		$revision=new GitRevision(); $parsing_state=0;  //0: not parsing desc or files, 1: parsing desc, 2: parsing files
		foreach(explode("\n",$output) as $line){
			if(preg_match('/^commit ([0-9a-f]{40})(( [0-9a-f]{40})*)$/',$line,$m)){
				if($parsing_state===1||$parsing_state===2){
					$parsing_state=0;
					$revisions[]=$revision;
					$revision=new GitRevision();
				}
				$revision->identifier=$revision->id=$m[1];
				$revision->parents=empty($m[2])?null:explode(' ',trim($m[2]));
				$revision->files=array();
			}elseif($parsing_state===0 && preg_match('/^(\w+):\s*(.*)$/s',$line,$m)){
				if($m[1]==='Author' || $m[1]==='Commit'){
					preg_match('/^\s*(.*)\s+\<\s*([^>]+)\s*>\s*$/U',$m[2],$m2);
					$fieldName= $m[1]==='Author' ? 'authorName' : 'committerName';
					$revision->$fieldName=empty($m2)?$m[2]:$m2[1];
					$fieldName= $m[1]==='Author' ? 'authorEmail' : 'committerEmail';
					$revision->$fieldName=empty($m2)?'':$m2[2];
				}elseif($m[1]==='CommitDate') $revision->time=strtotime($m[2]);
				elseif($m[1]==='AuthorDate') $revision->authorTime=strtotime($m[2]);
			}elseif($parsing_state===0 && $line===''){
				$parsing_state=1;
				$revision->comment='';
			}elseif(($parsing_state===1 || $parsing_state===2)
					&& (preg_match('/^:\d+\s+\d+\s+[0-9a-f.]+\s+[0-9a-f.]+\s+(\w)\t(.+)$/',$line,$m)
						|| preg_match('/^:\d+\s+\d+\s+[0-9a-f.]+\s+[0-9a-f.]+\s+(\w)\d+\s+(\S+)\t(.+)$/',$line,$m))
					){
				$parsing_state=2;
				$revision->files[$m[2]]=$m[1];
			}elseif($parsing_state===1 && $line===''){
				$parsing_state=2;
			}elseif($parsing_state===1){
				$revision->comment.=substr($line,4);
			}
		}
		
		if(!empty($revision->id)) $revisions[]=$revision;
		return $revisions;
	}

	public function fileHistory($path,$identifierFrom=null,$identifierTo=null,$options=array()){
		$output=$this->run('log --no-color --encoding=UTF-8 --follow --format=format:'.self::FORMAT_SHORT_REVISIONS
			.(isset($options['reverse'])&&$options['reverse']?' --reverse':'')
			.(isset($options['all'])&&$options['all']?' --all':'')
			.(isset($options['limit'])?' -n'.$options['limit']:'')
			.($identifierFrom===null?'':$identifierFrom.'..')
			.($identifierTo===null?'':$identifierTo)
			.' -- '.$path);
		return self::parseShortRevisions($output);
	}


	/* UPDATE A BARE REPOSITORY */
	
	
	public function editFile($content,$path){
		$sha1=$this->run('hash-object -w --stdin ',escapeshellarg($content).' | ');
		$this->run('update-index --cacheinfo 100644 '.escapeshellarg($sha1).' '.escapeshellarg($path));
	}
}