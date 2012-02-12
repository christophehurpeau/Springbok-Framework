<?php
$db=DB::init('test',array(
	'prefix'=>'','type'=>'MySQL','host'=>'localhost',
	'dbname'=>'mysql',
	'user'=>'mysql','password'=>'mysql'
));
$res=UProfiling::compare(1000,function() use(&$db){
	$result=$db->doSelectRows('SELECT * FROM db');
},function() use(&$db){
	$result=$db->doSelectRows('SELECT * FROM db WHERE Db='.$db->escape('mysql'));
},function() use(&$db){
	$result=$db->doSelectRow('SELECT * FROM db');
},function() use(&$db){
	$result=$db->doSelectRow('SELECT * FROM db WHERE Db='.$db->escape('mysql'));
},function() use(&$db){
	$result=$db->doSelectRows_('SELECT * FROM db');
},function() use(&$db){
	$result=$db->doSelectRows_('SELECT * FROM db WHERE Db='.$db->escape('mysql'));
},function() use(&$db){
	$result=$db->doSelectRow_('SELECT * FROM db');
},function() use(&$db){
	$result=$db->doSelectRow_('SELECT * FROM db WHERE Db='.$db->escape('mysql'));
},function() use(&$db){
	$result=$db->doSelectValues('SELECT * FROM db');
},function() use(&$db){
	$result=$db->doSelectValues('SELECT * FROM db WHERE Db='.$db->escape('mysql'));
},function() use(&$db){
	$result=$db->doSelectValue('SELECT help_topic_id FROM help_topic');
},function() use(&$db){
	$result=$db->doSelectValue('SELECT help_topic_id FROM help_topic WHERE help_category_id='.$db->escape(16));
},function() use(&$db){
	$result=$db->doSelectListValues('SELECT * FROM help_topic');
},function() use(&$db){
	$result=$db->doSelectListValues('SELECT * FROM help_topic WHERE help_category_id='.$db->escape(16));
},function() use(&$db){
	$result=$db->doSelectListValues_('SELECT * FROM help_topic');
},function() use(&$db){
	$result=$db->doSelectListValues_('SELECT * FROM help_topic WHERE help_category_id='.$db->escape(16));
},function() use(&$db){
	$result=$db->doSelectListValue('SELECT help_topic_id,name FROM help_topic');
},function() use(&$db){
	$result=$db->doSelectListValue('SELECT help_topic_id,name FROM help_topic WHERE help_category_id='.$db->escape(16));
},function(){
	$users=User::findAll();
});
print_r($res);