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

$cfg['form_id'] = '4';

$cfg['form_name'] = 'Franchise Opps Hub - Advertising';

$cfg['form_validationmessage'] = 'Thank you, your message has been sent to us.<br />We will get back to you as soon as possible.';

$cfg['form_errormessage_captcha'] = 'Incorrect captcha';

$cfg['form_errormessage_emptyfield'] = 'This field cannot be left blank';

$cfg['form_errormessage_invalidemailaddress'] = 'Invalid email address';

$cfg['form_errormessage_invalidurl'] = '';

$cfg['form_errormessage_terms'] = '';

$cfg['form_redirecturl'] = '';

$cfg['adminnotification_subject'] = 'Someone wants to advertise on FOH!';

$cfg['adminnotification_hideemptyvalues'] = false;

$cfg['adminnotification_hideformurl'] = true;

$cfg['usernotification_inputid'] = 'cfgen-element-4-3';

$cfg['usernotification_activate'] = true;

$cfg['usernotification_insertformdata'] = true;

$cfg['usernotification_format'] = 'plaintext';

$cfg['usernotification_subject'] = 'Thank you requesting more information!';

$cfg['usernotification_message'] = 'Thank you for contacting us regarding advertising on Franchise Opportunities Hub.

We will get back to you as soon as possible.

For your reference, below you can find a copy of the information you have provided us.';

$cfg['usernotification_hideemptyvalues'] = true;

$cfg['formvalidation_required'] = array('cfgen-element-4-6','cfgen-element-4-7','cfgen-element-4-3','cfgen-element-4-8','cfgen-element-4-9','cfgen-element-4-12','cfgen-element-4-4');

$cfg['formvalidation_email'] = array('cfgen-element-4-3');

?>