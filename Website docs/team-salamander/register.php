<?php

/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Included files */
require_once('template.php');

/*Set the necessary  $_SESSION Variables */
if(!isset($_SESSION['loggedin']))
  $_SESSION['loggedin'] = false;

if(!isset($_SESSION['userid']))
  $_SESSION['userid'] = "";
$userid = $_SESSION['userid'];

/* Unset all unnecessary $_SESSION variables */
foreach($_SESSION as $key => $val)
{
  if($key !== 'loggedin' && $key !== 'userid')
  {
    unset($_SESSION[$key]);
  }
}

/* Print all template html before the content area */
generate_template_beginning('login', 'register.css', "", $userid);

/* Print the content area */
echo <<<ZZEOF
				<h1>Registration</h1><br />

ZZEOF;

/* Print all template html after the content area */
generate_template_end();

?>
