<?php
$cfg['debug'] = false;

// This is the email address where you will receive the notification message
$cfg['email_address'] = 'info@serviceyoursmile.co.uk';
$cfg['email_from'] = '';

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
$cfg['form_name'] = 'Service your smile';
$cfg['form_validationmessage'] = 'Thank you, your message has been sent to us.<br />We will get back to you as soon as possible.';
$cfg['form_errormessage_captcha'] = 'Value does not match';
$cfg['form_errormessage_emptyfield'] = 'This field cannot be left blank';
$cfg['form_errormessage_invalidemailaddress'] = 'Invalid email address';
$cfg['form_errormessage_invalidurl'] = '';
$cfg['form_redirecturl'] = '';
$cfg['adminnotification_subject'] = 'New message sent from your website';
$cfg['adminnotification_hideemptyvalues'] = false;
$cfg['adminnotification_hideformurl'] = true;
$cfg['usernotification_inputid'] = 'cfg-element-2-3';
$cfg['usernotification_activate'] = false;
$cfg['usernotification_insertformdata'] = false;
$cfg['usernotification_format'] = '';
$cfg['usernotification_subject'] = '';
$cfg['usernotification_message'] = '';
$cfg['usernotification_hideemptyvalues'] = false;
$cfg['formvalidation_required'][] = 'cfg-element-2-6';
$cfg['formvalidation_required'][] = 'cfg-element-2-7';
$cfg['formvalidation_required'][] = 'cfg-element-2-9';
$cfg['formvalidation_required'][] = 'cfg-element-2-4';
$cfg['formvalidation_email'][] = 'cfg-element-2-3';
?>