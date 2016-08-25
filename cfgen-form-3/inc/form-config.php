<?php
$cfg['debug'] = false;

// This is the email address where you will receive the notification message
$cfg['email_address'] = 'enquiries@readymadebusiness4u.com';

$cfg['email_from'] = 'Readymade Business 4 U Ltd';

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

$cfg['timezone'] = 'UTC';

$cfg['form_id'] = '';

$cfg['form_name'] = 'Readymade Business 2016';

$cfg['form_validationmessage'] = '';

$cfg['form_errormessage_captcha'] = 'Incorrect captcha';

$cfg['form_errormessage_emptyfield'] = 'This field cannot be left blank';

$cfg['form_errormessage_invalidemailaddress'] = 'Invalid email address';

$cfg['form_errormessage_invalidurl'] = '';

$cfg['form_errormessage_terms'] = 'You must accept the terms and conditions';

$cfg['form_redirecturl'] = 'http://readymadebusiness4u.com/thankyou.html';

$cfg['adminnotification_subject'] = 'New message sent from the Readymade website';

$cfg['adminnotification_hideemptyvalues'] = false;

$cfg['adminnotification_hideformurl'] = false;

$cfg['usernotification_inputid'] = 'cfgen-element-3-3';

$cfg['usernotification_activate'] = true;

$cfg['usernotification_insertformdata'] = true;

$cfg['usernotification_format'] = 'plaintext';

$cfg['usernotification_subject'] = 'Thank you for your message {Name*|10}';

$cfg['usernotification_message'] = 'Thank you for contacting Readymade Business 4 U Ltd {Name*|10}.

We have provided a copy of the information you have provided us in this email.

We will be in touch shortly.';

$cfg['usernotification_hideemptyvalues'] = true;

$cfg['formvalidation_required'] = array('cfgen-element-3-10','cfgen-element-3-11','cfgen-element-3-3','cfgen-element-3-9','cfgen-element-3-4');

$cfg['formvalidation_email'] = array('cfgen-element-3-3');

$cfg['formvalidation_terms'] = array('cfgen-element-3-6');

?>
