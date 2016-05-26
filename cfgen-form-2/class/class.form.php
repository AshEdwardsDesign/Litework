<?php
class contactForm{

	function __construct($cfg){
		
		if(!ini_get('date.timezone') && function_exists('date_default_timezone_set')){
			date_default_timezone_set('UTC');
		}
		
		$this->dir_uploads = '../upload/';
		
		$this->cfg['email_address'] = isset($cfg['email_address']) ? trim($cfg['email_address']) : '';
		
		$this->usernotification_emailaddress = '';
		
		$this->url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

		
		// =?UTF-8?B? required to avoid bad character encoding in the From field
		$this->cfg['email_from'] = (isset($cfg['email_from']) && $cfg['email_from'])?'=?UTF-8?B?'.base64_encode($cfg['email_from']).'?=':$this->cfg['email_address'];
		
		$cfg_keys = array('debug', 
						  'email_address_cc', 'email_address_bcc', 
						  'timezone',
						  'adminnotification_subject',
						  'adminnotification_hideemptyvalues',
						  'adminnotification_hideformurl',
						  'usernotification_activate',
						  'usernotification_insertformdata',
						  'usernotification_inputid',
						  'usernotification_subject',
						  'usernotification_message',
						  'usernotification_hideemptyvalues',
						  'form_name',
						  'form_errormessage_captcha',
						  'form_errormessage_emptyfield',
						  'form_errormessage_invalidemailaddress',
						  'form_errormessage_invalidurl',
						  'form_errormessage_terms',
						  'form_validationmessage',
						  'form_redirecturl',
						  'emailsendingmethod',
						  'smtp_host',
						  'smtp_port',
						  'smtp_encryption',
						  'smtp_username',
						  'smtp_password',
						  'sms_admin_notification_gateway_id',
						  'sms_admin_notification_to_phone_number',
						  'sms_admin_notification_from_phone_number',
						  'sms_admin_notification_message',
						  'sms_admin_notification_username',
						  'sms_admin_notification_password',
						  'sms_admin_notification_api_id',
						  'sms_admin_notification_account_sid',
						  'sms_admin_notification_auth_token',
						  'screen_width',
						  'screen_height',
						  );

		foreach($cfg_keys as $v){
			$this->cfg[$v] = isset($cfg[$v]) ? $cfg[$v] : '';
		}

		$this->mail_content_type_format = 'plaintext'; // html
		
		// ADMIN NOTIFICATION CONTENT TYPE
		if($this->mail_content_type_format === 'plaintext'){
			$this->mail_content_type_format_charset = 'Content-type: text/plain; charset=utf-8';
			$this->mail_line_break = "\r\n";
		}
		
		if($this->mail_content_type_format === 'html'){
			$this->mail_content_type_format_charset = 'Content-type: text/html; charset=utf-8';
			$this->mail_line_break = "<br>";
		}
		
		// USER NOTIFICATION CONTENT TYPE
		$this->cfg['usernotification_format'] = isset($cfg['usernotification_format']) ? $cfg['usernotification_format'] : '';
		
		if($this->cfg['usernotification_format'] === 'plaintext'){
			$this->mail_content_type_format_charset_usernotification = 'Content-type: text/plain; charset=utf-8';
			$this->mail_line_break_usernotification = "\r\n";
		}
		
		if($this->cfg['usernotification_format'] === 'html'){
			$this->mail_content_type_format_charset_usernotification = 'Content-type: text/html; charset=utf-8';
			$this->mail_line_break_usernotification = "<br>";
		}

		
		require 'class.phpmailer.php';
		
		$this->phpmailer_adminnotification = $this->phpMailerSetUp(new PHPMailer());
		$this->phpmailer_adminnotification->AddAddress($this->cfg['email_address']);
		$this->phpmailer_adminnotification->IsHTML($this->mail_content_type_format === 'html' ? true : false);
		
		$this->phpmailer_usernotification = $this->phpMailerSetUp(new PHPMailer());
		// We are not using the SetFrom() method because it can generate "Could not instantiate mail function" on some servers (Infomaniak)
		//$this->phpmailer_usernotification->SetFrom($this->cfg['email_address'], $this->cfg['email_from']);
		$this->phpmailer_usernotification->From = $this->cfg['email_address'];
		$this->phpmailer_usernotification->FromName = $this->cfg['email_from'];
		$this->phpmailer_usernotification->AddReplyTo($this->cfg['email_address']);
		$this->phpmailer_usernotification->IsHTML($this->cfg['usernotification_format'] === 'html' ? true : false);
		
		$this->phpmailer_errornotification = $this->phpMailerSetUp(new PHPMailer());
		$this->phpmailer_errornotification->From = $this->cfg['email_address'];
		$this->phpmailer_errornotification->FromName = $this->cfg['email_address'];
		$this->phpmailer_errornotification->AddAddress($this->cfg['email_address']);
		$this->phpmailer_errornotification->IsHTML(false);

		if($this->cfg['emailsendingmethod'] === 'smtp'){
		
			$this->phpmailer_adminnotification = $this->phpMailerSetSMTP($this->phpmailer_adminnotification, array('smtpdebug'=>false));
			
			$this->phpmailer_usernotification = $this->phpMailerSetSMTP($this->phpmailer_usernotification, array('smtpdebug'=>false));
			
			$this->phpmailer_errornotification = $this->phpMailerSetSMTP($this->phpmailer_errornotification, array('smtpdebug'=>false));
		}

		$this->dash_line = '--------------------------------------------------------------';

		$this->merge_post_index = 0;
		
		$this->{'d'.'e'.'m'.'o'} = 0;
		
		$this->envato_link = 'http://codecanyon.net/item/contact-form-generator/1719810?ref=topstudio';
	}


	function phpMailerSetUp($phpmailer_obj){
	
		$phpmailer_obj->CharSet = 'utf-8';
		
		return $phpmailer_obj;
	}
	
	function phpMailerSetSMTP($phpmailer_obj, $options){
		
		$phpmailer_obj->IsSMTP(); // telling the class to use SMTP
		
		/**
		 Enables SMTP debug information (for testing)
		 false
		 1 = errors and messages
		 2 = messages only
		 */
		$phpmailer_obj->SMTPDebug = $options['smtpdebug'];

		$phpmailer_obj->Host = $this->cfg['smtp_host']; // sets the SMTP server
		$phpmailer_obj->Port = $this->cfg['smtp_port']; // set the SMTP port for the GMAIL server

		if($this->cfg['smtp_encryption'] === 'ssl' || $this->cfg['smtp_encryption'] === 'tls'){
			
			if($this->cfg['smtp_encryption'] === 'ssl'){
				$phpmailer_obj->SMTPSecure = 'ssl';
			}
				
			if($this->cfg['smtp_encryption'] === 'tls'){
				$phpmailer_obj->SMTPSecure = 'tls';
			}
		}
		
		if($this->cfg['smtp_username'] || $this->cfg['smtp_password']){
			$phpmailer_obj->SMTPAuth = true; // enable SMTP authentication
		}
		
		$phpmailer_obj->Username = $this->cfg['smtp_username'] ? $this->cfg['smtp_username'] : ''; // SMTP account username
		
		$phpmailer_obj->Password = $this->cfg['smtp_password'] ? $this->cfg['smtp_password'] : ''; // SMTP account password
		
		return $phpmailer_obj;
	}
	
	function deleteUpload($filename){
		
		if($filename){
			$file_to_delete = $this->dir_uploads.$filename;
			
			if(is_file($file_to_delete)){
				@unlink($file_to_delete);
			}
		}
	}
	

	
	function sendAdminNotification(){
		
		$count_files_to_attach = 0;
		
		
		if($this->cfg['timezone'] && function_exists('date_default_timezone_set')){
			// function_exists('date_default_timezone_set') for PHP4 servers
			date_default_timezone_set($this->cfg['timezone']);
		}
		
		// g:i A | 01:37 AM
		// G:i | 13:37
		$mail_body = $this->cfg['form_name']
					.$this->mail_line_break.$this->mail_line_break.@date('F jS, Y, G:i');

		if(!$this->cfg['adminnotification_hideformurl']){
			$mail_body .= ($this->url?$this->mail_line_break.$this->mail_line_break.'Form URL: '.$this->url : '');
		}
		
		$mail_body .= $this->mail_line_break.$this->dash_line;

		if($this->merge_post){
			
			foreach($this->merge_post as $value){
				
				if(isset($value['element_type']) && $value['element_type'] == 'upload' && isset($value['element_value']) && $value['element_value']){

					if( isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 2) ){
						$count_files_to_attach++;
					}

					$explode_requesturi = explode('/',$_SERVER['REQUEST_URI']);
					//print_r($explode_requesturi);
					
					$explode_requesturi = explode('/',$_SERVER['SCRIPT_NAME']);
					//print_r($explode_requesturi);

					$inc_form_validation = $explode_requesturi[count($explode_requesturi)-2].'/'.$explode_requesturi[count($explode_requesturi)-1] ;

					$install_dir = str_replace($inc_form_validation, '', $_SERVER['SCRIPT_NAME']);
					
					
					$mail_body .= $this->buildLabel($value['elementlabel_value'], $value['element_value'], $this->cfg['adminnotification_hideemptyvalues'], $this->mail_line_break);
					
					// No file link if we delete the file after the upload
					// 1: File Attachment + Download Link
					// 2: File Attachment Only
					if( isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 3) ){
						$mail_body .= $this->mail_line_break
									.'http://'.$_SERVER['SERVER_NAME']
									.str_replace('%2F', '/', rawurlencode($install_dir.ltrim($this->dir_uploads, './').$value['element_value']));
					}

				} 
				else{
					$mail_body .= $this->buildLabel($value['elementlabel_value'], $value['element_value'], $this->cfg['adminnotification_hideemptyvalues'], $this->mail_line_break);
				}
			}
		}
		
		$mail_body .= $this->mail_line_break.$this->mail_line_break.$this->dash_line;
		
		// IP ADDRESS
		$mail_body .= $this->mail_line_break.'IP address: '.$_SERVER['REMOTE_ADDR'];
		
		// HOST
		$mail_body .= $this->mail_line_break.'Host: '.gethostbyaddr($_SERVER['REMOTE_ADDR']);

		
		//SCREEN SIZE
		if($this->cfg['screen_width'] && $this->cfg['screen_height']){
			$mail_body .= $this->mail_line_break.'Screen resolution: '.$this->cfg['screen_width'].'x'.$this->cfg['screen_height'];
		}
		
		if($this->mail_content_type_format === 'html'){
			$mail_body = nl2br($mail_body);
		}
		

		// for the admin: if the user provides his email address, it will appear in the "from" field
		
		if($count_files_to_attach){
			foreach($this->merge_post as $value){
				if(
					isset($value['element_type']) && $value['element_type'] === 'upload'
					&& !empty($value['element_value']) 
					&& isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 2)
				)
				{
					$this->phpmailer_adminnotification->AddAttachment($this->dir_uploads.$value['element_value']);
				}
			} // foreach
		}


		if($this->cfg['email_address_cc']){
			foreach($this->cfg['email_address_cc'] as $explode_cc_value){
				$this->phpmailer_adminnotification->AddCC($explode_cc_value);
			}
		}

		if($this->cfg['email_address_bcc']){
			foreach($this->cfg['email_address_bcc'] as $explode_bcc_value){
				$this->phpmailer_adminnotification->AddBCC($explode_bcc_value);
			}
		}
		
		// The subject must not be set in the class constructor because ['adminnotification_subject'] can be updated in the form validation file (string replace)
		$this->phpmailer_adminnotification->Subject = $this->cfg['adminnotification_subject'];
		$this->phpmailer_adminnotification->Body = $mail_body; // Body can't be empty or the message won't be sent

		// The fields below must not be set in the class constructor because it can be updated in the form validation file
		// We are not using the SetFrom() method because it can generate "Could not instantiate mail function" on some servers (Infomaniak)
		// $this->phpmailer_adminnotification->SetFrom($param['reply_emailaddress'], $param['reply_emailaddress']);
		$param['reply_emailaddress'] = $this->usernotification_emailaddress ? $this->usernotification_emailaddress : $this->cfg['email_address'];
		$this->phpmailer_adminnotification->From = $param['reply_emailaddress'];
		$this->phpmailer_adminnotification->FromName = $param['reply_emailaddress'];
		$this->phpmailer_adminnotification->AddReplyTo($param['reply_emailaddress']);
		
		
		// SEND NOTIFICATION
		if($this->cfg['emailsendingmethod'] === 'php'){
			if(!$this->phpmailer_adminnotification->Send()){
				if($this->cfg['debug']){
					echo $this->phpmailer_adminnotification->ErrorInfo;
				}
			}
		}
		
		if($this->cfg['emailsendingmethod'] === 'smtp'){
			
			// $this->phpmailer_adminnotification->ErrorInfo; only returns the most recent mailer error message
			ob_start();
			
			if(!$this->phpmailer_adminnotification->Send()){
			
				// error? 
				$phpmailer_smtperrormessage = new PHPMailer();
				$phpmailer_smtperrormessage->CharSet = 'utf-8';
				// We are not using the SetFrom() method because it can generate "Could not instantiate mail function" on some servers (Infomaniak)
				//$phpmailer_smtperrormessage->SetFrom($this->cfg['email_address']);
				$phpmailer_smtperrormessage->From = $this->cfg['email_address'];
				$phpmailer_smtperrormessage->FromName = $this->cfg['email_address'];
				$phpmailer_smtperrormessage->AddAddress($this->cfg['email_address']);
				$phpmailer_smtperrormessage->Subject = 'Form '.$this->cfg['form_name'].' - SMTP failed delivery notification';
				$phpmailer_smtperrormessage->Body = '';
				$phpmailer_smtperrormessage->Body .= 'An error occured with the form. It was not possible to send your notification message and the following error message was returned. ';
				$phpmailer_smtperrormessage->Body .= "\r\n".strip_tags(ob_get_contents());
				$phpmailer_smtperrormessage->Body .= "\r\n".'SMTP host: '.$this->cfg['smtp_host'];
				$phpmailer_smtperrormessage->Body .= "\r\n".'SMTP port: '.$this->cfg['smtp_port'];
				$phpmailer_smtperrormessage->Body .= "\r\n".'SMTP encryption: '.$this->cfg['smtp_encryption'];
				$phpmailer_smtperrormessage->Body .= "\r\n".'SMTP username: '.$this->cfg['smtp_username'];
				$phpmailer_smtperrormessage->Body .= "\r\n".'You may want to double check your SMTP credentials in the form settings in order to fix this problem.';
				$phpmailer_smtperrormessage->Body .= "\r\n".'You may also want to check if the port '.$this->cfg['smtp_port'].' is opened on your server. You must contact your hosting support to get that information.';
				$phpmailer_smtperrormessage->Send();
				
			}
			
			ob_end_clean();
			
			if($this->cfg['debug']){
				echo $phpmailer_smtperrormessage->Body;
			}

		}


		/***
		 * Delete the uploaded files with the option File Attachment Only
		 * We can't put the delete procedure above the sending of the notification (below $this->phpmailer_adminnotification->AddAttachment)
		 * if the file uploaded is not on the server when using $this->phpmailer_adminnotification->Send() the smtp sending method will send an empty notification with no file attached
		 * it would still work with mail() though, because we use $data = fread() for file attachment, not the actual file
		 */
		if($count_files_to_attach){
				
			foreach($this->merge_post as $value){
				
				if(
					isset($value['element_type']) && $value['element_type'] == 'upload'
					&& isset($value['element_value']) && $value['element_value']
					&& isset($value['deletefile']) && $value['deletefile'] == 2
					// 2: File Attachment Only
				)
				{

					// delete attached file?
					// this is different from deleting the file when the user deletes the file himself in the from: check form-validation.php for this (in form-validation.php because the file must be deleted even if sendMail() is not called - when there are errors for example)
					if(isset($value['deletefile']) && $value['deletefile'] == 2){
						
						if(isset($value['element_value']) && $value['element_value']){
							
							$this->deleteUpload($value['element_value']);
						}
					}
				}
			} // foreach
		} // if($count_files_to_attach)
	}
	
	
	function sendUserNotification(){
		
		$mail_body = '';
		
		// no nl2br on the notification message because if html format is selected, it's up to the user to put the proper br or p tags to insert line breaks
		$mail_body .= $this->cfg['usernotification_message'];
		
		if($this->cfg['usernotification_insertformdata']){
			$mail_body .= $this->mail_line_break_usernotification.$this->dash_line;
			
			foreach($this->merge_post as $form_data){
				$mail_body .= $this->buildLabel($form_data['elementlabel_value'], $form_data['element_value'], $this->cfg['usernotification_hideemptyvalues'], $this->mail_line_break_usernotification);
			}
		}
		
		$this->phpmailer_usernotification->AddAddress($this->usernotification_emailaddress); // <- Cannot be moved in the constructor as the property is built in the form validation file, after the form class is created
		
		// The subject must not be set in the class constructor because ['usernotification_subject'] can be updated in the form validation file (string replace)
		$this->phpmailer_usernotification->Subject = $this->cfg['usernotification_subject'];
		$this->phpmailer_usernotification->Body = $mail_body; // Body can't be empty or the message won't be sent

		$this->phpmailer_usernotification->Send();
	}
	
	function mergePost($value){
		
		$this->merge_post[$this->merge_post_index]['element_id'] = $value['element_id'];
		$this->merge_post[$this->merge_post_index]['element_value'] = $this->quote_smart(trim($value['element_value']));
		$this->merge_post[$this->merge_post_index]['elementlabel_value'] = $this->quote_smart(trim($value['elementlabel_value']));
		$this->merge_post[$this->merge_post_index]['elementlabel_id'] = $this->quote_smart(trim($value['elementlabel_id']));
		
		if(!empty($value['element_type'])){
			// if element_type == upload, we add the download link in the mail body message
			$this->merge_post[$this->merge_post_index]['element_type'] = trim($value['element_type']);
		}
		
		if(!empty($value['deletefile'])){
			$this->merge_post[$this->merge_post_index]['deletefile'] = trim($value['deletefile']);
		}
		
		$this->merge_post_index++;
	}
	
	function isEmail($email){

		$atom = '[-a-z0-9\\_]'; // authorized caracters before @
		
		$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // authorized caracters after @

		$regex = '/^' . $atom . '+' . '(\.' . $atom . '+)*' . '@' . '(' . $domain . '{1,63}\.)+' . $domain . '{2,63}$/i';          
		
		return preg_match($regex, trim($email)) ? 1 : 0;
	}
	
	function quote_smart($value){

		$value = get_magic_quotes_gpc() ? stripslashes($value) : $value;
		
		return $value;
	}
	
	function buildLabel($label, $value, $hideemptyvalues, $linebreak){
		
		$label = $linebreak.$linebreak.$label.': '.$value;
		
		if($hideemptyvalues){
			
			if(!$value){
				$label = '';
			}
		}
		
		return($label);
	}
	
	function sendAdminAPIError($service_error){
		
		// For some error code, we don't send the error message which may be annoying in some cases
		// Example: Mailchimp Error code 214 : x@x.com is already subscribed to list 
		$sendmail = true;
		
		$api_id = $service_error['service_id'];
		
		$api_name = $service_error['service_name'];
		
		$error_code = isset($service_error['error_code']) ? $service_error['error_code'] : ''; // we don't check if value  !='' because it can be equal to 0 (mailchimp)
		
		
		$error_message = '';
	
		$service_error['error_message'] = (isset($service_error['error_message']) && $service_error['error_message']) ? $service_error['error_message'] : '';
		
		$mail_subject = $api_name.' API Error Message';
		
		$mail_body = 'An error occured with '.$api_name.' API and the following error message has been returned. ';
		
		if($error_code){
			$mail_body .= "\r\n\r\n".'Error code: '.$error_code;
		}
		
		
		// AWEBER
		if($api_id === 'aweber'){
			
			 /*
			  * There is no error code returned by the API
			  * We only use $service_error['error_message'] set in form validation
			  *
			  * Invalid email address? "email: Required input is missing", data won't be inserted
			  * Invalid list id?
					$e->type = NotFoundError
					$e->message = Object: None, name: ''
					We apply a custom error message
					data won't be inserted
			  *
			  * Invalid field id?
					custom_fields: Invalid key name. Valid key names are...
			  * Missing required field (email)? The only required field is email, data won't be inserted if the email is invalid
			  */
			  
		} // if getresponse

		
		// CAMPAIGN MONITOR
		if($api_id === 'campaignmonitor'){
		
			if($error_code == '50'){
				$error_message .= 'Invalid API key.';
			}
			
			if($error_code == '1'){
				$error_message .= 'Invalid email address.';
			}
			
			if($error_code == '101'){
				$error_message .= 'Invalid List ID.';
			}
			
			if($error_code == '400'){
				// For example: space character in the list ID
				$error_message .= 'HTTP Error 400. The request is badly formed.';
			}
			
			 /*
			  * Custom required field missing? No error message returned, data will be inserted
			  * Invalid value for specific field type? No error message returned, data will be inserted
			  * Invalid field name? No error message returned, data will be inserted
			  */
			  
		} // if campaignmonitor
		
		
		// CONSTANTCONTACT
		if($api_id === 'constantcontact'){
			
			if($error_code == 'mashery.not.authorized.inactive'){
				$error_message .= 'Invalid API key or access token.';
			}
			
			if($error_code == 'json.email.invalid'){
				$error_message .= 'Invalid email address.';
			}
			
			if($error_code == 'json.field.lists.value.invalid'){
				$error_message .= 'Invalid List ID.';
				// $service_error['error_message'] : The contact list XXX does not exist.
			}
			
			if($error_code == 'json.field.invalid'){
				$error_message .= 'Invalid field name.';
				// $service_error['error_message'] : #/xxx: Property was found but is not permitted at this location.
			}
			
		} // if constantcontact
		
		
		// GETRESPONSE
		if($api_id === 'getresponse'){
			
			 /*
			  * There is no error code returned by the API
			  * We only use $service_error['error_message'] set in form validation
			  *
			  * Invalid email address? No error message returned, data won't be inserted
			  * Invalid name? No error message returned, data won't be inserted
			  * Invalid value for specific field type? No error message returned, data won't be inserted
			  * Invalid list id? No error message returned, data won't be inserted
			  * Missing required field (name, email)? The only required field is email, data won't be inserted if the email is invalid
			  */
			  
		} // if getresponse
		
		
		// ICONTACT
		if($api_id === 'icontact'){
			
			 /*
			  * There is no error code returned by the API
			  * We only use $service_error['error_message'] set in form validation
			  *
			  * Invalid field name? No error message returned, data will be inserted
			  * Invalid email address? No error message returned, data won't be inserted
			  * Invalid list id? No error message returned, data will be inserted in the account, but the contact won't be linked to a list
			  * Missing required field? The only required field is email, data won't be inserted if the email is invalid
			  */
			  
		} // if icontact
		
		
		// MAILCHIMP
		if($api_id === 'mailchimp'){
		
			if($error_code == '0' || $error_code == '104'){
				$error_message .= 'API key is not valid or does not exist.';
			}
			
			if($error_code == '250'){
				// MERGE1 must be provided - Please enter a value
				// MERGE1 must be provided - Please enter a number (wrong data type on a required field)
				// MERGE1 must be provided - That is not a valid URL
				// wrong data type pushed into a field that is not required: the field is updated with "" and no error message is returned

				$error_message .= 'Missing required field.';
			}
			
			if($error_code == '-100'){
				// -100 The email parameter should include an email, euid, or leid key
				// -100 The domain portion of the email address is invalid
				// -100 An email address must contain a single @
				$error_message .= 'Invalid email address.';
			}
			
			if($error_code == '214'){
				// xxx@xxx.com is already subscribed to list f00. Click here to update your profile.
				$sendmail = false;
			}
			
			if($error_code == '270'){
				// "xxx" is not a valid Interest Group in Grouping "aaa" on the list: f00
				// "yyy" is not a valid Interest Grouping id for the list: f00
				$error_message .= 'Groups';
			}
			
			if($error_code == '200'){
				// Invalid MailChimp List ID
			}
			
			
			// Invalid field name? No error message returned, data will be inserted
			
			 
		} // if mailchimp
		
		
		// SALESFORCE
		if($api_id === 'salesforce'){
			
			if($error_code == 'INVALID_LOGIN' || $error_code == 'sf:INVALID_LOGIN'){
				$error_message .= 'Invalid username, password or security token.';
			}
			
			if($error_code == 'HTTP'){
				$error_message .= 'Invalid API key or access token support is not available. OpenSSL must be activated.';
			}
			
			if($error_code == 'REQUIRED_FIELD_MISSING'){
				$error_message .= 'Missing required field.';
			}
			
			if($error_code == 'sf:INVALID_FIELD'){
				$error_message .= 'Invalid field name.';
			}
			
			if($error_code == 'sf:INVALID_TYPE'){
				$error_message .= 'Invalid object type.';
			}
			
			
			if($error_code == 'INVALID_EMAIL_ADDRESS'){
				/**
				 * Email: invalid email address is already returned by SF
				 */
			}
			
			 /*
			  * Invalid value for specific field type (example: invalid email adddress) ? data will NOT be inserted
			  * 
			  */
			
		} // if salesforce
		
		$mail_body = $mail_body
					.($error_message ? "\r\n\r\n".$error_message : '')
					.($service_error['error_message'] ? "\r\n\r\n".$service_error['error_message'] : '')
					."\r\n\r\n".'No data has been inserted into your list.'
					;
		
		$mail_body .= "\r\n\r\n".$this->dash_line
					."\r\n\r\n".$this->cfg['form_name']
					;
		
		foreach($this->merge_post as $merge_post_v){
			$mail_body .= "\r\n\r\n".$merge_post_v['elementlabel_value'].': '.$merge_post_v['element_value'];
		}
		
		
		if($sendmail && ($error_message || $service_error['error_message'])){
									// ^-- CONSTANTCONTACT if !$api_contact_email, there is $service_error['error_message'] but there is no error_code
									// ^-- ICONTACT if !$api_contact_email, there is $service_error['error_message'] but there is no error_code

			$this->phpmailer_errornotification->Subject = $mail_subject;
				
			$this->phpmailer_errornotification->Body = $mail_body; // Body can't be empty or the message won't be sent
			
			$this->phpmailer_errornotification->Send();
			
		}
	}
	
}

/**
 * NO SPACES AFTER THIS LINE TO PREVENT "Warning: Cannot modify header information"
 */
?>