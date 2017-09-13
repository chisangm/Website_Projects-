<?php
/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Inluded files */
require_once('template.php');
require_once('../private/config.php');
require_once('./private/database.php');


// Set session variable if they havent been already 
if(!isset($_SESSION["loggedin"]))
  $_SESSION["loggedin"] = false;

if(!isset($_SESSION["userid"]))
  $_SESSION["userid"] = "";

$_SESSION['errormessage'] = "";


/* Get SESSION Variables */
$loggedin = $_SESSION["loggedin"]; // Whether the user is logged in
$userid = $_SESSION["userid"]; // The user's ID

if(!isset($_SESSION["backpage"]))
  $backpage = "search.php";
else
  $backpage = $_SESSION["backpage"];

/* Get POST variables */
$email = "";
if(isset($_POST['email']))
  $email = htmlspecialchars($_POST['email']);
$password = "";
if(isset($_POST['password']))
  $password = htmlspecialchars($_POST['password']);


/* If already logged in, go to the search page */
if($loggedin)
{
 header("Location: search.php"); 
 exit;
}


/* If no POST data was submitted, go back to the login page */
if($email == "" || $password == "")
{
  $_SESSION["errormessage"] = "Please fill in all fields";
  header("Location: login.php");
  exit;
}






try //database interaction
{ 
  $db = connect_to_db();

  /* Verify the username */
  if(!email_exists($db, $email))
  {
    $_SESSION['errormessage'] = "Username doesn't exist";  
    header("Location: login.php");
    exit;
  }

  /* verify the password */
  if(email_and_password_exists($db, $email, $password))
  {
   $_SESSION['loggedin'] = true;
   $_SESSION['userid'] = get_user_id($db, $email); 
   header('Location: '.$backpage);
   exit;
  }
  else
  {
    $_SESSION['errormessage'] = "Incorrect password";
    header("Location: login.php");
    exit;
  }



}
catch(PDOException $e)
{
  $_SESSION['backpage'] = "search.php";
  $_SESSION['responsemessage'] = "Sorry, an error occurred with your purchase,
                                    please contact the system admin";
  header('Location: responsepage.php');
  exit;
}





?>
