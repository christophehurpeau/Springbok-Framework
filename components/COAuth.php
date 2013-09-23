<?php
include CLIBS.'oauth.php';
/**
 * OAuth authentification
 */
class COAuth{
	private $accessToken,$url=NULL,$fullResponse=NULL;
	
	public function __construct($accessTokenKey,$accessTokenSecret){
		$this->accessToken = new OAuthToken($accessTokenKey, $accessTokenSecret);
	}
	
	public function get($consumer,$url,$getData=array()){
		$request = $this->createRequest($consumer, 'GET', $url, $getData);
		
		return self::doGet($request->to_url());
	}
	
	public function getAccessToken($consumer, $accessTokenURL, $requestToken, $httpMethod = 'POST', $parameters = array()) {
		$this->url = $accessTokenURL;
		$queryStringParams = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);
		$parameters['oauth_verifier'] = $queryStringParams['oauth_verifier'];
		$request = $this->createRequest($consumer, $httpMethod, $accessTokenURL, $requestToken, $parameters);
		
		return $this->doRequest($request);
	}
	
	public function getFullResponse(){
		return $this->fullResponse;
	}
	
	/**
	 * Call API with a POST request
	 */
	public function post($consumer, $url, $postData = array()) {
		$request = $this->createRequest($consumer, 'POST', $url, $postData);
		
		return $this->doPost($url, $request->to_postdata());
	}
	
	protected function createOAuthToken($response) {
		if (isset($response['oauth_token']) && isset($response['oauth_token_secret'])) {
			return new OAuthToken($response['oauth_token'], $response['oauth_token_secret']);
		}
		
		return null;
	}
	
	private function createRequest($consumer, $httpMethod, $url, array $parameters) {
		$request = OAuthRequest::from_consumer_and_token($consumer, $this->accessToken, $httpMethod, $url, $parameters);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $this->accessToken);
		
		return $request;
	}
	
	private function doGet($url) {
		$socket = new HttpSocket();
		return $socket->get($url);
	}
	
	private function doPost($url, $data) {
		$socket = new HttpSocket();
		return $socket->post($url, $data);
	}
	
	private function doRequest($request) {
		if($request->get_normalized_http_method() === 'POST')
			$data = $this->doPost($this->url, $request->to_postdata());
		else
			$data = $this->doGet($request->to_url());

		$this->fullResponse = $data;
		$response = array();
		parse_str($data, $response);

		return $this->createOAuthToken($response);
	}
}