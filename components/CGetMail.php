<?php
class CGetMail{
	private $_ref,$_connect,$current_folder;
	public function __construct($username,$password,$server='localhost',$port=143,$protocol='imap',$ssl=false,$tls=false){
		$this->_connect=imap_open(($this->_ref=('{'.$server.':'.$port.'/'.$protocol.'/novalidate-cert'.($ssl?'/ssl':'').($tls?'/tls':'').'}')).($this->current_folder='INBOX'),
			$username,$password);
		if(!$this->_connect) throw new Exception('Unable to connect');
	}
	
	/* FOLDERS */
	
	public function selectFolder($folder,$force=false){
		if(!$force && $this->current_folder == $folder) return true;
		if(!imap_reopen($this->_connect,$this->_ref.$folder)) return imap_last_error();
		$this->current_folder=$folder;
		return true;
	}
	
	public function renameFolder($oldFolder,$newFolder){
		if(!imap_renamemailbox($this->_connect,$this->_ref.$oldfolder,$this->_ref.$newfolder)) return imap_last_error();
		return true;
	}
	
	public function deleteFolder($folder){
		if(!imap_deletemailbox($this->_connect,$this->_ref.$folder)) return imap_last_error();
		return true;
	}
	
	public function createFolder(){
		//imap_createmailbox
		//imap_subscribe
	}
	
	public function folders(){
		$reflength=strlen($this->_ref);
		$folders = imap_getmailboxes($this->_connect,$this->_ref, "*");
		$list_folders=array();
		if(is_array($folders)){
			foreach($folders as $fkey=>&$folder){
				$mapname = substr(utf8_encode(imap_utf7_decode($folder->name)),$reflength);
				if($mapname[0] != ".") {
					$f['fname'] = $folder->name;
					$f['name'] = $mapname;
					$f['delimiter'] = $folder->delimiter;
					$f['attributes'] = $folder->attributes;
					$f['attr_values'] = self::_getFolderAttributesFlagArray($folder->attributes);
					$list_folders[$fkey]=$f;
				}
			}
		}
		
		$tree_list_folders=array();
		foreach($list_folders as &$folder){
			$mapname=explode('.',$folder['name']);
			$folderpath=&$tree_list_folders;
			foreach($mapname as &$key){
				if(!isset($folderpath[$key])) $folderpath[$key]=array();
				$folderpath =& $folderpath[$key];
			}
			$folderpath['_']=$folder;
		}
		return $tree_list_folders;
	}
	
	
	private static function _getFolderAttributesFlagArray($p_attributes){
		$attrs[LATT_HASNOCHILDREN]=false;
		$attrs[LATT_HASCHILDREN]=false;
		$attrs[LATT_REFERRAL]=false;
		$attrs[LATT_UNMARKED]=false;
		$attrs[LATT_MARKED]=false;
		$attrs[LATT_NOSELECT]=false;
		$attrs[LATT_NOINFERIORS]=false;
		$attrsX=$attrs;
		foreach($attrs as $attrkey=>$attrval){
			if ($p_attributes & $attrkey){
				$attrsX[$attrkey]=true;
				$p_attributes-=$attrkey;
			}
		}
		return $attrsX;
	}
	
	
	private function search($args){
		//construct the query
		$query = '';
		foreach ($args as $k => $v)
		{
			if (!preg_match('/SUBJECT|FROM|TO|BODY|BEFORE|SINCE|FLAGGED/', $k))
				continue;

			if ($k == 'FLAGGED') {
				if (!$v)
					continue;

				$query .= "$k ";
			} else {
				if (!$v)
					continue;
				$query .= "$k \"$v\" ";
			}
		}

		if ($args['EmailAttach'])
			$query .= "HEADER Content-Type mixed";

		if ($this->cur_mailbox != $args['EmailBox'])
			$this->_changeMailbox($args['EmailBox']);

	 	if ($this->use_native)
	 		$res = imap_search($this->mailer, $query);

		else
			$res = $this->mailer->search($query);

		if(is_array($res))
		foreach ($res as $k => $id)
			$res[$k] = $args['EmailBox'] . "::$id";

		return $res;
	}
	
	
	/* MAILS */
	
	public function getUnread(){
		$infos=imap_mailboxmsginfo($this->mailer);
		if(!$info) throw new Exception("Error");
		return $infos->Unread;
	}
	
	public function getMails(){
		$mailstab=imap_sort($this->_connect,SORTARRIVAL,1);
		//$message_count = count($mailstab);//imap_num_msg($this->_connect);
		$i=0; $mails=array();
		foreach($mailstab as $i){
			$header = imap_header($this->_connect,$i);
			//$body = trim(substr(imap_body($this->_connect,$i), 0, 100));
			if(empty($header->subject)) $subject=false;
			else{
				$subject='';
				$elements=imap_mime_header_decode($header->subject);
				foreach($elements as &$e) $subject.=$e->text;
				$subject=trim($subject);
				//debug($subject);
			}
			$mails[]=array(
				'i'=>$i,
				'from'=>imap_utf8($header->fromaddress),
				'date'=>date('Y-m-d H:i:s',strtotime($header->date)),
				'subject'=>$subject,
				'isSeen'=>!($header->Recent=='N' || $header->Unseen == 'U'),
				'isAnswered'=>$header->Answered=='A',
			);
		}
		return $mails;
	}
	
	/*
	public function getMail($num,$folder=NULL,$format=0,$cache=1){
		
	}
	*/
	public function getMailHeaders($msg_number){
		return imap_fetchheader($this->_connect,$msg_number);
	}
	
	public function getMail($msg_number){
		return imap_fetchheader($this->_connect,$msg_number).imap_body($this->_connect,$msg_number);
	}
	
	public function copyMail($num,$newFolder){
		if(!imap_mail_copy($this->_connect,$num,$this->_ref.$newFolder)) return imap_last_error();
		return true;
	}
	
	public function moveMail($num,$newFolder){
		if(!imap_mail_move($this->_connect,$num,$this->_ref.$newFolder)) return imap_last_error();
		return true;
	}
	
	public function deleteMessage($num,$folder,$user_uid=false){
		if(!imap_delete($this->_connect, $num)) return imap_last_error();
		imap_expunge($this->_connect);
		return true;
	}
	
	
}
