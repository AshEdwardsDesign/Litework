<?php
/**********************************************************************************
 * Contact Form Generator is (c) Top Studio
 * It is strictly forbidden to use or copy all or part of an element other than for your 
 * own personal and private use without prior written consent from Top Studio http://topstudiodev.com
 * Copies or reproductions are strictly reserved for the private use of the person 
 * making the copy and not intended for a collective use.
 *********************************************************************************/

class cfgenwpApi{

	
	function __construct(){


		$this->icontact['applicationid'] = 'gqr8puRu31jN8NKwhEH0LrGAaInenEX3';
		
		$this->isPHP53 = version_compare(phpversion(), '5.3.0', '>=');
		
		$this->openSSlIsLoaded = extension_loaded('openssl') ? true : false;
		
		$this->curlIsLoaded = extension_loaded('curl') ? true : false;
		
		$this->soapIsLoaded = extension_loaded('soap') ? true : false;
		
		
		$this->error_messages['isPHP53'] = 'PHP 5.3 or higher is required to have this service working properly';
		
		$this->error_messages['openSSlIsLoaded'] = 'OpenSSL must be loaded on the server to have this service working properly';
		
		$this->error_messages['curlIsLoaded'] = 'cURL must be loaded on the server to have this service working properly';
		
		$this->error_messages['soapIsLoaded'] = 'SOAP must be loaded on the server to have this service working properly';
		
		$this->service_types = array('emaillist'=>array());

		$this->addService('aweber');
		$this->setServiceName('aweber', 'Aweber');
		$this->setServiceDir('aweber', 'aweber');
		$this->setServiceRequirements('aweber', array('curlIsLoaded'));
		$this->setServiceType('aweber', 'emaillist');

		$this->addService('campaignmonitor');
		$this->setServiceName('campaignmonitor', 'Campaign Monitor');
		$this->setServiceDir('campaignmonitor', 'campaignmonitor');
		$this->setServiceRequirements('campaignmonitor', array());
		$this->setServiceType('campaignmonitor', 'emaillist');
		
		$this->addService('constantcontact');
		$this->setServiceName('constantcontact', 'Constant Contact');
		$this->setServiceDir('constantcontact', 'Ctct');
		$this->setServiceRequirements('constantcontact', array('isPHP53', 'curlIsLoaded'));
		$this->setServiceType('constantcontact', 'emaillist');
		
		$this->addService('getresponse');
		$this->setServiceName('getresponse', 'GetResponse');
		$this->setServiceDir('getresponse', 'getresponse');
		$this->setServiceRequirements('getresponse', array('curlIsLoaded'));
		$this->setServiceType('getresponse', 'emaillist');
		/*
		$this->addService('googlecontacts');
		$this->setServiceName('googlecontacts', 'Google Contacts');
		$this->setServiceDir('googlecontacts', 'google-api-php-client-master');
		$this->setServiceRequirements('googlecontacts', array('curlIsLoaded'));
		$this->setServiceType('googlecontacts', 'emaillist');
		*/
		$this->addService('icontact');
		$this->setServiceName('icontact', 'iContact');
		$this->setServiceDir('icontact', 'icontact');
		$this->setServiceRequirements('icontact', array('curlIsLoaded'));
		$this->setServiceType('icontact', 'emaillist');
		
		$this->addService('mailchimp');
		$this->setServiceName('mailchimp', 'MailChimp');
		$this->setServiceDir('mailchimp', 'mailchimp');
		$this->setServiceRequirements('mailchimp', array('curlIsLoaded'));
		$this->setServiceType('mailchimp', 'emaillist');
		
		$this->addService('salesforce');
		$this->setServiceName('salesforce', 'Salesforce');
		$this->setServiceDir('salesforce', 'salesforce');
		$this->setServiceRequirements('salesforce', array('soapIsLoaded', 'openSSlIsLoaded'));
		$this->setServiceType('salesforce', 'emaillist');

	}
	
	function checkServiceRequirements($api_id){
		
		$status = true;
		
		$api_requirements = $this->service[$api_id]['requirements'];
		
		foreach($api_requirements as $requirement){
		
			if(!$this->{$requirement}){
				$status = false;
				break;
			}
		}
		
		if($status){
			return array('status'=>true);
		} else{
			$e = $this->getApiRequirementsError($api_requirements);
			return array('status'=>false, 'errors'=>$e);
		}
	}
	
	
	function getApiRequirementsError($param = array()){
		
		$e = array();
		
		foreach($param as $param_v){
			if(!$this->{$param_v}){
				$e[] = $this->error_messages[$param_v];
			}
		}
		
		return $e;
	}
	
	function addService($service){
		$this->service[$service] = array();
		$this->service[$service]['id'] = $service;
	}
	
	function setServiceRequirements($service, $requirements){
		$this->service[$service]['requirements'] = $requirements;
	}
	
	function setServiceName($service, $name){
		$this->service[$service]['name'] = $name;
	}
	
	function setServiceDir($service, $dir){
		$this->service[$service]['dir'] = $dir;
	}
	
	function setServiceType($service, $type){
		$this->service_types[$type][] = $service;
	}
	
	function getServiceName($service){
		return $this->service[$service]['name'];
	}
	
	function getServiceDir($service){
		return $this->service[$service]['dir'];
	}
}
?>