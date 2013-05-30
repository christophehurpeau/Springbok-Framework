<?php
class CGitHub{
	public static function exists($userAndRepo){
		$res=CSimpleHttpClient::getJson('https://api.github.com/repos/'.$userAndRepo);
		CLogger::get('github-api')->log($userAndRepo.' => '.json_encode($res));
		return (!empty($res['message']) && $res['message'] === 'Not Found') ? false : $res['git_url'];
	}
}