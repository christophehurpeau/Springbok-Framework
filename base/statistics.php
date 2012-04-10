<?php
/* DEV */ if(!empty(Guest::$__PROP_DEF)): /* /DEV */
try{
	if(isset($_SERVER['REQUEST_URI'])) Guest::getOrCreate();
}catch(Exception $e){
	Springbok::handleException($e);
}
/* DEV */ endif; /* /DEV */