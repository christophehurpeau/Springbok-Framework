<?php
class CHttpClientParallel extends CHttpClient{
	
	public function useReferer(){ throw new Exception('Referer cannot be used in parallel mode.');}
	
	public static $MAX_PARALLEL_REQUESTS=8;
	private $master,$requests=array(),$i=0,$stop=false,$isCallback,$urls,$countUrls;
	
	private function initHandle(&$urls){
		$this->urls=&$urls;
		$this->stop=false;
		$this->requests=array();
		$this->i=0;
			
		if(!($this->isCallback=!is_array($urls))){
			$this->countUrls=count($urls);
			$max=min($countUrls,self::$MAX_PARALLEL_REQUESTS);
			// start the first batch of requests
			for(; $this->i < $max; )
				$this->addHandle($urls[$this->i]);
		}else{
			while($this->i < self::$MAX_PARALLEL_REQUESTS && ($target=$urls($this->i))!==false)
				$this->addHandle($target);
		}
	}
	
	private function addNewRequest(){
		if($this->stop===true) return;
		
		if($this->isCallback){
			$c=$this->urls;
			$target=$c($this->i);
			$target=$target();
		}else $target=&$this->urls[$this->i];
		
		empty($target) ? $this->stop=true : $this->addHandle($target);
	}
	
	private function addHandle($target){
		if(is_array($target)){
			$method='POST';
			$params=$target[1];
			$target=$target[0];
		}else{
			$method='GET';
			$params=null;
		}
		
		$this->requests[$this->i++]=$ch=$this->_curl_create($method,$target,$params);
		curl_multi_add_handle($this->master,$ch);
	}
	
	public function getUrls($urls,$callback){
		if($this->master!==null) throw new Exception('a parallel client is already running');
		$this->master = curl_multi_init();
		$curl_arr = array();
		
		$this->initHandle($urls);
		
		do{
			while(($execrun = curl_multi_exec($this->master,$running)) == CURLM_CALL_MULTI_PERFORM);
			if($execrun != CURLM_OK) break;
			
			// a request was just completed -- find out which one
			while($done =curl_multi_info_read($this->master)){
				
				$content=curl_multi_getcontent($done['handle']);
				$status=curl_getinfo($done['handle'],CURLINFO_HTTP_CODE);
				$error=curl_error($done['handle']);
				$callback($status,$error,$content);
				
				// start a new request (it's important to do this before removing the old one)
				$this->addNewRequest();

				// remove the curl handle that just completed
				curl_multi_remove_handle($this->master,$done['handle']);
			}
		}while($running);
	   
		curl_multi_close($this->master);
		$this->master=null;
		return true;
	}
}