<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// Email Settings
// These settings determine how LimeSurvey will send emails
$config = array();
$config['siteadminemail']     = 'your-email@example.net'; // The default email address of the site administrator
$config['siteadminbounce']    = 'your-email@example.net'; // The default email address used for error notification of sent messages for the site administrator (Return-Path)
$config['siteadminname']      = 'Your Name';      // The name of the site administrator

$config['emailmethod']        = 'mail';           // The following values can be used:
$config['protocol'] = $config['emailmethod'];
// mail      -  use internal PHP Mailer
// sendmail  -  use Sendmail Mailer
// smtp      -  use SMTP relaying

$config['emailsmtphost']      = 'localhost';      // Sets the SMTP host. You can also specify a different port than 25 by using
// this format: [hostname:port] (e.g. 'smtp1.example.com:25').

$config['emailsmtpuser']      = '';               // SMTP authorisation username - only set this if your server requires authorization - if you set it you HAVE to set a password too
$config['emailsmtppassword']  = '';               // SMTP authorisation password - empty password is not allowed
$config['emailsmtpssl']       = '';               // Set this to 'ssl' or 'tls' to use SSL/TLS for SMTP connection

$config['emailsmtpdebug']     = 0;                // Settings this to 1 activates SMTP debug mode

$config['maxemails']          = 50;               // The maximum number of emails to send in one go (this is to prevent your mail server or script from timeouting when sending mass mail)

$config['charset']       = "utf-8";

return $config;  // You can change this to change the charset of outgoing emails to some other encoding  - like 'iso-8859-1'