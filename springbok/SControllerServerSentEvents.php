<?php
/* https://developer.mozilla.org/en/Server-sent_events/Using_server-sent_events */
class SControllerServerSentEvents extends Controller{
	protected static $resp;
	
	public static function dispatch(&$suffix,&$mdef){
		self::$suffix=&$suffix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod(); $methodName=CRoute::getAction();
		if($method !=='GET') $methodName.=$method;
		
		if(!method_exists(get_called_class(),$methodName)) notFound();
		self::$methodName=&$methodName;
		$methodAnnotations=&$mdef['annotations'];
		$params=$mdef['params']===false?array():self::getParams($mdef,$methodAnnotations);
		self::$resp=new ServerSentEventsResponse();
		header("Content-Type: text/event-stream");
		ignore_user_abort(true);
		while(ob_get_length()!==false) ob_end_flush();
		while(connection_aborted()===0 && false===call_user_func_array(array('static',$methodName),$params)) ;
	}
}

class ServerSentEventsResponse{
	public function id($id){
		echo 'id: '.$id."\n";
	}
	
	public function event($eventName){
		echo 'event: '.$eventName."\n";
	}
	
	public function data($data){
		echo 'data: '.str_replace("\n","\ndata: ",$data)."\n";
	}
	
	public function jsonData($data){
		echo 'data: '.json_encode($data)."\n";
	}
	
	public function comment($comment){
		echo ': '.$comment."\n";
	}
	
	public function push(){
		echo "\n";
		flush();
	}
}
