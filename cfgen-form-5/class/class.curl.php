<?php
class Curl{
	
	function __construct(){

	}
	
	function setCurlRequestUrl($url){
		$this->request_url = $url;
	}
	
	function getCurlRequestUrl(){
		return $this->request_url;
	}
	
	function setCurlRequestContent($content){
		$this->request_content = $content;
	}
	
	function getCurlRequestContent(){
		return $this->request_content;
	}
	
	function setCurlRequestHeaders($headers){
		$this->request_headers = $headers;
	}
	
	function getCurlRequestHeaders(){
		return isset($this->request_headers) ? $this->request_headers : null;
	}

	function setCurlResponse($response){
		$this->curl_response = $response;
	}

	function getCurlResponse(){
		return $this->curl_response;
	}
	
	function setCurlRequestPostType(){
		$this->request_type_post = true;
	}
	
	function getCurlRequestPostType(){
		return !empty($this->request_type_post) ? true : false; 
	}

	function setCurlUsername($value){
		$this->curl_username = $value;
	}
	
	function getCurlUsername(){
		return isset($this->curl_username) ? $this->curl_username : null;
	}

	function setCurlPassword($value){
		$this->curl_password = $value;
	}
	
	function getCurlPassword(){
		return isset($this->curl_password) ? $this->curl_password : null;
	}
	
	
	function execCurl(){
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		curl_setopt($ch, CURLOPT_URL, $this->getCurlRequestUrl());
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if($this->getCurlRequestPostType()){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getCurlRequestContent());
		}
		
		if($this->getCurlRequestHeaders()) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlRequestHeaders());
		
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		
		if($this->getCurlUsername() && $this->getCurlPassword()) curl_setopt($ch, CURLOPT_USERPWD, $this->getCurlUsername() .':'. $this->getCurlPassword());
		
		$this->setCurlResponse(curl_exec($ch));
		
		curl_close($ch);
		
	}
	
}

?>