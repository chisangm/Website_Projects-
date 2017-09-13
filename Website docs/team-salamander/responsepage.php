<?php
/* --------------------------------------------------------------------------
responsepage.php: Takes a message and a back link in the session variable.
It displays the message and provides a link to to the back page to the user.
This page is good for error messages, success message. Add/remove
Acknowledgments and so on
---------------------------------------------------------------------------*/
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

/* Unset all unnecessary $_SESSION variables */
//TODO

/* If no pagepage or response message was given, display an error and provide
    a link to go back home */
if(!isset($_SESSION['responsemessage']) || !isset($_SESSION['backpage']))
{
  $message = "Sorry there was an error <br /><br />";
  $backpage =  'Click <a href="index.php">Here</a> to go back to home';
}
else
{
  $message = $_SESSION['responsemessage'];
  $backpage = 'Click <a href="'.$_SESSION['backpage'].'" />here</a> to go back';
}

/* Print all template html before the content area */
generate_template_beginning('', 'responsepage.css');

/* Print the content area */
echo $message;
echo "<br /><br />";
echo $backpage;
/* Print all template html after the content area */
generate_template_end();

?>
