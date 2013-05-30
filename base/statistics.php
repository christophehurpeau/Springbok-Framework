<?php
/*#if DEV */ if(!empty(Guest::$__PROP_DEF)): /*#/if*/
try{
	if(isset($_SERVER['REQUEST_URI'])) Guest::getOrCreate();
}catch(Exception $e){
	Springbok::handleException($e);
}
/*#if DEV */ endif; /*#/if*/