<?php
/** http://www.cyberciti.biz/faq/linux-unix-reuse-openssh-connection/ */
class UExec{
	public static function createPersistantSsh($ssh,$for=120){
			$sshCommand=self::_sshCommand($ssh).'-f ';
			if(!empty($ssh['user'])) $sshCommand.=$ssh['user'].'@';
			$sshCommand.=$ssh['host'].' ';
			
			return self::execWithSshAgent($sshCommand.'"sleep '.((int)$for).'"',$ssh);
	}
	
	public static function rsync($source,$dest,$options,$rsyncOptions=false){
		/* bug : rsync: getcwd(): No such file or directory (2) */
		$command='cd / && rsync -'.($rsyncOptions!==false ? $rsyncOptions : ($options['simulation'] === true ? 'rtvnzC' : 'rtvlzC').' --delete');
		/*
		 * -v : verbose
		 * -r : recursive
		 * -t : preserve modification times
		 * -l : links
		 * -C : auto-ignore files in the same way CVS does
		 * -z : compress file data during the transfer
		*/
		
		//$dest=$dest;
		if($options['ssh']){
			$command.=' -e "'.(substr(self::_sshCommand($options['ssh']),0,-1)).'"';
			$dest=(empty($options['ssh']['user'])?'':$options['ssh']['user'].'@').$options['ssh']['host'].':'.escapeshellarg($dest);
		}
		
		if(!empty($options['exclude']))
			foreach($options['exclude'] as $exclude)
				$command.=' --exclude '.escapeshellarg($exclude);
		
		$command.=' '.escapeshellarg($source).' '.$dest;
		
		if($options['ssh'])
			return self::execWithSshAgent($command,$options['ssh']);
		return self::exec($command);
	}
	
	public static function copyFile($source,$dest,$ssh=false){
		if($ssh) return self::execWithSshAgent($cmd='scp '.escapeshellarg($source).' '.escapeshellarg((empty($ssh['user'])?'':$ssh['user'].'@').$ssh['host'].':'.$dest),$ssh);
		else return self::exec('cp '.escapeshellarg($source).' '.escapeshellarg($dest));
	}
	
	public static function php($args,$ssh=false){
		$args=func_get_args();
		$cmd='php';
		foreach($args as $key=>&$arg)
			$cmd.=' '.escapeshellarg($arg);
		return self::exec($cmd,$ssh=false);
	}

	private static function _sshCommand($ssh){
		$sshCommand='ssh  -C -c blowfish ';
		//if(!empty($ssh['forcePseudoTty'])) $sshCommand.='-t ';
		if(!empty($ssh['key_file'])) $sshCommand.='-i '.escapeshellarg($ssh['key_file']).' ';
		//if(!empty($ssh['known_hosts_file'])) $sshCommand.='-o UserKnownHostsFile='.escapeshellarg($ssh['known_hosts_file']).' ';
		return $sshCommand;
	}
	
	private static function execWithSshAgent($sshCommand,$ssh){
		if(empty($ssh['passphrase']))
			return self::exec($sshCommand);
		
		file_put_contents($filename=(DATA.'ssh/tmp'),$ssh['passphrase']);
		$res='';
		do{
			$res=self::exec('eval `ssh-agent` > /dev/null'
					. ' && '. 'ssh-add '.escapeshellarg($ssh['key_file']).' < '.escapeshellarg($filename)
					. ' && '. $sshCommand .' 2>&1');
		}while(strpos($res,'Connection timed out during banner exchange')!==false);
		file_put_contents($filename,'');
		return $res;
	}
	
	public static function getBasicCommand($ssh=false){
		$command='echo "ok"';
		$sshCommand=self::_sshCommand($ssh);
		if(!empty($ssh['user'])) $sshCommand.=$ssh['user'].'@';
		$sshCommand.=$ssh['host'].' '.escapeshellarg($command);
		return $sshCommand;
	}
	
	public static function exec($command,$ssh=false,$waiting=true){
		if($ssh){
			$sshCommand=self::_sshCommand($ssh);
			if(!empty($ssh['user'])) $sshCommand.=$ssh['user'].'@';
			$sshCommand.=$ssh['host'].' '.escapeshellarg($command);
			
			return self::execWithSshAgent($sshCommand,$ssh);
		}
		//CLogger::get('exec')->log($command);
		return UEncoding::convertToUtf8(trim(shell_exec($command.($waiting?' 2>&1':' > /dev/null 2>/dev/null &'))));
	}
	
	public static function createTarGz(/* HIDE */$cd,$options,$archive,$files/* /HIDE */){
		$files=func_get_args();
		$cd=array_shift($files);
		$options=array_shift($files);
		$archive=array_shift($files);
		return self::exec('cd '.escapeshellarg($cd).' && tar'.$options.' -czf '.escapeshellarg($archive).' '.implode(' ',array_map('escapeshellarg',$files)));
	}
	
	public static function rmEscape($path){
		return preg_replace('/([\*\[\]\?\!])/','\\\$1',$path);
	}
}