<?php
class SPaginationOverrunException extends HttpException{
	public function __construct(){
		parent::__construct(404,'Not Found',_tC('http.404'));
	}
}
