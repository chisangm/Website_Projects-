<?php
/* -------------------------------------------------------------------------
index.php
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
generate_template_beginning('home', 'index.css', "", $userid);


/* Print the content area */
echo <<<ZZEOF
			<!-- Search Area -->
			<div id="rightcolumn">
				<div id="searcharea">
					<h3>Search for a Textbook</h3>
					<form method="post" action="search.php">
						Title: <input type="text" name="title" /><br />
						ISBN: <input type="text" name="isbn" /><br />
						Author: <input type="text" name="author" /><br />
						<input type="submit" value="Search">
					</form>
				</div>
			</div>
      <h1 id="hometitle">Home</h1><br />
				<h3>Welcome to <span id="companyname">Salamander Book Exchange!</span></h3>
				<p>
					Salamander book exchange provides a textbook exchange service to students
					who wish to buy or sell textbooks 
				</p>

				<h4>No Account Required</h4>
				<p>
					Guests may search for a textbook, view the results
					and request to buy it. Registered users have all of the functionality
					that the guests have in addition to the ability to sell textbooks,
					and access to the messaging/inbox system. 
				</p>

				<h4>Manage Inventory with Ease</h4>
				<p>
					Sellers may add and remove
					textbooks using their inventory page and every registered user may
					send messages to the inbox of any other registered user. 
				</p>

				<h4>Messaging system for quick and easy communication</h4>
				<p>
					When a buyer requests to purchase a textbook, the seller will automatically receive
					a message in their inbox about the request along with the buyer's contact information.
					Registered users may send messages 
				</p>

ZZEOF;

/* Print all template html after the content area */
generate_template_end();

?>
