<?php
$cfg['debug'] = false;

// This is the email address where you will receive the notification message
$cfg['email_address'] = 'info@franchiseopportunitieshub.co.uk';

$cfg['email_from'] = 'Franchise Opportunities Hub';

// The recipients in CC and BCC will receive a copy of the data collected in the form
// Use commas to separate mutiple e-mail addresses (no spaces allowed)
// Example: youraddress1@yourdomain.com,youraddress2@yourdomain.com
$cfg['email_address_cc'] = '';

$cfg['email_address_bcc'] = '';

$cfg['emailsendingmethod'] = 'php';

$cfg['smtp_host'] = '';

$cfg['smtp_port'] = '';

$cfg['smtp_encryption'] = '';

$cfg['smtp_username'] = '';

$cfg['smtp_password'] = '';

$cfg['timezone'] = 'Europe/London';

$cfg['form_id'] = '';

$cfg['form_name'] = 'Franchise Opps Hub - Franchise Enquiry';

$cfg['form_validationmessage'] = 'Thank you, your message has been sent to us.<br />We will get back to you as soon as possible.';

$cfg['form_errormessage_captcha'] = 'Incorrect captcha';

$cfg['form_errormessage_emptyfield'] = 'This field cannot be left blank';

$cfg['form_errormessage_invalidemailaddress'] = 'Invalid email address';

$cfg['form_errormessage_invalidurl'] = '';

$cfg['form_errormessage_terms'] = '';

$cfg['form_redirecturl'] = '';

$cfg['adminnotification_subject'] = 'Franchise Inquiry from Franchise Opportunities Hub';

$cfg['adminnotification_hideemptyvalues'] = false;

$cfg['adminnotification_hideformurl'] = false;

$cfg['usernotification_inputid'] = 'cfgen-element-5-3';

$cfg['usernotification_activate'] = true;

$cfg['usernotification_insertformdata'] = true;

$cfg['usernotification_format'] = 'plaintext';

$cfg['usernotification_subject'] = 'Thank you requesting more information!';

$cfg['usernotification_message'] = 'Thank you for requesting further information from one of the franchises or business opportunities currently advertising on Franchise Opportunities Hub.

The franchise/business opportunity will contact you directly as soon as possible.

For your reference, below you can find a copy of the information you have provided us.';

$cfg['usernotification_hideemptyvalues'] = false;

$cfg['formvalidation_required'] = array('cfgen-element-5-6','cfgen-element-5-7','cfgen-element-5-3','cfgen-element-5-8','cfgen-element-5-13','cfgen-element-5-12','cfgen-element-5-4');

$cfg['formvalidation_email'] = array('cfgen-element-5-3');

?>