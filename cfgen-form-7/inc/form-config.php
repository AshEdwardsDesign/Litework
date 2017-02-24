<?php
$cfg['debug'] = false;

// This is the email address where you will receive the notification message
$cfg['email_address'] = 'info@lunchesandmunchies.co.uk';

$cfg['email_from'] = 'Lunches and Munchies';

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

$cfg['form_name'] = 'Lunches and Munchies';

$cfg['form_validationmessage'] = 'Thank you, your message has been sent to us.<br />We will get back to you as soon as possible.';

$cfg['form_errormessage_captcha'] = 'Incorrect captcha';

$cfg['form_errormessage_emptyfield'] = 'This field cannot be left blank';

$cfg['form_errormessage_invalidemailaddress'] = 'Invalid email address';

$cfg['form_errormessage_invalidurl'] = '';

$cfg['form_errormessage_terms'] = '';

$cfg['form_redirecturl'] = '';

$cfg['adminnotification_subject'] = 'Message from your website';

$cfg['adminnotification_hideemptyvalues'] = false;

$cfg['adminnotification_hideformurl'] = true;

$cfg['usernotification_inputid'] = 'cfgen-element-7-3';

$cfg['usernotification_activate'] = true;

$cfg['usernotification_insertformdata'] = true;

$cfg['usernotification_format'] = 'plaintext';

$cfg['usernotification_subject'] = 'Thank you for contacting Lunches and Munchies!';

$cfg['usernotification_message'] = 'Thank you for contacting us at Lunches and Munchies.

We will respond to your message as soon as possible.

For your reference, below you can find a copy of the information you have provided us.';

$cfg['usernotification_hideemptyvalues'] = false;

$cfg['formvalidation_required'] = array('cfgen-element-7-6','cfgen-element-7-3','cfgen-element-7-8','cfgen-element-7-4');

$cfg['formvalidation_email'] = array('cfgen-element-7-3');

?>