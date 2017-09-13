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

$errormessage = "";
if(isset($_SESSION['errormessage']))
  $errormessage = $_SESSION['errormessage'];

/* Unset all unnecessary $_SESSION variables */
foreach($_SESSION as $key => $val)
{
  if($key !== 'loggedin' && $key !== 'userid')
  {
    unset($_SESSION[$key]);
  }
}


/* Print all template html before the content area */
generate_template_beginning('login', 'login.css', "", $userid);

/* Print the content area */
echo <<<ZZEOF
				<h1>Login</h1><br />
        <form method="POST" action="processlogin.php" id="loginform">
       
        <!-- Email (username) -->
        <span id="email_label">Email:</span>
        <input type="text" name="email" id="email" placeholder="email@abc.com" /><br />

        <!-- Password -->
        <span id="password_label">Password:</span>
        <input type="password" name="password" id="password" />
        
        <!-- Submit -->
        <input type="submit" name="submitbutton" value="Login" />

        </form>
        <p>$errormessage</p>
        <a href="register.php">Don't have an account? Register Here.</a>

ZZEOF;

/* Print all template html after the content area */
generate_template_end();

?>
