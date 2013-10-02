<?php
/**
 * Server-Sent events
 * 
 * @see https://developer.mozilla.org/en/Server-sent_events/Using_server-sent_events
 */
class SControllerServerSentEvents extends Controller{
	protected static $resp;
	
	/**
	 * @internal
	 */
	public static function dispatch($suffix,$mdef){
		self::$suffix=$suffix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod(); $methodName=CRoute::getAction();
		if($method !=='GET') $methodName.=$method;
		
		if(!method_exists(get_called_class(),$methodName)) notFound();
		$mdef=include $mdef;
		$methodAnnotations=$mdef['annotations'];
		$params=$mdef['params']===false?array():self::getParams($mdef,$methodAnnotations);
		self::$resp=new ServerSentEventsResponse();
		header("Content-Type: text/event-stream");
		ignore_user_abort(true);
		while(ob_get_length()!==false) ob_end_flush();
		while(connection_aborted()===0 && false===call_user_func_array(array('static',$methodName),$params)) ;
	}
}

/**
 * Server-Sent Event Response
 */
class ServerSentEventsResponse{
	/**
	 * Send the id of the response
	 * 
	 * @param string|int
	 * @return void
	 */
	public function id($id){
		echo 'id: '.$id."\n";
	}
	
	/**
	 * Send the event name
	 * 
	 * @param string
	 * @return void
	 */
	public function event($eventName){
		echo 'event: '.$eventName."\n";
	}
	
	/**
	 * Send the data of the response
	 * 
	 * @param string
	 * @return void
	 */
	public function data($data){
		echo 'data: '.str_replace("\n","\ndata: ",$data)."\n";
	}
	
	/**
	 * Send a json not-yet-encoded data
	 * 
	 * @param mixed
	 * @return void
	 */
	public function jsonData($data){
		echo 'data: '.json_encode($data)."\n";
	}
	
	/**
	 * Send a comment
	 * 
	 * @param string
	 * @return void
	 */
	public function comment($comment){
		echo ': '.$comment."\n";
	}
	
	/**
	 * End the current response
	 * 
	 * @return void
	 */
	public function push(){
		echo "\n";
		flush();
	}
}
