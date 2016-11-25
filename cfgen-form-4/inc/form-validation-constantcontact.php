<?php
if(
   !isset($contactform_obj) 
   || (isset($contactform_obj) && !is_object($contactform_obj)) 
   || !isset($_POST['form_values'])
   || !isset($json_error_array)
   || $json_error_array
   || !isset($element_ids_values)
   || !$element_ids_values
   )
{
	exit;
}

if(!empty($cfg['constantcontact']['apikey']) 
	&& !empty($cfg['constantcontact']['accesstoken']) 
	&& !empty($cfg['constantcontact']['lists']))
	{
		$service_id = 'constantcontact';
		
		include('../api/Ctct/autoload.php');

		$cc = new Ctct\ConstantContact($cfg[$service_id]['apikey']);

		foreach($cfg[$service_id]['lists'] as $list_v)
		{
			$list_id = $list_v['list_id'];
			
			$merge_vars = array();

			$api_contact_email = '';
			
			$api_action = '';

			// EMAIL FIELD FOR getContactByEmail()
			if(!empty($list_v['fields'])){
				
				foreach($list_v['fields'] as $fields_v){
					
					if($fields_v['list_field_id'] == 'email'){
						$api_contact_email = $element_ids_values[$fields_v['element_id']]['element_value'][0];
						break;
					}
				}
			}
				
			if($api_contact_email)
			{
				try{
					$response = $cc->getContactByEmail($cfg[$service_id]['accesstoken'], $api_contact_email);
					
					// ADD
					if(empty($response->results)){
						
						$cc_contact = new Ctct\Components\Contacts\Contact();

						
						$cc_contact->addEmail($api_contact_email);
						/*
						if(!empty($list_v['addresses'])){

							foreach($list_v['addresses'] as $address_field_k=>$address_field_v){
								
								$address = new Ctct\Components\Contacts\Address();
								
								foreach($address_field_v['data'] as $address_data_k=>$address_data_v){
									$address->{$address_data_k} = $element_ids_values[$address_data_v['element_id']]['element_value'][0];
								}
								
								$address->address_type = $address_field_v['type']; // PERSONAL, BUSINESS, UNKNOWN
								
								$cc_contact->addAddress($address);	
							}
						}
						*/
						$api_action = 'add';
						
					}
					// UPDATE
					else{
					
						$cc_contact = $response->results[0];
						
						$api_action = 'update';
					}

					// FIELDS
					if(!empty($list_v['fields'])){
						foreach($list_v['fields'] as $fields_v){
							if($fields_v['list_field_id'] != 'email'){
								$cc_contact->{$fields_v['list_field_id']} = $element_ids_values[$fields_v['element_id']]['element_value'][0];
							}
						}
					}
					
					// GROUPS
					/**
					 * https://community.constantcontact.com/t5/Developer-Support-ask-questions/Still-getting-json-min-items-violation-for-new-contact/td-p/180138
					 * Right now, there isn't a way to directly add a contact through the API without adding them to a list.
					 * Else the API would throw the error json.min.items.violation #/lists: 0 items were contained, but the minimum number of items allowed is 1.
					 */
					if(!empty($list_v['groups']) && ($api_action == 'add' || $list_v['updateexistingcontact']) ){
						foreach($list_v['groups'] as $group_v){
							$cc_contact->addList($group_v);
						}
					}
					
					if($api_action == 'add'){

						/*
						 * The third parameter of addContact defaults to false, but if this were set to true it would tell
						 * Constant Contact that this action is being performed by the contact themselves, and gives the ability to
						 * opt contacts back in and trigger Welcome/Change-of-interest emails.
						 *
						 * See: http://developer.constantcontact.com/docs/contacts-api/contacts-index.html#opt_in
						 */
						
						if($contactform_obj->cfg['debug']){
							echo 'ADD CONTACT'."\r\n";
							echo 'Contact data to send'."\r\n";
							print_r($cc_contact);
						}
						
						$returnContact = $cc->addContact($cfg[$service_id]['accesstoken'], $cc_contact, true);

						if($contactform_obj->cfg['debug']){
							echo 'Server:'."\r\n";
							print_r($returnContact);
						}
						
					} else{
						
						/*
						 * The third parameter of updateContact defaults to false, but if this were set to true it would tell
						 * Constant Contact that this action is being performed by the contact themselves, and gives the ability to
						 * opt contacts back in and trigger Welcome/Change-of-interest emails.
						 *
						 * See: http://developer.constantcontact.com/docs/contacts-api/contacts-index.html#opt_in
						 */
						 if($list_v['updateexistingcontact']){

							/**
							 * To prevent potential json.field.lists.value.invalid errors if a list id returned in the contact search results does not exist
							 * "json.field.lists.value.invalid Invalid List ID. The contact list 1234567 does not exist."
							 * In the form builder: Note that the user will be removed from the lists you don\'t select in the Email lists management section
							 */							
							if(!empty($list_v['groups'])){
								
								unset($cc_contact->lists);
								
								foreach($list_v['groups'] as $group_v){
									// We update the contact with the lists set in the config file only, not with the lists returned by the search results
									$cc_contact->addList($group_v);
								}
							}
							
							/**
							 * Remove the addresses property to prevent error message
							 * If a contact has 3 addresses that were created using the online dashboard, and if this contact gets updated using the Constant Contact API, 
							 * the following error message is thrown away:  
							 * json.max.items.violation #/addresses: 3 items were contained, but the maximum number of items allowed is 2
							 * (after using getContactByEmail, all the current addresses are returned in $response->results[0], therefore we must remove the property addresses in order to run updateContact properly)
							 **/
							unset($cc_contact->addresses);
							
							if($contactform_obj->cfg['debug']){
								echo 'UPDATE CONTACT'."\r\n";
								echo 'Contact data to send'."\r\n";
								print_r($cc_contact);
							}
							
							$returnContact = $cc->updateContact($cfg[$service_id]['accesstoken'], $cc_contact, true);
							
							if($contactform_obj->cfg['debug']){
								echo 'Server:'."\r\n";
								print_r($returnContact);
							}							
						}
					}
				}
				catch(Exception $e){
					
					$cc_error = $e->getErrors();
					
					if($contactform_obj->cfg['debug']){
						print_r($cc_error);
					}
							
					$admin_api_error[$service_id]['error_code'] = $cc_error[0]['error_key'];
					
					$admin_api_error[$service_id]['error_message'] = $cc_error[0]['error_message'];
				}
			} // if $api_contact_email
			else{
				$admin_api_error[$service_id]['error_message'] = 'Invalid email address, email address is empty.';										
			}
		} // foreach list
	} // CONSTANTCONTACT
	
?>			