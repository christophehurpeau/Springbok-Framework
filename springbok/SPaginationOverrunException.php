<?php
/**
 * Occurs when the CPagination Component found a page in GET greater than the total number of pages. 
 */
class SPaginationOverrunException extends HttpException{
	public function __construct(){
		parent::__construct(404,'Not Found',_tC('http.404'));
	}
}
