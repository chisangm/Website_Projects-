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


/* Get SESSION Variables */
if(!isset($_SESSION["loggedin"]))
{
 $_SESSION["loggedin"] = false;
}
$loggedin = $_SESSION["loggedin"]; // Whether the user is logged in

if(!isset($_SESSION["userid"]))
{
  $_SESSION['userid'] = "";
}
$userid = $_SESSION["userid"]; // The user's ID

if(!isset($_POST["selection"]))
{
  $bookid = "";
}
else
{
  $bookid = htmlspecialchars($_POST["selection"]); // The selected textbook
}


/* Get POST variables */
$memberpurchase = false; // True if "Purchase" was button pressed
$guestpurchase = false; //True iff "Guest" button was pressed
$guestemail = "";
$nobutton = false; //True if neither button was pressed and URL was visted directly
$badguestemail = false; // True if the user entered an invalid email as a guest

/* Get which (if any) submit buttons were pressed */
if(isset($_POST['btn_purchase']))
{
  if($_POST["btn_purchase"])
    $memberpurchase = true;
}
if(isset($_POST['btn_guest']))
{
  if($_POST["btn_guest"])
    $guestpurchase = true;
}
if(isset($_POST['guest_email']))
{
  $guestemail = htmlspecialchars($_POST['guest_email']); 
}
if(!$guestpurchase && !$memberpurchase)
  $nobutton = true;


/* If no button was pressed, error message---------------------------------- */
if($nobutton)
{
  $_SESSION['backpage'] = 'search.php';
  $_SESSION['responsemessage'] = 'Sorry there was in error in processing your purchase.';
  header('Location: responsepage.php');
  exit;
}


/* If no textbook was selected, error message------------------------------ */
if(!$bookid || $bookid == "")
{
  $_SESSION['backpage'] = 'search.php';
  $_SESSION['responsemessage'] = 'Please select a textbook for purchase.';
  header('Location: responsepage.php');
  exit;
}


/* If member purchase and not logged in, go to login page------------------- */
if($memberpurchase && !$loggedin)
{
  $_SESSION['backpage'] = 'search.php';
  header('Location: login.php');
  exit;
}


/* If member purchase and logged in, purchase book-------------------------- */
if($memberpurchase && $loggedin)
{
  try
  {
    $db = connect_to_db();
    $bookdata = get_textbook_by_id($db, $bookid); // Get the textbook information
    $sellerid = $bookdata['seller_id']; // The book seller's id
    $userdata = get_user_by_userid($db, $userid); //The user's information

    insert_purchase_request($db, $userid, $sellerid, $bookid); 

    /* Send the seller a message about the purchase */
    insert_message($db, $userid, $sellerid, "PURCHASE REQUEST",
                      "User ".$userdata['email']." requests purchase of your listed textbook, ".$bookdata['title']."(id ".$bookdata['id'].")");    

    /* Send a success response */
    $_SESSION['responsemessage'] = 'Seller was notified of your puchase request';
    $_SESSION['backpage'] = 'search.php';
    header('Location: responsepage.php');
    exit;
  }
  catch(PDOException $e) //database error
  {
    $_SESSION['backpage'] = "search.php";
    $_SESSION['responsemessage'] = "Sorry, an error occurred with your purchase,
                                      please contact the system admin";
    header('Location: responsepage.php');
    exit;
  }
}

/* If guest purchase (only displayed if not logged in)------------------ */
if($guestpurchase)
{
  /* If the entered email was invalid, go back to search page with error message */
  if(!filter_var($guestemail, FILTER_VALIDATE_EMAIL))
  {
   $_SESSION['guest_email_error'] = "Invalid email"; 
   header('Location: search.php');
   exit;
  }

  /* email ok, purchase as guest */
  try
  {
    $db = connect_to_db();
    $bookdata = get_textbook_by_id($db, $bookid);
    $sellerid = $bookdata['seller_id']; // The book's seller
  
    /* Send a message to the seller */
    insert_message($db, 1, $sellerid, "GUEST PURCHASE REQUEST", 
        "A guest, ".$guestemail." requests purchase of your listed textbook, ".$bookdata['title']."(id ".$bookdata['id'].")");

    /* Send a success response */
    $_SESSION['responsemessage'] = 'Seller was notified of your puchase request';
    $_SESSION['backpage'] = 'search.php';
    header('Location: responsepage.php');
    exit;
 
  }
  catch(PDOException $e)
  {
    $_SESSION['backpage'] = "search.php";
    $_SESSION['responsemessage'] = "Sorry, an error occurred with your purchase,
                                      please contact the system admin";
    header('Location: responsepage.php');
    exit;
  }
}




?>
