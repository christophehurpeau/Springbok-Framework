<?php
class_exists('CSocket');
abstract class WebSocketUser extends BasicSocketUser{
	private $handshake;
	
	public function hadHandshake(){return $this->handshake===true;}
	public function setHandshake(){$this->handshake=true;}
}
class CWebSocket extends CSocket{
	protected function received($user,$buffer,$socket,$bytes){
		# this is a new connection, no handshake yet
		if(!$user->hadHandshake()){
			$this->doHandshake($user,$buffer,$socket);
			return;
		}
		# handshake already done, read data
		$msg=$this->unwrap($buffer,$bytes);
		$this->log("<{$action}");
		

		if( method_exists('socketWebSocketTrigger',$action) ){
			$this->send($socket,socketWebSocketTrigger::$action());
		}else{
			for ($i = 0; $i <= 0; $i++) {
				$this->send($socket,"{$action}");
			}
		}
	}
	
	
	/**
	 * Manage the handshake procedure
	 *
	 * @param string $buffer The received stream to init the handshake
	 * @param socket $socket The socket from which the data came
	 */
	private function doHandshake($user,$buffer,$socket){
		$this->log('Requesting handshake...');
		$this->log($buffer);
		
		list($resource,$host,$origin,$key1,$key2,$l8b) = $this->getHeaders($buffer);

		$this->log('Handshaking...');
		$upgrade="HTTP/1.1 101 WebSocket Protocol Handshake\r\n"
			."Upgrade: WebSocket\r\n"
			."Connection: Upgrade\r\n"
			//."WebSocket-Origin: " . $origin . "\r\n"
			//."WebSocket-Location: ws://" . $host . $resource . "\r\n"
			."Sec-WebSocket-Origin: " . $origin . "\r\n"
			."Sec-WebSocket-Location: ws://" . $host . $resource . "\r\n"
			//."Sec-WebSocket-Protocol: icbmgame\r\n" . //Client doesn't send this
			."\r\n"
			.$this->calcKey($key1,$key2,$l8b)."\r\n";
		
		$this->send($socket,$upgrade.char(0));
		$user->setHandshake();

		$this->log('Done handshaking...');
	}
	
	private function getHeaders($req){
		$r=$h=$o=$sk1=$sk2=$l8b=null;
		if(preg_match("/GET (.*) HTTP/"			   ,$req,$match)) $r=$match[1];
		if(preg_match("/Host: (.*)\r\n/"			  ,$req,$match)) $h=$match[1];
		if(preg_match("/Origin: (.*)\r\n/"			,$req,$match)) $o=$match[1];
		if(preg_match("/Sec-WebSocket-Key1: (.*)\r\n/",$req,$match)) $this->log("Sec Key1: ".$sk1=$match[1]);
		if(preg_match("/Sec-WebSocket-Key2: (.*)\r\n/",$req,$match)) $this->log("Sec Key2: ".$sk2=$match[1]);
		if($match=substr($req,-8)) $this->log("Last 8 bytes: ".$l8b=$match);
		return array($r,$h,$o,$sk1,$sk2,$l8b);
	}
	
	private function calcKey($key1,$key2,$l8b){
		//Get the numbers
		preg_match_all('/([\d]+)/', $key1, $key1_num);
		preg_match_all('/([\d]+)/', $key2, $key2_num);
		//Number crunching [/bad pun]
		$this->log("Key1: " . $key1_num = implode($key1_num[0]) );
		$this->log("Key2: " . $key2_num = implode($key2_num[0]) );
		//Count spaces
		preg_match_all('/([ ]+)/', $key1, $key1_spc);
		preg_match_all('/([ ]+)/', $key2, $key2_spc);
		//How many spaces did it find?
		$this->log("Key1 Spaces: " . $key1_spc = strlen(implode($key1_spc[0])) );
		$this->log("Key2 Spaces: " . $key2_spc = strlen(implode($key2_spc[0])) );
		if($key1_spc==0|$key2_spc==0){ $this->log("Invalid key");return; }
		//Some math
		$key1_sec = pack("N",$key1_num / $key1_spc); //Get the 32bit secret key, minus the other thing
		$key2_sec = pack("N",$key2_num / $key2_spc);
		//This needs checking, I'm not completely sure it should be a binary string
		return md5($key1_sec.$key2_sec.$l8b,1); //The result, I think
		
	}
	
	protected function wrap($msg){ return chr(0).$msg.chr(255); }
	/** remove chr(0) and chr(255) */
	protected function unwrap($msg,$bytes){ return substr($msg,1,$bytes-2); }
}
