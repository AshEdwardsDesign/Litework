<?php
require 'sessionpath.php';

require '../inc/form-config.php';

require '../class/class.form.php';

$contactform_obj = new contactForm($cfg);

$json_error_array = array();

?>
<?php

/**
 * json error message for invalid captcha (captcha_img_string)
 * $json_error_array = array(); // in saveform, before the captcha error message also written in saveform
 */


/**
 * some basic security checks to prevent direct access
 * form-validation-constantcontact.php must also be updated
 */

if(
   !isset($contactform_obj) || !isset($_POST['form_values'])
   || (isset($contactform_obj) && !is_object($contactform_obj)) 
   )
{
	exit;
}

include('../class/class.cfgenwp.api.php');
$cfgenwpapi_obj = new cfgenwpApi();

// default json_response, no input field values ($contactform_obj->merge_post is empty)
$json_response = array('status'=>'nok', 'message'=>'');


function debugServiceSeparator($service_id, $open){
	
	global $contactform_obj;
	
	$log_separator = '-----------------------------------------------------------------------------';

	if($contactform_obj->cfg['debug']){
		
		echo "\r\n".$log_separator;
		
		if($open){
			echo "\r\n".$service_id."\r\n";
			echo "\r\n";
		}
	}
};

function getElementValue($element_id){

	global $element_ids_values;
	
	return $element_ids_values[$element_id]['element_value'][0];
}

function explodeEndValue($delimiter, $array){

	$explode = explode($delimiter, $array); // prevents "Only variables shoulds be Passed by reference" when using end(explode()) in EasyPHP
	
	$end = end($explode);
	
	return $end;
}

function foreachCleanConcat($delimiter, $array){

	$string = '';
	
	foreach($array as $value){
		$string .= $value.$delimiter; // if($value) not necessary, if $value = '', the delimiter will be deleted with substr anyway
	}
	
	$string = substr($string, 0, -strlen($delimiter));
	
	return $string;
}

// delete the files the user uploaded and then deleted
if(!empty($_POST['deleteuploadedfile'])){

	foreach($_POST['deleteuploadedfile'] as $value){
		
		if(in_array($value, $_SESSION['uploaded_files'])){
			@unlink('../upload/'.$contactform_obj->quote_smart($value));
		}
	}
}

if(!empty($_POST['form_values'])){

	foreach($_POST['form_values'] as $value){
		$contactform_obj->mergePost($value);
	}
}

$element_ids_values = array();

if(!empty($contactform_obj->merge_post)){
	
	foreach($contactform_obj->merge_post as $merge_post_value){
		
		$element_ids_values[$merge_post_value['element_id']]['element_value'][] = $merge_post_value['element_value'];
		$element_ids_values[$merge_post_value['element_id']]['elementlabel_value'] = $merge_post_value['elementlabel_value'];
		$element_ids_values[$merge_post_value['element_id']]['elementlabel_id'] = $merge_post_value['elementlabel_id'];
		
		
		$explode_element_id = explode('-', $merge_post_value['element_id']); // prevents "Only variables shoulds be Passed by reference" when using end(explode()) in EasyPHP
		$element_id_int = end($explode_element_id);

		$element_ids_values['element_id_int_val'][$element_id_int]['value'][] = $merge_post_value['element_value'];
	}
}

ksort($element_ids_values);
//print_r($contactform_obj->merge_post);
//print_r($element_ids_values);

// VALIDATION REQUIRED
if(!empty($cfg['formvalidation_required']) && !empty($contactform_obj->merge_post)){
	
	foreach($cfg['formvalidation_required'] as $value){

		if(!$element_ids_values[$value]['element_value'][0] && $element_ids_values[$value]['element_value'][0] !== '0'){
			
			$json_error_array[$value]['errormessage'] = $contactform_obj->cfg['form_errormessage_emptyfield'];
		}
	}
}

// VALIDATION EMAIL
if(!empty($cfg['formvalidation_email']) && !empty($contactform_obj->merge_post)){
	
	foreach($cfg['formvalidation_email'] as $value){
		
		if($element_ids_values[$value]['element_value'][0] && !$contactform_obj->isEmail($element_ids_values[$value]['element_value'][0])){
			
			$json_error_array[$value]['errormessage'] = $contactform_obj->cfg['form_errormessage_invalidemailaddress'];
		}
	}
}

// VALIDATION TERMS
if(!empty($cfg['formvalidation_terms']) && !empty($contactform_obj->merge_post)){
	
	foreach($cfg['formvalidation_terms'] as $value){
		
		if(!$element_ids_values[$value]['element_value'][0] && $element_ids_values[$value]['element_value'][0] !== '0'){
			
			$json_error_array[$value]['errormessage'] = $contactform_obj->cfg['form_errormessage_terms'];
		}
	}
}

// VALIDATION URL
if(!empty($cfg['formvalidation_url']) && !empty($contactform_obj->merge_post)){
	
	foreach($cfg['formvalidation_url'] as $value){
		
		$pattern_url = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';
			
		if($element_ids_values[$value]['element_value'][0] && !preg_match($pattern_url, $element_ids_values[$value]['element_value'][0])){
			
			$json_error_array[$value]['errormessage'] = $contactform_obj->cfg['form_errormessage_invalidurl'];
		}
	}
}

// FORMATTING JSON RESPONSE AND SENDING MAIL
if($json_error_array){

	if(isset($error_captcha)){
		
		if(!empty($contactform_obj->merge_post)){
			
			// pushing the captcha field properties into $contactform_obj->merge_post to manage the error message 
			// the error messages are displayed following the order of the inputs in the form with $contactform_obj->merge_post data
			// the captcha properties (id, label id) are not pushed into $contactform_obj->merge_post data (which is built on $_POST['form_values'] only)
			$contactform_obj->merge_post[] = array('element_id'=>$captcha_element_id);
			// ^-- the captcha is removed from merge_post with array_pop below
			
			$element_ids_values[$captcha_element_id]['elementlabel_id'] = $captcha_elementlabel_id;
		}
	}
	// print_r($contactform_obj->merge_post);
	
	
	if(!empty($contactform_obj->merge_post)){
		
		$element_json_error = array();
		
		// displays the error messages following the order of the inputs in the form
		foreach($contactform_obj->merge_post as $merge_post_value){
			
			if(isset($json_error_array[$merge_post_value['element_id']]['errormessage'])){
				
				$element_json_error[] = array('element_id'=>$merge_post_value['element_id'],
											  'elementlabel_id'=>$element_ids_values[$merge_post_value['element_id']]['elementlabel_id'],
											  'errormessage'=>$json_error_array[$merge_post_value['element_id']]['errormessage'],
											  );
			}
		}
		
		$json_response = array('status'=>'nok', 'message'=>$element_json_error);
	}
	
	
	if(isset($error_captcha)){
		// removing the captcha data to prevent having it displayed in the notification message
		array_pop($contactform_obj->merge_post);
	}
	
} else{
	
	if(!empty($contactform_obj->merge_post)){

		/**
		 * DATABASE CONNECTION
		 * 
		 * How to grab form values for database connection:
		 *
		 * $field_1 = $element_ids_values['cfg-element-xxxxx']['element_value'][0];
		 * 
		 * Replace cfg-element-xxxxx with the actual html id of the field in your form.
		 *
		 * For example, if the input you want to use has its html id set as cfg-element-1-1 , the variable must be set like this:
		 * $field_1 = $element_ids_values['cfg-element-1-1']['element_value'][0];
		 *
		 * Then you can use your own db code snippet to insert the value $field_1 in your database		
		 *
		 * 
		 */

		// PLACE YOUR DATABASE CONNECTION CODE SNIPPET HERE		

		if(!empty($cfg['database_host']) && !empty($cfg['database_name']) && !empty($cfg['database_login']) && !empty($cfg['database_table'])){
			
			if(!extension_loaded('pdo_mysql')){
				// DATABASE MAIL ERROR
				$contactform_obj->phpmailer_errornotification->Subject = 'Form '.$cfg['form_name'].' - Database connection error';
				$contactform_obj->phpmailer_errornotification->Body  = 'An error occured with the database connection.';
				$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'The PDO extension pdo_mysql is not loaded on your server.';
				$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'You need to install and activate the PDO extension in the PHP config file of your server in order to enable the database connection properly.';
				$contactform_obj->phpmailer_errornotification->Send();
			} else{

				try{
					// ERRMODE_WARNING to catch the exception when the host is invalid (an uncatchable warning notice would be thrown otherwise)
					// +@: http://stackoverflow.com/a/23288361/973237
					$pdo_obj = @new PDO('mysql:host='.$cfg['database_host'].';dbname='.$cfg['database_name'].(version_compare(PHP_VERSION, '5.3.6', '>') ? ';charset='.$cfg['database_table_charset'].'' : ''), 
										$cfg['database_login'], 
										$cfg['database_password'], 
										array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
					
					if(version_compare(PHP_VERSION, '5.3.6', '<')){
						$pdo_obj->exec('SET NAMES '.$cfg['database_table_charset']);
					}

				} catch(PDOException $pdo_error){
					// DATABASE MAIL ERROR
					$contactform_obj->phpmailer_errornotification->Subject = 'Form '.$cfg['form_name'].' - Database connection error';
					$contactform_obj->phpmailer_errornotification->Body  = 'An error occured with the database connection and the following error message has been returned:';
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".$pdo_error->getMessage();
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'It was not possible to connect to the database using the credentials below.';
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Database host: '.$cfg['database_host'];
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Database name: '.$cfg['database_name'];
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Database login: '.$cfg['database_login'];
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'You may want to double check your database credentials in the form settings in order to fix this problem.';
					$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'You can also change the database credentials in the form config file: cfgen-form/inc/form-config.php';
					$contactform_obj->phpmailer_errornotification->Send();
				}
				

				if(isset($pdo_obj)){ // $pdo_obj remains undefined if an exception is thrown

					$db_field_separator = ', ';

					// DATABASE CONCAT FIELD NAME + PDO PARAMS
					$db_field_name_concat = ''; // INSERT INTO $db_field_name_concat
					$db_field_value_concat = ''; // VALUES ($db_field_value_concat)
					
					$pdo_params = array();
					
					$pdo_element_id_i = 1; // That way the same element can be used in multiple columns (we could not do it by using element_id only)

					foreach($cfg['database_table_fields'] as $db_fields_k=>$db_fields_v){
						
						$db_field_name_concat .= $db_fields_v['table_field_id'].$db_field_separator;
						
						// PARAMS FOR ELEMENT
						if(isset($db_fields_v['element_id'])){

							$pdo_element_id = str_replace('-', '_', $db_fields_v['element_id'].'_'.$pdo_element_id_i);

							$pdo_element_value = $element_ids_values[$db_fields_v['element_id']]['element_value'];

							$pdo_params[$pdo_element_id] = implode(';', $pdo_element_value);

							if(!$pdo_params[$pdo_element_id] && isset($db_fields_v['table_field_default_value']) && $db_fields_v['table_field_default_value'] === 'NULL'){
								$pdo_params[$pdo_element_id] = null;
							}
							
							$db_field_value_concat .= ':'.$pdo_element_id.$db_field_separator;
						}
						
						// PARAMS FOR ELEMENT
						if(isset($db_fields_v['preset_id'])){
							
							$preset_id = $db_fields_v['preset_id'];
							
							$pdo_element_id = str_replace('-', '_', $db_fields_v['preset_id'].'_'.$pdo_element_id_i);
							
							
							if($preset_id === 'ipaddress'){
								$pdo_bind = true;
								$pdo_params[$pdo_element_id] = $_SERVER['REMOTE_ADDR'];
							}
							
							if($preset_id === 'form_name'){
								$pdo_bind = true;
								$pdo_params[$pdo_element_id] = $cfg['form_name'];
							}
							
							if($preset_id === 'form_id'){
								$pdo_bind = true;
								$pdo_params[$pdo_element_id] = $cfg['form_id'];
							}
							
							if($preset_id === 'form_url'){
								$pdo_bind = true;
								$pdo_params[$pdo_element_id] = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
							}
							
							if($preset_id === 'date'){
								$pdo_bind = false;
								$preset_value = 'CURDATE()';
							}
							
							if($preset_id === 'datetime'){
								$pdo_bind = false;
								$preset_value = 'NOW()';
							}
							
							if($preset_id === 'time'){
								$pdo_bind = false;
								$preset_value = 'CURTIME()';
							}
							
							if($preset_id === 'utc_timestamp'){
								$pdo_bind = false;
								$preset_value = 'UTC_TIMESTAMP()';
							}
							
							if($preset_id === 'unix_timestamp'){
								$pdo_bind = false;
								$preset_value = 'UNIX_TIMESTAMP()';
							}
							
							if($pdo_bind){
								$db_field_value_concat .= ':'.$pdo_element_id.$db_field_separator;
							} else{
								$db_field_value_concat .= $preset_value.$db_field_separator;
							}
							
						}
						
						$pdo_element_id_i++;
						
					}

					$db_field_name_concat = substr($db_field_name_concat, 0, -strlen($db_field_separator));

					$db_field_value_concat = substr($db_field_value_concat, 0, -strlen($db_field_separator));


					// DATABASE PREPARE INSERT
					$req = 'INSERT INTO '.$cfg['database_table'].' ('.$db_field_name_concat.')'
							  .' VALUES ('.$db_field_value_concat.')';
					
					$prep_req = $pdo_obj->prepare($req);
					
					// echo $req; print_r($pdo_params);

					// DATABASE EXECUTE INSERT
					try{
						$prep_req->execute($pdo_params);
					} catch(PDOException $pdo_error){
						// ^-- Not using if(!$prep_req->execute($pdo_params)) + $prep_req->errorInfo() because errors are managed with exceptions: ERRMODE_EXCEPTION

						$pdo_error_info = $pdo_error->getMessage();

						// DATABASE MAIL ERROR
						$contactform_obj->phpmailer_errornotification->Subject = 'Form '.$cfg['form_name'].' - Database SQL error';
						$contactform_obj->phpmailer_errornotification->Body  = 'An error occured with the database connection and the following error message was returned:';
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".$pdo_error_info;
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n";
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'SQL request:';
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".$req;
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n";
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Table name:';
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".$cfg['database_table'];
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n";
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Field names:';
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".$db_field_name_concat;
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n";
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'Field values:';
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".print_r($pdo_params, true);
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n";
						$contactform_obj->phpmailer_errornotification->Body .= "\r\n".'You may want to double check if the table name and if table field names match with what you have set in the form settings.';
						$contactform_obj->phpmailer_errornotification->Send();
					}
				} //if(isset($pdo_obj))
			} //extension_loaded('pdo_mysql')
		}
		
		// SCREEN SIZE
		if(isset($_POST['screen_width']) && ctype_digit($_POST['screen_width']) && isset($_POST['screen_height']) && ctype_digit($_POST['screen_height'])){
			
			$contactform_obj->cfg['screen_width'] = $_POST['screen_width'];
			$contactform_obj->cfg['screen_height'] = $_POST['screen_height'];
		}		
		
		if($cfg['usernotification_inputid']){
			
			$contactform_obj->usernotification_emailaddress = $element_ids_values[$cfg['usernotification_inputid']]['element_value'][0];
		}		
		
		// REPLACE BRACES		
		$adminnotification_subject_beforepregreplace = isset($contactform_obj->cfg['adminnotification_subject']) ? $contactform_obj->cfg['adminnotification_subject'] : '';
		$usernotification_subject_beforepregreplace = isset($contactform_obj->cfg['usernotification_subject']) ? $contactform_obj->cfg['usernotification_subject'] : '';
		$usernotification_message_beforepregreplace = isset($contactform_obj->cfg['usernotification_message']) ? $contactform_obj->cfg['usernotification_message'] : '';
		$sms_admin_notification_message = isset($contactform_obj->cfg['sms_admin_notification_message']) ? $contactform_obj->cfg['sms_admin_notification_message'] : '';

		$pattern_braces = '/{([^}]*)}/';

		foreach(array('adminnotification_subject', 'usernotification_subject', 'usernotification_message', 'sms_admin_notification_message') as $contactform_obj_findandreplace_braces)
		{
			preg_match_all($pattern_braces, $contactform_obj->cfg[$contactform_obj_findandreplace_braces], $matches_braces);
			//	print_r($matches_braces[1]);
			/**
			Array
				(
					[0] => ipaddress
					[1] => First Name|7
					[2] => Last Name|6
				)
			 */
			foreach($matches_braces[1] as $match_value)
			{
				// prevents 'Notice: Undefined index: 195' if an element is in braces but does not exist in the form
				if(isset($element_ids_values['element_id_int_val'][explodeEndValue('|', $match_value)])){
					
					$replace_braces = foreachCleanConcat(' ', $element_ids_values['element_id_int_val'][explodeEndValue('|', $match_value)]['value']);
					$contactform_obj->cfg[$contactform_obj_findandreplace_braces] = str_replace('{'.$match_value.'}', $replace_braces, $contactform_obj->cfg[$contactform_obj_findandreplace_braces]);
				}
				
				if($match_value == 'ipaddress'){
					$contactform_obj->cfg[$contactform_obj_findandreplace_braces] = str_replace('{ipaddress}', $_SERVER['REMOTE_ADDR'], $contactform_obj->cfg[$contactform_obj_findandreplace_braces]);
				}
				
				if($match_value == 'form_id'){
					$contactform_obj->cfg[$contactform_obj_findandreplace_braces] = str_replace('{form_id}', $contactform_obj->cfg['form_id'], $contactform_obj->cfg[$contactform_obj_findandreplace_braces]);
				}
				
				if($match_value == 'form_name'){
					$contactform_obj->cfg[$contactform_obj_findandreplace_braces] = str_replace('{form_name}', $contactform_obj->cfg['form_name'], $contactform_obj->cfg[$contactform_obj_findandreplace_braces]);
				}
				
				if($match_value == 'url'){
					$contactform_obj->cfg[$contactform_obj_findandreplace_braces] = str_replace('{url}', $contactform_obj->url, $contactform_obj->cfg[$contactform_obj_findandreplace_braces]);
				}
			}
		}


		// ADMIN MAIL NOTIFICATION
		$contactform_obj->sendAdminNotification();
		

		// ADMIN SMS NOTIFICATION CLICKATELL
		if(!empty($cfg['sms_admin_notification_gateway_id']) && !empty($cfg['sms_admin_notification_to_phone_number'])){
		
			require '../class/class.curl.php';
			
			require '../class/class.notification.sms.php';
			
			if($cfg['sms_admin_notification_gateway_id'] === 'clickatell'){

				if(!empty($cfg['sms_admin_notification_username']) && !empty($cfg['sms_admin_notification_password']) && !empty($cfg['sms_admin_notification_api_id'])){
					
					$clickatell_sms_admin_notification = new SmsNotificationClickatell(new Curl());
					
					// Using $contactform_obj and not $cfg because this is $contactform_obj that is modified when parsing {} for text replacement
					$clickatell_sms_admin_notification->setSmsNotificationPhoneNumber($contactform_obj->cfg['sms_admin_notification_to_phone_number']);
					
					$clickatell_sms_admin_notification->setSmsNotificationMessage($contactform_obj->cfg['sms_admin_notification_message']);
					
					$clickatell_sms_admin_notification->setClickatellUsername($contactform_obj->cfg['sms_admin_notification_username']);
					
					$clickatell_sms_admin_notification->setClickatellPassword($contactform_obj->cfg['sms_admin_notification_password']);
					
					$clickatell_sms_admin_notification->setClickatellApiId($contactform_obj->cfg['sms_admin_notification_api_id']);
					
					$clickatell_sms_admin_notification->sendClickatellSmsNotification();
				}
			}

		
			// ADMIN SMS NOTIFICATION TWILIO
			if($cfg['sms_admin_notification_gateway_id'] === 'twilio'){
			
				if(!empty($cfg['sms_admin_notification_account_sid']) && !empty($cfg['sms_admin_notification_auth_token']) && !empty($cfg['sms_admin_notification_from_phone_number'])){
					
					$twilio_sms_admin_notification = new SmsNotificationTwilio(new Curl());
					
					// Using $contactform_obj and not $cfg because this is $contactform_obj that is modified when parsing {} for text replacement
					$twilio_sms_admin_notification->setSmsNotificationPhoneNumber($contactform_obj->cfg['sms_admin_notification_to_phone_number']);
					
					$twilio_sms_admin_notification->setSmsNotificationMessage($contactform_obj->cfg['sms_admin_notification_message']);
					
					$twilio_sms_admin_notification->setTwilioAccountSid($contactform_obj->cfg['sms_admin_notification_account_sid']);
					
					$twilio_sms_admin_notification->setTwilioAuthToken($contactform_obj->cfg['sms_admin_notification_auth_token']);
					
					$twilio_sms_admin_notification->setTwilioFromPhoneNumber($contactform_obj->cfg['sms_admin_notification_from_phone_number']);
					
					$twilio_sms_admin_notification->sendTwilioSmsNotification();
				}
			}

		}
		

		
		// USER NOTIFICATION
		if($contactform_obj->cfg['usernotification_activate'] && $contactform_obj->cfg['usernotification_inputid'] && $contactform_obj->usernotification_emailaddress){		
			$contactform_obj->sendUserNotification();
		}
		
		
		$redirect_url = $contactform_obj->cfg['form_redirecturl'] ? $contactform_obj->cfg['form_redirecturl'] : '';

		
		// VALIDATION MESSAGE
		$json_response = array('status'=>'ok', 'message'=>$contactform_obj->cfg['form_validationmessage'], 'redirect_url'=>$redirect_url);

		
		/**************************************************************************************
		 * AWEBER API
		 **************************************************************************************/
		if(!empty($cfg['aweber']['consumerkey']) 
			&& !empty($cfg['aweber']['consumersecret']) 
			&& !empty($cfg['aweber']['accesstokenkey']) 
			&& !empty($cfg['aweber']['accesstokensecret']) 
			&& !empty($cfg['aweber']['lists'])
			)
		{
			$service_id = 'aweber';
			
			debugServiceSeparator($service_id, true);
			
			
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
			
			if($service_requirements_status['status']){
	
				include('../api/aweber/aweber_api.php');
				
				try{
					$aweber_api = new AWeberAPI($cfg[$service_id]['consumerkey'], $cfg[$service_id]['consumersecret']);
					
					$aweber_account = $aweber_api->getAccount($cfg[$service_id]['accesstokenkey'], $cfg[$service_id]['accesstokensecret']);
					//print_r($aweber_account);
				
					$aweber_account_id = $aweber_account->id;
					
					foreach($cfg[$service_id]['lists'] as $list_v){
					
						$list_id = $list_v['list_id'];
						
						$aweber_contact = array();
						$aweber_contact['email'] = '';
						$aweber_contact['name'] = '';
						$aweber_contact['ipaddress'] = $_SERVER['REMOTE_ADDR'];
								
						// FIELDS
						if(!empty($list_v['fields'])){
							
							foreach($list_v['fields'] as $field_v){
								
								if($field_v['list_field_id'] == 'email' || $field_v['list_field_id'] == 'name'){
									
									$aweber_contact[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
								}
								
								if($field_v['list_field_id'] != 'email' && $field_v['list_field_id'] != 'name'){
									
									if($element_ids_values[$field_v['element_id']]['element_value'][0]){
										
										// custom_fields must be set only there are custom field values to send
										// sending empty $aweber_contact['custom_field'] cause AWeberAPIException: custom_fields: ([], <type 'dict'>, 'custom_fields') 
										if(!isset($aweber_contact['custom_fields'])){
											$aweber_contact['custom_fields'] = array();
										}
										
										$aweber_contact['custom_fields'][$field_v['list_field_id']] = getElementValue($field_v['element_id']);
									}
								}
							}
						
							$aweber_list = $aweber_account->loadFromUrl("/accounts/{$aweber_account_id}/lists/{$list_id}");
							
							// if $aweber_contact['email'] = '' , find() will return all the contacts in the list
							if($aweber_contact['email']){
								
								$aweber_search_contact = $aweber_list->subscribers->find(array('email'=>$aweber_contact['email']));
										
								if($contactform_obj->cfg['debug']){
									echo 'SEARCH RESULTS'."\r\n";
									print_r($aweber_search_contact);
								}
								
								// ADD CONTACT
								if(isset($aweber_search_contact->data['entries']) && !$aweber_search_contact->data['entries']){
									
									try{
										
										$aweber_new_subscriber = $aweber_list->subscribers->create($aweber_contact);
										
										if($contactform_obj->cfg['debug']){
											echo 'ADD CONTACT'."\r\n";
											print_r($aweber_new_subscriber);
										}
										
									} catch(AWeberAPIException $e){
										
										$admin_api_error[$service_id]['error_message'] = $e->message;
										
										if($contactform_obj->cfg['debug']){
											echo 'AWEBER ADD CONTACT ERROR'."\r\n";
											print_r($e);
										}
											
										/*
										Invalid list id:
										$e->type = NotFoundError
										$e->message = Object: None, name: ''
										We apply a custom error message
										*/
										
										if(!isset($aweber_list)){
											$admin_api_error[$service_id]['error_message'] = 'Invalid list ID : '.$list_id;
										} else{
											$admin_api_error[$service_id]['error_message'] = $e->message;
										}
										
									} // catch
								}
								// UPDATE CONTACT
								else{
									if($list_v['updateexistingcontact']){

										foreach($aweber_search_contact as $aweber_contact_obj){
											
											if($contactform_obj->cfg['debug']){
												echo 'UPDATE CONTACT'."\r\n";
												print_r($aweber_contact_obj);
											}
											
											if($aweber_contact['name']){ // prevents "AWeberAPIException: name: (u'', 1)"
												$aweber_contact_obj->name = $aweber_contact['name'];
											}
											
											if(!empty($aweber_contact['custom_fields'])){
												// && $aweber_contact['custom_fields']: sending empty $aweber_contact['custom_field'] cause AWeberAPIException: The API is temporarily unavailable. please try again.
												$aweber_contact_obj->custom_fields = $aweber_contact['custom_fields'];
											}
											
											try{
												// check on is_verified to prevent "AWeberAPIException: An unconfirmed subscriber cannot be modified."
												if($aweber_contact_obj->is_verified == 1){
													$aweber_contact_obj->save();
												}
												
											} catch(AWeberAPIException $e){
												
												$admin_api_error[$service_id]['error_message'] = $e->message;
												
												if($contactform_obj->cfg['debug']){
													echo 'AWEBER UPDATE CONTACT ERROR'."\r\n";
													print_r($e);
												}
											} // foreach search res
										} // if updateexistingcontact
									}

								} // else add update
							} // if email
							else{
								$admin_api_error[$service_id]['error_message'] = 'Invalid email address, email address is empty.';
							}
						} // if fields
					} // foreach list
				} // try connection
					
				catch(AWeberAPIException $e){
					
					if($contactform_obj->cfg['debug']){
						echo 'AWEBER CONNECTION ERROR';
						print_r($e);
					}
					
					$admin_api_error[$service_id]['error_message'] = $e->message;
					
				} // catch
				
			} // if api works
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);				
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // AWEBER
		
		
		/**************************************************************************************
		 * CAMPAIGN MONITOR API
		 **************************************************************************************/
		if(!empty($cfg['campaignmonitor']['apikey']) 
			&& !empty($cfg['campaignmonitor']['lists']))
		{
			$service_id = 'campaignmonitor';
			
			debugServiceSeparator($service_id, true);
			
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
			
			if($service_requirements_status['status']){	
			
				include('../api/campaignmonitor/csrest_subscribers.php');
				
				
				$campaignmonitor_auth = array('api_key' => $cfg[$service_id]['apikey']);	
	
				foreach($cfg[$service_id]['lists'] as $list_v){
				
					$list_id = $list_v['list_id'];					
					
					$campaignmonitor_contact = array();
					
					/*
					* If the subscriber is in an inactive state or has previously been unsubscribed and you specify the Resubscribe input value as true,
					* they will be re-added to the list. Therefore, this method should be used with caution and only where suitable.
					* If Resubscribe is specified as false, the subscriber will not be re-added to the active list.
					*/
					$campaignmonitor_contact['Resubscribe'] = true;
					
					$campaignmonitor_contact['CustomFields'] = array();
					
					// FIELDS
					if(!empty($list_v['fields'])){
						
						foreach($list_v['fields'] as $field_v){
							
							if($field_v['list_field_id'] == 'EmailAddress' || $field_v['list_field_id'] == 'Name'){
								// 'Name' IS NOT a mandatory field
								$campaignmonitor_contact[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
							}
							
							if($field_v['list_field_id'] != 'EmailAddress' && $field_v['list_field_id'] != 'Name'){
								$campaignmonitor_contact['CustomFields'][] = array('Key'=>$field_v['list_field_id'],
																				   'Value'=>getElementValue($field_v['element_id'])
																				   );
							}
						}
					
						$wrap = new CS_REST_Subscribers($list_id, $campaignmonitor_auth);

						if(!$list_v['updateexistingcontact']){

							$cm_search_res = $wrap->get($campaignmonitor_contact['EmailAddress']);

							if($contactform_obj->cfg['debug']){
								echo 'SEARCH RESULTS '."\r\n";
								print_r($cm_search_res); echo "\r\n";
							}
						}
						
						/**
						 * Error response 203 returns : Subscriber not in list or has already been removed.
						 * Error response 203 : Email Address does not belong to the list. Subscriber not updated.
						 * https://www.campaignmonitor.com/api/subscribers/#updating_a_subscriber
						 */

						if((isset($cm_search_res) && isset($cm_search_res->response->Code) && $cm_search_res->response->Code == 203) 
							|| $list_v['updateexistingcontact']){

							// If the subscriber (email address) already exists, their name and any custom field values are updated with whatever is passed in.
							$result = $wrap->add($campaignmonitor_contact);

							if($contactform_obj->cfg['debug']){
								echo 'ADD/UPDATE CONTACT'."\r\n";
								print_r($result);
							}
							
							if(!$result->was_successful()){
								
								if(isset($result->response->Code)){
									$admin_api_error[$service_id]['error_code'] = $result->response->Code;
								}
								
								if(!isset($result->response->Code) && $result->http_status_code == '400'){
									$admin_api_error[$service_id]['error_code'] = $result->http_status_code;
								}
							}
						}						
					} // if fields
				} // foreach list
			}
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // CAMPAIGN MONITOR
		
		
		/**************************************************************************************
		 * CONSTANTCONTACT API
		 **************************************************************************************/
		if(!empty($cfg['constantcontact']['apikey']) 
			&& !empty($cfg['constantcontact']['accesstoken']) 
			&& !empty($cfg['constantcontact']['lists']))
		{
			$service_id = 'constantcontact';
			
			debugServiceSeparator($service_id, true);
			
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
		
			if($service_requirements_status['status']){
				include('form-validation-constantcontact.php');
			} else{
				$admin_api_error['constantcontact']['error_message'] = implode("\r\n", $service_requirements_status['errors']);
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // CONSTANTCONTACT
		
		
		/**************************************************************************************
		 * GETRESPONSE API
		 **************************************************************************************/
		if(!empty($cfg['getresponse']['apikey']) 
			&& !empty($cfg['getresponse']['lists']))
		{
			$service_id = 'getresponse';
			
			debugServiceSeparator($service_id, true);
			
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
			
			if($service_requirements_status['status']){	
			
				require ('../api/getresponse/GetResponseAPI.class.php');
	
	
				$getresponse_api = new GetResponse($cfg[$service_id]['apikey']);
				
				$getresponse_ping = $getresponse_api->ping();
			
			
				if($getresponse_ping == 'pong'){
					
					foreach($cfg[$service_id]['lists'] as $list_v){
					
						$list_id = $list_v['list_id'];						
						
						$getresponse_contact = array();
						$getresponse_contact['email'] = '';
						$getresponse_contact['name'] = '';
						$getresponse_contact['customfields'] = array();
				
						// FIELDS
						if(!empty($list_v['fields'])){
							
							foreach($list_v['fields'] as $field_v){
								
								if($field_v['list_field_id'] == 'email' || $field_v['list_field_id'] == 'name'){
									// 'name' IS a mandatory field
									$getresponse_contact[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
								}
								
								if($field_v['list_field_id'] != 'email' && $field_v['list_field_id'] != 'name'){

									// If a custom field is set with an empty value, the contact won't be added
									// Any custom field that is added must have a value in order to add the contact in the list
									if($element_ids_values[$field_v['element_id']]['element_value'][0]){

										// for customfields in add procedure
										$getresponse_contact['customfields'][] = array(
																						'name'=>$field_v['list_field_id'],
																						'content'=>getElementValue($field_v['element_id'])
																					   );

										// for customfields in update procedure
										$gr_update_contact_customs[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
									}
								}
							}
							
							$getresponse_add = '';
							
							// SEARCH CONTACT
							if($getresponse_contact['email'] && $getresponse_contact['name']){
								
								$gr_search_res = $getresponse_api->getContactsByEmail($getresponse_contact['email'], array($list_id), $operator = 'EQUALS');
								
								if($contactform_obj->cfg['debug']){
									
									echo 'SEARCH RESULTS in (list_id)'.$list_id."\r\n";
	
									print_r($gr_search_res); echo "\r\n";
								}
								
								
								$gr_search_res_arr = (Array)$gr_search_res;
								// ^-- we use a temp variable with the search results casted as an array because $gr_search_res is an object and it is not possible to check if an object is empty (if empty: add, if not: update)
								
								// ADD
								if(!$gr_search_res_arr){

									$getresponse_add = $getresponse_api->addContact($list_id, $getresponse_contact['name'], $getresponse_contact['email'], $action = 'standard', $cycle_day = 0, $getresponse_contact['customfields']);
									
									if($contactform_obj->cfg['debug']){
										echo 'ADD CONTACT '."\r\n";
										print_r($getresponse_add); echo "\r\n";
									}
									
									if(!$getresponse_add){
										$admin_api_error[$service_id]['error_message'] = 'Add contact, unknown error.';
									}
								}
								// UPDATE
								else{
									if($list_v['updateexistingcontact']){

										/*
										$gr_search_res:							
										stdClass Object
										(
											[xyz] => stdClass Object
												(
													[email] => x@x.com
												)
										
										)
										contact id is $gr_search_res->xyz: the only way to get the contact id is using an array
										*/
										
										$gr_contact_res = (Array)$gr_search_res;

										$gr_contact_id = key($gr_contact_res);
										
										$gr_update_name = $getresponse_api->setContactName($gr_contact_id, $getresponse_contact['name']);
											
										/*
										http://apidocs.getresponse.com/en/api/1.5.0/Contacts/set_contact_name
										success: ->updated = 1
										*/
										
										if($contactform_obj->cfg['debug']){
											echo 'UPDATE CONTACT '."\r\n";
											echo 'gr_update_name'; echo "\r\n";
											print_r($gr_update_name); echo "\r\n";
										}

										if(!$gr_update_name){
											$admin_api_error[$service_id]['error_message'] = 'Update contact name, unknown error.';
										}
										
										if($gr_update_name){

											if(isset($gr_update_contact_customs)){
												// ^-- $gr_update_contact_customs must be an array or the api method would throw the error "Second argument must be an array"

												$gr_update_customs_res = $getresponse_api->setContactCustoms($gr_contact_id, $gr_update_contact_customs);
												/*
												http://apidocs.getresponse.com/en/api/1.5.0/Contacts/set_contact_customs
												success: ->updated = 2
												added: ->added = 1
												deleted: ->deleted = 1
												*/
												
												if($contactform_obj->cfg['debug']){
													echo 'gr_update_customs_res '."\r\n";
													print_r($gr_update_customs_res); echo "\r\n";
												}
			
												if(!$gr_update_customs_res){
													$admin_api_error[$service_id]['error_message'] = 'Update contact customs, unknown error.';
												}
											}

										}
									} // if updateexistingcontact
								}
							} else{
								if(!$getresponse_contact['email']){
									$admin_api_error[$service_id]['error_message'] = 'Invalid email address, email address is empty.';
								}
								if(!$getresponse_contact['name']){
									$admin_api_error[$service_id]['error_message'] = 'Invalid name, name is empty.';
								}
							} // if email && name						
						} // if fields
					} // foreach list
				}
				else{
					$admin_api_error[$service_id]['error_message'] = 'Invalid API key';
				}
			}
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);
			} // if/else service status
			
			debugServiceSeparator($service_id, false);
		} // GETRESPONSE
		
		
		/**************************************************************************************
		 * ICONTACT API
		 **************************************************************************************/
		if(!empty($cfg['icontact']['username']) 
			&& !empty($cfg['icontact']['applicationpassword']) 
			&& !empty($cfg['icontact']['lists'])
			)
		{	
			$service_id = 'icontact';
			
			debugServiceSeparator($service_id, true);
		
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
			
			if($service_requirements_status['status']){
				
				include('../api/icontact/iContactApi.php');
				
				try{
					iContactApi::getInstance()->setConfig(array('appId' => $cfgenwpapi_obj->icontact['applicationid'], 'apiUsername' => $cfg[$service_id]['username'], 'apiPassword' => $cfg[$service_id]['applicationpassword']));
				} catch(Exception $e){
						
					$icontact_error = $oiContact->getErrors();
						
					$error = $icontact_error[0];
						
					$admin_api_error[$service_id]['error_message'] = $icontact_error[0];
					
					if($contactform_obj->cfg['debug']){
						echo 'iContact ERROR'."\r\n";
						print_r($icontact_error);
					}
						
				} // try iContact connect
				
				$oiContact = iContactApi::getInstance();
					
				$ic_makecall_url_prefix = '/a/'.$oiContact->setAccountId().'/c/'.$oiContact->setClientFolderId().'/contacts';
			
				foreach($cfg[$service_id]['lists'] as $list_v){
				
					$list_id = $list_v['list_id'];
					
					$api_contact_email = '';
						
					$ic_contact_id = ''; // returned when adding updating the contact into the account, this id is used to subscribe the contact to a list
					
					// FIELDS
					if(isset($list_v['fields']) && $list_v['fields']){
						
						foreach($list_v['fields'] as $field_v){
						
							$ic_contact[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
							
							if($field_v['list_field_id'] == 'email'){
								$api_contact_email = getElementValue($field_v['element_id']);
							}
						}
							
						// Default values for add and update
						// If the prefix length is > 6 characters, the contact is not added
						$ic_contact['prefix'] = (isset($ic_contact['prefix']) ? substr($ic_contact['prefix'], 0, 6) : '');
						$ic_contact['firstName'] = (isset($ic_contact['firstName']) ? $ic_contact['firstName'] : null);
						$ic_contact['lastName'] = (isset($ic_contact['lastName']) ? $ic_contact['lastName'] : null);
						// If the suffix length is > 6 characters, the contact is not added
						$ic_contact['suffix'] = (isset($ic_contact['suffix']) ? substr($ic_contact['suffix'], 0, 6) : null);
						$ic_contact['street'] = (isset($ic_contact['street']) ? $ic_contact['street'] : null);
						$ic_contact['street2'] = (isset($ic_contact['street2']) ? $ic_contact['street2'] : null);
						$ic_contact['city'] = (isset($ic_contact['city']) ? $ic_contact['city'] : null);
						// If the state length is > 10 characters, the contact is not added
						$ic_contact['state'] = (isset($ic_contact['state']) ? substr($ic_contact['state'], 0, 10) : null);
						// If the zip length is > 10 characters, the contact is not added
						$ic_contact['postalCode'] = (isset($ic_contact['postalCode']) ? substr($ic_contact['postalCode'], 0, 10) : null);
						// If the phone length is > 20 characters, the contact is not added
						$ic_contact['phone'] = (isset($ic_contact['phone']) ? substr($ic_contact['phone'], 0, 20) : null);
						// If the fax length is > 20 characters, the contact is not added
						$ic_contact['fax'] = (isset($ic_contact['fax']) ? substr($ic_contact['fax'], 0, 20) : null);
						$ic_contact['business'] = (isset($ic_contact['business']) ? $ic_contact['business'] : null);;
							
							
						if($contactform_obj->cfg['debug']){
							echo 'iContact CONTACT DATA TO SEND '."\r\n";
							print_r($ic_contact);
						}
						
						if($contactform_obj->cfg['debug']){
							echo 'SEARCH CONTACT'."\r\n";
						}
							
						if($api_contact_email){
								
							$ic_search_contact = $oiContact->makeCall($ic_makecall_url_prefix.'?status=total&email='.$api_contact_email);
							
							if($contactform_obj->cfg['debug']){
								echo 'SEARCH RESULTS'."\r\n";
								print_r($ic_search_contact);
							}
								
							if(!$ic_search_contact->contacts){// no search results
									
								// ADD CONTACT
								try{
									// if '' after array($ic_contact) : $ic_contact_id = $ic_contact->contacts[0]->contactId;
									// $ic_add_contact is null if the call fails
									$ic_add_contact = $oiContact->makeCall($ic_makecall_url_prefix, 'POST', array($ic_contact), 'contacts');
									
									if($contactform_obj->cfg['debug']){
										echo 'ADD CONTACT '."\r\n";
										print_r($ic_add_contact);
									}
									
									/*			
									$ic_add_contact = $oiContact->addContact($api_contact_email,'normal',$ic_contact['prefix'],$ic_contact['firstName'],$ic_contact['lastName'],$ic_contact['suffix'],$ic_contact['street'],$ic_contact['street2'],
																			$ic_contact['city'],$ic_contact['state'],$ic_contact['postalCode'],$ic_contact['phone'],$ic_contact['fax'],$ic_contact['business']);
									*/
									
									// $ic_add_contact is null if the contact is not created (invalid email address)
									if(isset($ic_add_contact[0]->contactId) && $ic_add_contact[0]->contactId){
										$ic_contact_id = $ic_add_contact[0]->contactId;
									}
									
									
								} catch(Exception $e){
								
									$icontact_error = $oiContact->getErrors();
									
									$admin_api_error[$service_id]['error_message'] = 'Add contact error.'."\r\n".$icontact_error[0];
									
								} // try add contact
								
							} else{
								
								if($list_v['updateexistingcontact']){
									
									// UPDATE CONTACT
									try{
										// if '' after array($ic_contact) : $ic_contact_id = $ic_update_contact->contact->contactId;
										// $ic_update_contact is null if the call fails
										$ic_update_contact = $oiContact->makeCall($ic_makecall_url_prefix.'/'.$ic_search_contact->contacts[0]->contactId, 'POST', $ic_contact, 'contact');
										
										if(isset($ic_update_contact->contactId) && $ic_update_contact->contactId){
											$ic_contact_id = $ic_update_contact->contactId;
										}
										
										if($contactform_obj->cfg['debug']){
											echo 'UPDATE CONTACT '.$ic_contact_id."\r\n";
											print_r($ic_update_contact);
										}
										
										/*
										The wrapper method updateContact does not work (the contact data is not updated)
										$ic_update_contact = $oiContact->updateContact($ic_search_contact->contacts[0]->contactId,$api_contact_email,$ic_contact['prefix'],$ic_contact['firstName'],
																					$ic_contact['lastName'],$ic_contact['suffix'],$ic_contact['street'],$ic_contact['street2'],$ic_contact['city'],
																					$ic_contact['state'],$ic_contact['postalCode'],$ic_contact['phone'],$ic_contact['fax'],$ic_contact['business']);
										*/
									} catch(Exception $e){
					
										$icontact_error = $oiContact->getErrors();
										
										$admin_api_error[$service_id]['error_message'] = 'Update contact error.'."\r\n".$icontact_error[0]."\r\n";
										
									} // try update contact
								} // if update existing contact
							} // if/else search res
							
							if(isset($ic_contact_id)){
							
								// Subscribe contact to list : http://developer.icontact.com/documentation/subscriptions/
								if(!empty($list_v['groups']) 
								&& (isset($ic_add_contact) || $list_v['updateexistingcontact'])
								){
								
									foreach($list_v['groups'] as $group_v){
																			
										$ic_subscribe = $oiContact->subscribeContactToList($ic_contact_id, $group_v, 'normal');
											
										if($contactform_obj->cfg['debug']){
											echo 'ADD CONTACT TO GROUP '.$group_v."\r\n";
											print_r($ic_subscribe);
										}
										
										/**
										 * empty $ic_subscribe does not necessarily means there is an error:
										 * $ic_subscribe is an empty array if subscription fails on add
										 * $ic_subscribe is an empty array if the user is already associated with this group/list (may happen when updating a contact)
										 */
										
									} // foreach group
								} // if groups
							} //if $ic_contact_id
						} // if $api_contact_email
						else{
							$admin_api_error[$service_id]['error_message'] = 'Invalid email address, email address is empty.';												
						}
					} // if fields
				} // foreach list
			}
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);
							
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // ICONTACT
		
		
		/**************************************************************************************
		 * MAILCHIMP API
		 **************************************************************************************/
		if(!empty($cfg['mailchimp']['apikey']) 
			&& !empty($cfg['mailchimp']['lists']) 
			)
		{
			$service_id = 'mailchimp';
			
			debugServiceSeparator($service_id, true);
		
			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);
			
			if($service_requirements_status['status']){
	
				
				include('../api/mailchimp/Mailchimp.php');
				
				$mc = new Mailchimp($cfg[$service_id]['apikey']);
				
				foreach($cfg[$service_id]['lists'] as $list_v){
				
					$list_id = $list_v['list_id'];
					
					$merge_vars = array();
						
					// FIELDS
					if(isset($list_v['fields']) && $list_v['fields']){
						
						foreach($list_v['fields'] as $field_v){
							
							if($field_v['list_field_id'] == 'EMAIL'){
								$api_contact_email = getElementValue($field_v['element_id']);
							}
							
							if($field_v['list_field_id'] != 'EMAIL'){
								$merge_vars[$field_v['list_field_id']] = getElementValue($field_v['element_id']);
							}
						}
						
						// GROUPINGS
						if(isset($list_v['groupings']) && $list_v['groupings']){
							foreach($list_v['groupings'] as $grouping_v){
								if($grouping_v['groups']){
									$merge_vars['GROUPINGS'][] = array('id'=>$grouping_v['grouping_id'], 'groups'=>$grouping_v['groups']);
								}
							}
						}
							
						/*
						sendwelcomeemail:  optional, if your double_optin is false and this is true, we will send your lists Welcome Email if this subscribe succeeds - this will *not* fire if we end up updating an existing subscriber.
						If double_optin is true, this has no effect. defaults to false.
						*/
						
						try{
							if(!$list_v['updateexistingcontact']){

								$mc_search_res = $mc->lists->memberInfo($list_id, array(array('email'=>$api_contact_email)));

								if($contactform_obj->cfg['debug']){
									echo 'SEARCH RESULTS '."\r\n";
									print_r($mc_search_res); echo "\r\n";
								}

							}


							// $mc_search_res is set only when updatexistingcontact is set to false
							// Error code 214 List_AlreadySubscribed is returned if the contact exists and if updatexistingcontact is set to false
							if((isset($mc_search_res) && !$mc_search_res['success_count']) || $list_v['updateexistingcontact']){
								$mc->lists->subscribe($list_id, array('email'=>$api_contact_email), $merge_vars, $api_contact_email_type='html', $list_v['doubleoptin'], $list_v['updateexistingcontact'], $replace_interests=true, $list_v['sendwelcomeemail']);
							}							
						}
						
						catch(Exception $e){
						
							if($contactform_obj->cfg['debug']){
								echo 'MAILCHIMP ERROR'."\r\n";
								echo $e->getCode().' '.$e->getMessage();
							}
								
							$admin_api_error[$service_id]['error_code'] = $e->getCode();
							$admin_api_error[$service_id]['error_message'] = $e->getMessage();
						} // catch
					} // if fields
				} // foreach list
			}
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);							
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // MAILCHIMP
		
		
		/**************************************************************************************
		 * SALESFORCE API
		 **************************************************************************************/
		if(!empty($cfg['salesforce']['username']) 
			&& !empty($cfg['salesforce']['password']) 
			&& !empty($cfg['salesforce']['accesstoken']) 
			&& !empty($cfg['salesforce']['lists']) 
			)
		{
			$service_id = 'salesforce';

			debugServiceSeparator($service_id, true);

			$service_requirements_status = $cfgenwpapi_obj->checkServiceRequirements($service_id);

			if($service_requirements_status['status']){		

				include('../api/salesforce/SforceEnterpriseClient.php');

				$mySforceConnection = new SforceEnterpriseClient();

				try{
					$mySforceConnection->createConnection('../api/salesforce/enterprise.wsdl.xml');

					$mySforceConnection->login($cfg[$service_id]['username'], $cfg[$service_id]['password'].$cfg[$service_id]['accesstoken']);
					
					foreach($cfg[$service_id]['lists'] as $list_v){
						
						$list_id = $list_v['list_id'];
						
						$merge_vars = array();
							
						// FIELDS
						$records = array();
							
						$records[0] = new stdclass();
							
						if(isset($list_v['fields']) && $list_v['fields']){
							
							foreach($list_v['fields'] as $field_v){
								
								$records[0]->{$field_v['list_field_id']} = getElementValue($field_v['element_id']);
							}
						
							try{
								$sf_addcontact = true;
								
								if($list_v['preventduplicates'] || $list_v['updateexistingcontact'] && !empty($list_v['filterduplicates'])){
									
									
									$sf_contact_exists_where = '';
									foreach($list_v['filterduplicates'] as $filterduplicates_v){
										$sf_contact_exists_where .= $filterduplicates_v.'=\''.addcslashes($element_ids_values[ $list_v['fields_by_id'][$filterduplicates_v] ]['element_value'][0], "'").'\' AND ';
									}
									
									$sf_contact_exists_where = substr($sf_contact_exists_where, 0, -4);
									
									$sf_contact_exists_req = 'SELECT Id from '.$list_id.' WHERE '.$sf_contact_exists_where;
									
									$sf_contact_exists_res = $mySforceConnection->query($sf_contact_exists_req);
									
									if($contactform_obj->cfg['debug']){
										echo $sf_contact_exists_req."\r\n";
										echo 'Exist_res:'."\r\n";
										print_r($sf_contact_exists_res->records); echo "\r\n";
									}
									
									if($list_v['preventduplicates'] && $sf_contact_exists_res->records){
										$sf_addcontact = false;
									} else{
										if($contactform_obj->cfg['debug']){
											echo 'NO RES CONTACT';
										}
									}
								}
								
								if($contactform_obj->cfg['debug']){
									if($list_v['preventduplicates']){
										echo 'PREVENT DUPLICATES IS ON: no add if the contact already exists'."\r\n";
									}
									if(!$list_v['updateexistingcontact']){
										echo 'UPDATE EXISTING CONTACT IS OFF: no update if the contact already exists'."\r\n";
									}
								}
								
								if($list_v['updateexistingcontact'] && isset($sf_contact_exists_res->records) && $sf_contact_exists_res->records){
									foreach($sf_contact_exists_res->records as $sf_contact_res){

										$records[0]->Id = $sf_contact_res->Id;
										$response = $mySforceConnection->update($records, $list_id);
										if($contactform_obj->cfg['debug']){
											echo 'UPD CONTACT '.$sf_contact_res->Id."\r\n";
										}
									}
								}

								if($sf_addcontact){// flag is set to false if duplicates are found in exist req
									if($contactform_obj->cfg['debug']){
										echo 'ADD CONTACT';
									}
									
									// The Id must be unset, else the contact will be updated instead of being added, even when using the create() method
									// Would trigger Error code: INVALID_FIELD_FOR_INSERT_UPDATE
									// Cannot specify Id in an insert call
									unset($records[0]->Id);
									
									$response = $mySforceConnection->create($records, $list_id);
								}
								
								// REQUIRED_FIELD_MISSING
								if(
								   isset($response[0]->errors[0]->statusCode) && $response[0]->errors[0]->statusCode
								   && isset($response[0]->errors[0]->message) && $response[0]->errors[0]->message
									){
									
									if($contactform_obj->cfg['debug']){
										print_r($response);
									}

									$admin_api_error[$service_id]['error_code'] = $response[0]->errors[0]->statusCode;
									$admin_api_error[$service_id]['error_message'] = $response[0]->errors[0]->message;
								}
								
							} catch(SoapFault $soapFault){
								// INVALID FIELD NAME, INVALID OBJECT TYPE (Contact, Lead, etc.)
								
								if($contactform_obj->cfg['debug']){
									print_r($soapFault); echo "\r\n";
								}
								
								$admin_api_error[$service_id]['error_code'] = $soapFault->faultcode;
								$admin_api_error[$service_id]['error_message'] = $soapFault->faultstring;
							}
						} // if fields
					} // foreach list
				} // try
				
				catch(Exception $e){
					
					if($contactform_obj->cfg['debug']){
						echo 'SALESFORCE ERROR'."\r\n";
						print_r($e); echo "\r\n";
					}
						
					$admin_api_error[$service_id]['error_code'] = $e->faultcode;
						
				} // catch
			}
			else{
				$admin_api_error[$service_id]['error_message'] = implode("\r\n", $service_requirements_status['errors']);							
			} // if/else service status

			debugServiceSeparator($service_id, false);

		} // SALESFORCE
		
		
		// SEND API ERROR MESSAGE
		if($contactform_obj->cfg['debug']){
			
			if(isset($admin_api_error) && $admin_api_error){
			
				foreach($admin_api_error as $admin_api_error_service_id=>$admin_api_error_v){
					
					$admin_api_error[$admin_api_error_service_id]['service_id'] = $admin_api_error_service_id;
					
					$admin_api_error[$admin_api_error_service_id]['service_name'] = $cfgenwpapi_obj->getServiceName($admin_api_error_service_id);
					
					$contactform_obj->sendAdminAPIError($admin_api_error[$admin_api_error_service_id]);
				}
			}
		}
	} // if $contactform_obj->merge_post
}

echo json_encode($json_response);
?>