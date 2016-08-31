<?php
class SmsNotification{
	
	function __construct(Curl $curl){
		$this->curl = $curl;
	}
	
	function getCurlObj(){
		return $this->curl;
	}
	
	function setSmsNotificationPhoneNumber($value){
		$this->phone_number = $value;
	}
	
	function getSmsNotificationPhoneNumber(){
		return $this->phone_number;
	}

	function setSmsNotificationMessage($value){
		$this->message = $value;
	}

	function getSmsNotificationMessage(){
		return $this->message;
	}
	

}


class SmsNotificationClickatell extends SmsNotification{

	function setClickatellUsername($value){
		$this->clickatell_username = $value;
	}
	
	function getClickatellUsername(){
		return $this->clickatell_username;
	}

	function setClickatellPassword($value){
		$this->clickatell_password = $value;
	}
	
	function getClickatellPassword(){
		return $this->clickatell_password;
	}

	function setClickatellApiId($value){
		$this->clickatell_api_id = $value;
	}
	
	function getClickatellApiId(){
		return $this->clickatell_api_id;
	}
	
	function to_utf16($text) {
		
		$out = '';
		
		$text = mb_convert_encoding($text, 'UTF-16', 'UTF-8');
		
		for ($i=0; $i<mb_strlen($text,'UTF-16'); $i++){
			$out .= bin2hex(mb_substr($text,$i,1,'UTF-16'));
		}
		
		return $out;
	}

	function sendClickatellSmsNotification(){
		
		// Required to handle international characters
		$message = $this->to_utf16($this->getSmsNotificationMessage());
		
		$url_params = array('user'=>$this->getClickatellUsername(),
							'password'=>$this->getClickatellPassword(),
							'api_id'=>$this->getClickatellApiId(),
							'to'=>$this->getSmsNotificationPhoneNumber(),
							'text'=>$message,
							'unicode'=>1,
							);

		$url = 'http://api.clickatell.com/http/sendmsg?'.http_build_query($url_params);
		
		$this->getCurlObj()->setCurlRequestUrl($url);
		
		$this->getCurlObj()->execCurl();
	}
}


class SmsNotificationTwilio extends SmsNotification{
	
	function setTwilioAccountSid($value){
		$this->twilio_account_sid = $value;
	}
	
	function getTwilioAccountSid(){
		return $this->twilio_account_sid;
	}

	function setTwilioAuthToken($value){
		$this->twilio_auth_token = $value;
	}
	
	function getTwilioAuthToken(){
		return $this->twilio_auth_token;
	}

	function setTwilioFromPhoneNumber($value){
		$this->twilio_from_phone_number = $value;
	}
	
	function getTwilioFromPhoneNumber(){
		return $this->twilio_from_phone_number;
	}
	
	function sendTwilioSmsNotification(){

		$url_params = array('To'=>$this->getSmsNotificationPhoneNumber(),
							'From'=>$this->getTwilioFromPhoneNumber(),
							'Body'=>$this->getSmsNotificationMessage()
							.' ' // <= do NOT delete this space: this unicode character forces Twilio to encode the message as Unicode and to handle the accented characters properly
							// ^-- do NOT delete this space: this unicode character forces Twilio to encode the message as Unicode and to handle the accented characters properly
							);

		$url = 'https://api.twilio.com/2010-04-01/Accounts/'.$this->getTwilioAccountSid().'/SMS/Messages.json';

		
		$this->getCurlObj()->setCurlRequestHeaders(array('Accept-Charset: utf-8'));

		$this->getCurlObj()->setCurlRequestPostType();
		
		$this->getCurlObj()->setCurlRequestContent(http_build_query($url_params));
		
		$this->getCurlObj()->setCurlUsername($this->getTwilioAccountSid());
		
		$this->getCurlObj()->setCurlPassword($this->getTwilioAuthToken());
		
		$this->getCurlObj()->setCurlRequestUrl($url);
		
		$this->getCurlObj()->execCurl();
	}
}
?>