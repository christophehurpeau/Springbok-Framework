<?php
/* https://developer.mozilla.org/en/Server-sent_events/Using_server-sent_events */
class SControllerServerSentEvents extends SController{
	public static function dispatch(&$suffix,&$mdef){
		self::$suffix=&$suffix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod(); $methodName=CRoute::getAction();
		if($method !=='GET') $methodName.=$method;
		
		if(!method_exists(get_called_class(),$methodName)) notFound();
		self::$methodName=&$methodName;
		$methodAnnotations=&$mdef['annotations'];
		static::crossDomainHeaders();
		$params=$mdef['params']===false?array():self::getParams($mdef,$methodAnnotations);
		array_unshift($params,$sser=new ServerSentEventsResponse());
		header("Content-Type: text/event-stream\n\n");
		while(ob_get_length()>0) ob_flush();
		while(false===call_user_func_array(array('static',$methodName),$params)) ;
	}
	
	public static function send($eventName,$data){
		
	}
}

class ServerSentEventsResponse{
	public function id($id){
		echo 'id: '.$id;
	}
	
	public function event($eventName){
		echo 'event: '.$eventName;
	}
	
	public function data($data){
		echo 'data: '.str_replace("\n","\ndata: ",$data);
	}
	
	public function jsonData($data){
		echo 'data: '.json_encode($data);
	}
	
	public function comment($comment){
		echo ': '.$comment;
	}
	
	public function push(){
		echo "\n\n";
		flush();
	}
}
