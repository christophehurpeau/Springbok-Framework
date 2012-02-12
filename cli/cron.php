<?php
/* http://matthieu.developpez.com/execution_periodique/ */
/* http://www.phpclasses.org/package/4140-PHP-Database-driven-PHP-job-scheduler-like-cron.html#download */
/* http://greenservr.com/projects/crontab/crontab.phps */


$workspace=Workspace::findOneById($argv['workspace_id']);
if(empty($workspace)) die('Unknown workspace');
Model::$__dbName=$workspace->db_name;

/*if(!isset($server)){
	$server=Server::findOneByServer_id($argv['server_id']);
	if(empty($server)) die('Unknown server');
}*/

//$projectsPath=Project::findValuesPathByServer_id($server->id);
