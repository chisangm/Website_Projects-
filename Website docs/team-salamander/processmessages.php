<?php
/* processmessages.php: processes any submits performed on the messages.php page*/

/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Inluded files */
require_once('template.php');
require_once('../private/config.php');
require_once('./private/database.php');


/* Get SESSION Variables */
if(!isset($_SESSION["loggedin"]))
{
  $_SESSION["loggedin"] = false;
}
$loggedin = $_SESSION["loggedin"]; // Whether the user is logged in

if(!isset($_SESSION["userid"]))
{
  $_SESSION["userid"] = "";
}
$userid = $_SESSION["userid"]; // The user's ID


/* Get POST variables----------------------------------------- */
$removebutton = false; // True if "Remove" was button pressed
$sendbutton = false; //True iff "Send" button was pressed

/* Get which (if any) submit buttons were pressed */
if(isset($_POST['remove_message']))
{
  if($_POST["remove_message"])
    $removebutton = true;
}
if(isset($_POST['send_message']))
{
  if($_POST["send_message"])
    $sendbutton = true;
}

/* Get which message radio button was selected */
$selection = "";
if(isset($_POST['selection']))
{
  $selection = htmlspecialchars($_POST['selection']);
}


/* Get the TO address in the send area */
$sendemail = "";
if(isset($_POST['sendemail']))
{
  $sendemail = htmlspecialchars($_POST['sendemail']);
}


/* Get the subject in the send area */
$sendsubject = "";
if(isset($_POST['sendsubject']))
{
  $sendsubject = htmlspecialchars($_POST['sendsubject']);
}

/* Get the message body in the send area */
$sendbody = "";
if(isset($_POST['sendbody']))
{
  $sendbody = htmlspecialchars($_POST['sendbody']);
}


/* -------------------------------------------------------------------------*/
/* PROCESSING ---------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/

/* If no user is logged in, error message */
if(!$loggedin)
{
  $_SESSION['backpage'] = "messages.php";
  header('Location: login.php');
  exit;
}

/* If no button was pressed, error message */
if(!$removebutton && !$sendbutton && !$selection)
{
  $_SESSION['backpage'] = 'messages.php';
  $_SESSION['responsemessage'] = 'Sorry there was in error in processing your request.';
  header('Location: responsepage.php');
  exit;
}


/* If remove button---------------------------------------------------------*/
if($removebutton)
{
  /* If no message selection was made, error message */
  if($selection == "")
  {
    $_SESSION["backpage"] = "messages.php";
    $_SESSION["responsemessage"] = "Please select a message to remove";
    header("Location: responsepage.php");
    exit;
  }
  
  try
  {
    /* Check that the message belongs to the user (to prevent hacking) */
    $db = connect_to_db();
    if(has_message($db, $userid, $selection))
    {
      remove_message($db, $selection); 
      header("Location: messages.php");
      exit;
    }
    else
    {
      $_SESSION['responsemessage'] = "The message you are trying to remove does not belong to you";
      $_SESSION['backpage'] = "messages.php";
      header("Location: responsepage.php");
      exit;
    }
  }
  catch(PDOException $e)
  {
    $_SESSION["backpage"] = "messages.php";
    $_SESSION['responsemessage'] = "Sorry there was an error with your request";
    header("Location: responsepage.php");
    exit;
  }

}
elseif($sendbutton) /* If send button ---------------------------------------*/
{
  try
  {
    $db = connect_to_db();
    
    /* If the email to send the message doesn't exists, error */
    if(!email_exists($db, $sendemail))
    {
     $_SESSION['responsemessage'] = "Cannot send message: Email does not exist"; 
     $_SESSION['backpage'] = "messages.php";
     header("Location: responsepage.php");
     exit;
    }

    /* Send message otherwise */
    insert_message($db, $userid, get_user_id($db, $sendemail), $sendsubject, $sendbody);
    $_SESSION['responsemessage'] = "Message Sent.";
    $_SESSION['backpage'] = "messages.php";
    header("Location: responsepage.php");
    exit;
  }
  catch(PDOException $e)
  {
    $_SESSION['responsemessage'] = "Sorry there was an error with your request";
    $_SESSION['backpage'] = "messages.php";
    header("Location: responsepage.php");
    exit;
  }


}
elseif($selection != "")
{
  $_SESSION['currentselection'] = $selection;
  header("Location: messages.php");
  exit;
}

?>
