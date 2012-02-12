<?php
include_once CLIBS.'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Gdata_Feed');

class CGoogleData{
	private $user,$gdata;
	
	/** Can throw Exceptions ! */
	public function __construct($user,$pwd){
		$this->user=&$user;
		$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pwd,'cp');
		$client->setHeaders('If-Match: *');
		$this->gdata = new Zend_Gdata($client);
		$this->gdata->setMajorProtocolVersion(3);
	}
	
	public static function getContacts(){
		$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/'.$this->user.'/full');
		$query->setMaxResults(9999);
		return $this->gdata->getFeed($query);  
	}
}