<?php
namespace VPS;

class PayPal
{
	private $client_id;
	private $client_secret;
	private $accessToken;
	private $apiUrl = 'https://api.paypal.com/';
	
	/** 
	* Create a new instance
	* @param string $client_id
	* @param string $client_secret
	* @param string $sandbox - optional
	* @param string $access_token - optional
	*/
	public function __construct($client_id, $client_secret, $sandbox = true, $access_token = false)
	{
		if($sandbox) $this->apiUrl = 'https://api.sandbox.paypal.com/';
		
		//validate
		if(! $client_id || ! $client_secret){
			throw new \Exception('client_id and client_secret must be set before making request!');
		}
		
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		
		if($access_token) $this->accessToken = $access_token;
		else $this->accessToken = $this->getAccessToken();
	}
	
	/** 
	* Private Method to request Access Token
	* @return string
	*/
	public function getAccessToken()
	{
			$endPoint = 'v1/oauth2/token?grant_type=client_credentials';
			$requestUrl = $this->apiUrl.$endPoint;
			$httpVerb = 'POST';
			$data = array('grant_type' => 'client_credentials');
			$data = http_build_query($data);
			$header = array(
				'Accept: application/json',
				'Accept-Language: en_US',
				'Authorization: Basic '.base64_encode($this->client_id.':'.$this->client_secret)
			);
			
			$result = $this->curlRequest($requestUrl, $httpVerb, $data, $header);
			if(isset($result['error'])) throw new \Exception('Error - '.$result['error_description']);
			else return $result['access_token'];
	}
	
	/** 
	* Magic Method to request http verb
	* @return array
	*/
	public function __call($method, $arguments)
	{
		$httpVerb = strtoupper($method);
		$allowedHttpVerbs = array('GET', 'POST', 'PATCH', 'DELETE');
		
		//Validate http verb
		if(in_array($httpVerb, $allowedHttpVerbs)){
			$endPoint = $arguments[0];
			$data = isset($arguments[1]) ? $arguments[1] : array();
			return $this->request($httpVerb, $endPoint, $data);
		}
		
		throw new \Exception('Invalid http verb!');
	}
	
	/** 
	* Call MailChimp API
	* @param string $httpVerb
	* @param string $endPoint - (http://kb.mailchimp.com/api/resources)
	* @param mixed $data - Optional
	* @return array
	*/
	public function request($httpVerb = 'GET', $endPoint, $data = false)
	{
		//validate Token
		if(! $this->accessToken){
			throw new \Exception('AccessToken is required before making request!');
		}
		
		$endPoint = ltrim($endPoint, '/');
		$httpVerb = strtoupper($httpVerb);
		$requestUrl = $this->apiUrl.$endPoint;
		$header = array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->accessToken
		);
		if($data && is_array($data)) $data = json_encode($data);
		
		return $this->curlRequest($requestUrl, $httpVerb, $data, $header);
	}
	
	/** 
	* Request using curl extension
	* @param string $url
	* @param string $httpVerb
	* @param mixed $data - Optional
	* @return array
	*/
	private function curlRequest($url, $httpVerb, $data = false, array $header = array(), $curlTimeout = 15)
	{
		if(function_exists('curl_init') && function_exists('curl_setopt')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, 'VPS/PP-API');
			curl_setopt($ch, CURLOPT_TIMEOUT, $curlTimeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpVerb);
			
			if(!empty($header)){
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			}
			
			//Submit data
			if(!empty($data)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			
			return $result ? json_decode($result, true) : false;
		}

		throw new \Exception('curl extension is missing!');
	}
}