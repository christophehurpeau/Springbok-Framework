<?php
class DBAsyncMysqlQuery extends DBAsyncQuery{
	private $_result;
	
	public function isAvailable(){
		$error = $reject = array();
		$res = mysqli_poll(array($this->_connect),0,10);
		if(!empty($error)) throw new Exception(print_r($error,true));
		if(!empty($reject)) throw new Exception(print_r($reject,true));
		return $res;
	}
	
	public function result(){
		return $this->_result = $this->_connect->reap_async_query();
	}
	
	public function close(){
		return mysqli_free_result($this->_result);
	}
}
