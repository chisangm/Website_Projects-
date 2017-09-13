<?php
/* --------------------------------------------------------------------------
template.php: This page contains the functions necessary to display the 
                banner, nav bar and any other component that common amongst
                each individual page. This way, any changes will only need to 
                be done once.
tabsize=2
---------------------------------------------------------------------------*/
require_once("../private/config.php");
require_once("private/database.php");



/*---------------------------------------------------------------------
generate_template_beginning: prints the html page up to the beginning 
              of the content area
Input: 
				$pagename:   which page is calling the function that it can highlight
                     it's navbar column
				$stylesheet_filename:   the name of the .css file that has rules 
                               which are specific to the page. It Assumes the 
                               css files is located in the CSS folder.
----------------------------------------------------------------------*/
function generate_template_beginning($pagename, $stylesheet_filename, $javascript_filename="", $userid="")
{

  /* Get which nav bar item should be highlighted */
  $home = "";
  $textbooksearch = "";
  $messaging = "";
  $inventory = "";
  $contact = "";
  $login = "";


  /* Bold the current page's nav bar item */
  if($pagename == 'home')
    $home = 'id="current"';
  elseif($pagename =='textbooksearch')
    $textbooksearch = 'id="current"';
  elseif($pagename =='messaging')
    $messaging = 'id="current"';
  elseif($pagename =='inventory')
    $inventory = 'id="current"';
  elseif($pagename =='contact')
    $contact = 'id="current"';
  elseif($pagename =="login")
    $login = 'id="current"';

  /* Add javascript, if any */
  if($javascript_filename != "")
  {
    $javascript = '<script type="text/javascript" src="js/'.$javascript_filename.'"></script>';
  }
  else
    $javascript = "";


  /* Determine whether a log in, or log out button is displayed */
  if($userid == "")
  {
    $logbutton = '<a href="login.php" '.$login.'>Login</a>';
  }
  else
  {
    try
    {
      $db = connect_to_db();
      $user = get_user_by_userid($db, $userid);
      $displayname = $user['display_name'];
      $displayname = '<span id="displayname">'.$displayname."</span>";
      $logbutton = '<a href="logout.php" '.$login.'>('.$displayname.') Logout </a>';
    }
    catch(PDOException $e)
    {
      $logbutton = '<a href="logout.php" '.$login.'> Logout </a>';
    }
  }

echo <<<ZZEOF
  <!DOCTYPE html>
  <html>

  <head>
    <title> Salamander Textbook Exchange </title>
    <link rel="stylesheet" type="text/css" href="css/template.css" />
    <link rel="stylesheet" type="text/css" href="css/$stylesheet_filename" />
    <link rel="icon" href="images/favicon.ico" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    $javascript

  </head>

  <body>
  <div id="maincontain">

    <!-- Banner Image -->
    <div id="banner"><img src="images/logo.jpg" alt="Pulpit rock" id="banner"></div>

    <!-- Navigation Bar -->
    <div id="navbar">
      <ul>
       <li class="first" >
          <a href="index.php" $home>Home</a>
       </li>
       <li>
          <a href="search.php" $textbooksearch>Textbook Search</a>
       </li>
       <li>
          <a href="messages.php" $messaging>Messaging/Inbox</a>
       </li>
       <li>
          <a href="inventory.php" $inventory>Inventory</a>
       </li>
       <li>
          <a href="contact.php" $contact>Contact Us</a>
       </li>
       <li>
       $logbutton
       </li>
      </ul>
    </div>

      <!-- Content area start-->
      <div id="contentarea">
ZZEOF;

}


/* -------------------------------------------------------------------------
generate_template_end: prints the html code from the end of the content area
                      to the end of the html file
---------------------------------------------------------------------------*/
function generate_template_end()
{
echo <<<ZZEOF

    </div><!-- Content area end -->


    <!-- Footer -->
    <div id="footer">&copy; 2013 Team Salamander. All Rights Reserved.</div>

  </div> <!-- Main Contain -->

</html>
</body>
ZZEOF;
}

?>
