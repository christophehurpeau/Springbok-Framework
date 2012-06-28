<?php
abstract class BasicSocketUser{
	private static $userCount=0;
	private $id,$socket;
	public function __construct($socket){
		$this->socket=$socket;
		$this->id=++self::$userCount;
	}
	
	public function getId(){return $this->id;}
}
class CSocket{
	private $logger,$app;
	
	/** the master socket */
	protected $master;
	
	/** Holds all connected sockets */
	protected $allsockets=array(),$users=array();
	
	public function __construct($appName,$logger,$host='localhost',$port=8000,$max=100){
		$this->logger=$logger;
		$this->createSocket($host,$port);
		$this->createApp($appName);
	}
	
	protected function createApp($appName){
		$this->app=new CSocketApp($this);
		$this->app->run($appName);
	}
	
	protected function doAction($user,$socket,$buffer){
		$action_params=explode(' ',$buffer,2);
		if(!isset($action_params[1])) $action_params[1]=null;
		$res=$this->app->action($user,$action_params[0],$action_params[1]);
		if($res!==null){
			$this->send($socket,$this->wrap($res));
			$this->log($socket.' respond : '.$res);
		} 
	}
	
	protected function log($message){
		$this->logger->log($message);
	}
	
	/**
	 * Create a socket on given host/port
	 * @param string $host The host/bind address to use
	 * @param int $port The actual port to bind on
	 */
	private function createSocket($host,$port){
		if( ($this->master=socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0 ){
			die("socket_create() failed, reason: ".socket_strerror($this->master));
		}

		$this->log("Socket {$this->master} created.");

		socket_set_option($this->master,SOL_SOCKET,SO_REUSEADDR,1);
		#socket_set_option($master,SOL_SOCKET,SO_KEEPALIVE,1);

		if( ($ret=socket_bind($this->master,$host,$port)) < 0 )
			die("socket_bind() failed, reason: ".socket_strerror($ret));
		
		$this->log("Socket bound to {$host}:{$port}.");

		if( ($ret=socket_listen($this->master,5)) < 0 )
			die("socket_listen() failed, reason: ".socket_strerror($ret));
		
		$this->log('Start listening on Socket.');

		$this->allsockets[] = $this->master;
	}
	
	public function run(){
		while(true){
			# because socket_select gets the sockets it should watch from $changed_sockets
			# and writes the changed sockets to that array we have to copy the allsocket array
			# to keep our connected sockets list
			$changed_sockets = $this->allsockets;

			# blocks execution until data is received from any socket
			$write=$exceptions=null;
			socket_select($changed_sockets,$write,$exceptions,null);

			# foreach changed socket...
			foreach($changed_sockets as &$socket){
				# master socket changed means there is a new socket request
				if($socket==$this->master){
					# if accepting new socket fails
					if( ($client=socket_accept($this->master)) < 0 ){
						$this->log('socket_accept() failed: reason: ' . socket_strerror(socket_last_error($client)));
						continue;
					}
					
					$this->connect($client);
				}
				# client socket has sent data
				else{
					$userId=array_search($socket,$this->allsockets);
					$buffer=null;
					# the client status changed, but theres no data ---> disconnect
					$bytes = socket_recv($socket,$buffer,2048,0);
					if($bytes === 0)
						$this->close($userId,$socket);
					# there is data to be read
					else{
						$this->log($socket.' received : '.$buffer);
						$this->received($this->users[$userId],$buffer,$socket,$bytes);
					}
				}
			}
		}
	}


	protected function connect($socket){
		$user = new SocketUser($socket);
		$this->users[$user->getId()]=$user;
		$this->allsockets[$user->getId()]=$socket;
		$this->log($socket . ' CONNECTED!');
		
	}
	protected function received($user,$buffer,$socket,$bytes){
		$this->doAction($user,$socket,$buffer);
	}
	
	
	/**
	 * Sends a message over the socket
	 * @param socket $client The destination socket
	 * @param string $msg The message
	 */
	protected function send($socket,$msg){
		socket_write($socket,$msg,strlen($msg));
	}
	
	
	protected function wrap($msg){ return $msg."\n"; }
	protected function unwrap($msg,$bytes){ return rtrim($msg); }
	
	
	protected function close($userId,$socket){
		if($userId > 0){
			unset($this->allsockets[$userId]);
			unset($this->users[$userId]);
			$this->closed($userId);
		}
		socket_close($socket);
		$this->log($socket.' closed');
	}
	
	protected function closed($userId){}
}
