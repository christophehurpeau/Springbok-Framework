<?php
try{
	if(isset($_SERVER['REQUEST_URI'])) Guest::getOrCreate();
}catch(Exception $e){
	Springbok::handleException($e);
}
