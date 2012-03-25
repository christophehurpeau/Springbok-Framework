<?php
class FPaginationOverrunException extends HttpException{
	public function __construct(){
		parent::__construct(404,'Not Found',_tC('The page you requested was not found'));
	}
}
