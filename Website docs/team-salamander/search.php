<?php
/* search.php: A page to search for, and view search results of textbooks 
  Tabsize=2
*/

/* Start Session */
if(!isset($_SESSION))
{
session_start();
}

/* Inluded files */
require_once('template.php');
require_once('../private/config.php');
require_once('private/database.php');


/* INPUT --------------------------------------------------*/
/*--------------------------------------------------------*/

/* Store loggedin and userid */
if(!isset($_SESSION['loggedin']))
  $_SESSION['loggedin'] = false;

if(!isset($_SESSION['userid']))
  $_SESSION['userid'] = "";
$loggedin = $_SESSION['loggedin']; 
$userid = $_SESSION['userid']; 


/*--- Get the search terms from POST or SESSION data---- */
// title 
if(isset($_POST['title']))
{
  $titlesearch = htmlspecialchars($_POST['title']);
  $_SESSION['titlesearch'] = $titlesearch; 
}
elseif(isset($_SESSION['titlesearch']))
  $titlesearch = $_SESSION['titlesearch'];
else
  $titlesearch = "";

// isbn
if(isset($_POST['isbn']))
{
  $isbnsearch = htmlspecialchars($_POST['isbn']);
  $_SESSION['isbnsearch'] = $isbnsearch;
}
elseif(isset($_SESSION['isbnsearch']))
{
  $isbnsearch = $_SESSION['isbnsearch'];
}
else
  $isbnsearch = "";

// author 
if(isset($_POST['author']))
{
  $authorsearch = htmlspecialchars($_POST['author']);
  $_SESSION['authorsearch'] = $authorsearch;
}
elseif(isset($_SESSION['authorsearch']))
  $authorsearch = $_SESSION['authorsearch'];
else
  $authorsearch = "";


/* $_GET results page number */
if(!isset($_GET['pagenumber']))
  $pagenumber = 1;
elseif($_GET['pagenumber'] < 1)
  $pagenumber = 1;
else
  $pagenumber = $_GET['pagenumber'];

/* Get the guest email validate message */
$guest_email_error = "";
if(isset($_SESSION["guest_email_error"]))
  $guest_email_error = $_SESSION["guest_email_error"];


/* Unset all unnecessary $_SESSION variables */
foreach($_SESSION as $key => $val)
{
  if($key !== 'loggedin' && $key !== 'userid' && $key !=='titlesearch' &&
       $key !=='isbnsearch' && $key !=='authorsearch')
  {
    unset($_SESSION[$key]);
  }
}

/* PROCESSING ---------------------------------------------*/
/*---------------------------------------------------------*/


/* Determine which "Purchase" buttons to display based on if the user is 
    logged in or not */
if(!$loggedin) // Not logged in: both purchase and purchase as guest
{
  $submit_buttons =  '
  <p>'.$guest_email_error.'</p>
    <input type="submit" name="btn_guest" value="Purchase as Guest"
          class="submit" />
    <input type="text" name="guest_email" placeholder="Email" id="guest_email" />
    <br />
          <input type="submit" name="btn_purchase" value="Purchase as User" class="submit" />';
}
else // logged in, only Purchase
{
   $submit_buttons =  '<input type="submit" name="btn_purchase" value="Purchase" class="submit" />';
}


try //Database interaction
{
    /* connnect to the database */
    $db = connect_to_db();

    /* Set the maximum number of results per page */
    $maxresults = 20;

    /* Store the results for this page  */
    $pagestart = ($pagenumber-1)*$maxresults; // The record number to start at
    $results = search_textbook_range($db, $titlesearch, $isbnsearch, $authorsearch
                                      ,$pagestart, $maxresults);
}
catch(PDOException $e)
{
  /* Go to response page with an error message */
  $_SESSION["responsemessage"] = "Sorry, we could not process your request.
  Please try again later. If your request is still unsuccessful, please
  contact the website administrator";

  $_SESSION["backpage"] = "search.php";
  header("Location: responsepage.php");
  exit;
}

/* If no results were found, store a "no results found message" */
$noresults = "";
if(count($results) < 1)
  $noresults = '<tr id="noresults"><td colspan="7">No results found.</td></tr>';

/* Store the "prev" button is there are previous results to show */
$prevbutton = "";
$prev_pagenumber = $pagenumber-1;

if($prev_pagenumber > 0)
{
  $prev_pagestart = ($prev_pagenumber-1)*$maxresults;
  $prevresults = search_textbook_range($db, $titlesearch, $isbnsearch, 
                            $authorsearch, $prev_pagestart, $maxresults);

  $has_prevresults = (count($prevresults) > 0);
  if($has_prevresults)
     $prevbutton = '<a href="search.php?pagenumber='.$prev_pagenumber.'">Prev</a>';
}

/* Store "next" button if there are more results to show */
$next_pagenumber = $pagenumber + 1;
$next_pagestart = ($next_pagenumber - 1)*$maxresults;
$moreresults = search_textbook_range($db, $titlesearch, $isbnsearch,
                             $authorsearch, $next_pagestart,$maxresults); 

$has_moreresults = (count($moreresults) > 0);

$nextbutton = "";
if($has_moreresults)
{
 $nextbutton = '<a href="search.php?pagenumber='.$next_pagenumber.'">Next</a>';
}

/*----HTML OUTPUT--------------------------------------------*/
/*-----------------------------------------------------------*/

/* Print all template html before the content area */
generate_template_beginning('textbooksearch', 'search.css', "",  $userid);



/* Print the search area */
echo <<<ZZEOF
  <div id="searcharea">
    <h3>Search for a Textbook</h3>
    <form method="post" action="search.php">
      Title: <input type="text" name="title" value="$titlesearch" />
      ISBN: <input type="text" name="isbn" value="$isbnsearch" />
      Author: <input type="text" name="author" value="$authorsearch" />
      <input type="submit" value="Search" id="btnsearch">
    </form>
  </div> 
ZZEOF;
/* Print table column headers */
echo <<<ZZEOF
<form action="processpurchase.php" method="post">
  <h3 id="results_title"> Results: </h3>
  <table id="result_table">
    <tr id="tbltitlebar">
      <td class="radiocell"></td>
      <td class="tbltitle">Title</td>
      <td class="tblisbn">ISBN</td>
      <td class="tblauthor">Author</td>
      <td class="tblseller_email">Seller Email</td>
      <td class="tbldesc">Description</td>
      <td class="tblprice">Price</td>
    </tr>
ZZEOF;

/* Print the table rows with textbook results */
foreach($results as $book)
{
  // Store the textbook information in variables for easy substitution 
  $id = $book['id'];
	$title = $book['title'];
	$isbn = $book['isbn'];
	$author = $book['author'];
	$seller_email = get_email($db, $book['seller_id']);
	$description = $book['description'];
	$price = $book['price'];

  /* print*/
  echo<<<ZZEOF
    <tr>
      <td class="radiocell"><input type="radio" name="selection" value="$id"></td>
      <td class="title_result">$title</td>
      <td class="isbn_result">$isbn</td>
      <td class="author_result">$author</td>
      <td class="seller_result">$seller_email</td>
      <td class="description_result">$description</td>
      <td class="price"_result>$price</td>
    </tr>
ZZEOF;
}

/* Print the remainder of the content area */
echo<<<ZZEOF

  $noresults
  </table>
<!-- The Page number area -->
<p id="pagenumber">
$prevbutton
Page $pagenumber
$nextbutton
</p>
 $submit_buttons
 <br />
</form>

ZZEOF;

/* Print all template html after the content area */
generate_template_end();
?>
