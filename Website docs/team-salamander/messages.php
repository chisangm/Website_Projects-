<?php
/*
Messages.php a page to read and send message to/from other users

tabsize=2
*/

/* --------------------------------------------------------------------
INPUT -------------------------------------------------------------
----------------------------------------------------------------------*/
/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Included files */
require_once('template.php');


/*Set/get the necessary  $_SESSION Variables */
if(!isset($_SESSION['loggedin']))
  $_SESSION['loggedin'] = false;
$loggedin = $_SESSION['loggedin']; // True iff the user is logged in

if(!isset($_SESSION['userid']))
  $_SESSION['userid'] = "";
$userid = $_SESSION['userid']; // The user id of the currently logged in user

if(!isset($_SESSION['currentselection']))
{
  $_SESSION['currentselection'] = "";
}
$currentselection = $_SESSION['currentselection']; // The id if the message to show
$_SESSION['currentselection'] = "";



/* Unset all unnecessary $_SESSION variables */
foreach($_SESSION as $key => $val)
{
  if($key !== 'loggedin' && $key !== 'userid')
  {
    unset($_SESSION[$key]);
  }
}


/* ---------------------------------------------------------------
PROCESSING ------------------------------------------------------
-------------------------------------------------------------------*/
$messsageresults = ""; //Contains the message inbox

/* If the user is not logged in, redirect them to the login page */
if(!$loggedin)
{
  $_SESSION['backpage'] = "messages.php";
  header("Location: login.php");
  exit;
}

/* Get and store the message inbox for the current user */
try
{
  $db = connect_to_db();
  $messageresults = get_message_inbox($db, $userid);  
}
catch(PDOException $e)
{
  $_SESSION['responsemessage'] = "Sorry there was an internal error";
  $_SESSION['backpage'] = "index.php";
  header("Location: responsepage.php");
  exit;
}



/* ---------------------------------------------------------------
OUTPUT ------------------------------------------------------
-------------------------------------------------------------------*/
/* Print all template html before the content area */
generate_template_beginning('messaging', 'messages.css', "", $userid);

/* Print the content area */
echo <<<ZZEOF

<form action="processmessages.php" method="post">
  <h3 id="results_title"> Message Inbox: </h3>

  <table id="result_table">
    <tr id="tbltitlebar">
      <td class="radiocell"></td>
      <td class="tblid">ID</td>
      <td class="tblfrom">From</td>
      <td class="tblsubject">Subject</td>
    </tr>

ZZEOF;

$currentmessage = ""; //Stores the current message to display

/* Print the table rows with message results */
foreach($messageresults as $message)
{
  // Store the message information in variables for easy substitution 
  $id = $message['id'];
	$from = $message['from_id'];
	$subject = $message['subject'];

  // Convert the from id to a from email
  try
  {
    $email = get_email($db, $from);    
  }
  catch(PDOException $e)
  {
    $_SESSION['responsemessage'] = "Sorry there was an internal error";
    $_SESSION['backpage'] = "index.php";
    header("Location: responsepage.php");
    exit;
  }
  
  // Check if this row should be selected and this message should be displayed 
  $selected = "";
  if($id == $currentselection)
  {
   $selected = 'checked="checked"'; 
   $currentmessage = $message['message_body'];
  }

  /* print row*/
  echo<<<ZZEOF
    <tr>
      <td class="radiocell"><input type="radio" onClick="submit()" name="selection" value="$id" $selected></td>
      <td class="tblid">$id</td>
      <td class="tblfrom">$email</td>
      <td class="tblsubject">$subject</td>
    </tr>
ZZEOF;
}


/* Print after the inbox table */
echo<<<ZZEOF

</table>
ZZEOF;

/* Print the "send message" area */
echo<<<ZZEOF

  <div id="messagebody">
    <h3>Message Body</h3>
    <p id="body">
      $currentmessage
    </p>
  </div>
  <input type="submit" value="Remove" name="remove_message" />
</form>


<br /><br />
<h3>Send Message</h3>
<form id="sendmessageform" action="processmessages.php" method="post">
  To:      <input type="text" name="sendemail" id="sendemail" placeholder="email@email.com" /><br />
  Subject: <input type="text" name="sendsubject" id="sendsubject" placeholder="Subject" />
  <br />
  Message:  <br />
  <textarea id="sendbody" name="sendbody" >

  </textarea>
  <br />
  <input type="submit" name="send_message" value="Send Message" />
</form>
ZZEOF;

/* Print all template html after the content area */
generate_template_end();

?>
