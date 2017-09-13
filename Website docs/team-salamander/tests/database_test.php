<?php

require_once("../../private/config.php");
require_once("../private/database.php");
/* databasePHP_test.php: Tests both the regenerate_database.php file and the database.php file 
*/

try//database interaction
{

$db = connect_to_db();

/* Fill sample data */
regenerate_database();

/* insert_user()   three times -------------------------------------------*/ 
echo "<br /><br />";
echo "<b>insert_user() (3 times)</b>";
echo "<br />"; 
insert_user($db, "jason@jason.jason", "password1", "Jason");
insert_user($db, "bob@bob.bob", "password2", "Bob");
insert_user($db, "robert@robert.robert", "password3", "Robert");
echo "<br />Success.";

/*get_all_users() -------------------------------------------------------*/ 
echo "<pre>";
echo "<br /><br />";
echo "<b>get_all_users()</b>";
echo "<br />"; 
print_r(get_all_users($db));
echo "</pre>";


/* user_id_exists()------------------------------------------------------ */
echo "<br /><br />";
echo "<b>user_id_exists(6): Expected: id does not exist</b>";
echo "<br />"; 
if(user_id_exists($db, 6))
  echo "id exists";
else
  echo "id does not exist";

/* user_id_exists()----------------------------------------------------- */
echo "<br /><br />";
echo "<b>user_id_exists(4)</b>";
echo "<br />"; 
if(user_id_exists($db, 4))
  echo "id exists";
else
  echo "id does not exist";

/*email_exists()----------------------------------------------------- */
echo "<br /><br />";
echo "<b>email_exists(bob@bob.bob): Expected: true </b>";
echo "<br />"; 
if(email_exists($db, "bob@bob.bob"))
  echo "email exists";
else
  echo "email does not exist";

/* Insert textbooks -----------------------------------------------------*/
echo "<br /><br />";
echo "<b>insert_textbook() twice once for user 3 and once for user 4</b>";
insert_textbook($db, "Intro to CS", "1023010232", "J.K Tolkien", 3, "good cond.", "34.45");
insert_textbook($db, "Intro to CS2", "1023010232", "Richard Stallman", 4, "bad cond.", "88.88");
echo "<br />success";

/* Get all textbooks --------------------------------------------------- */
echo "<pre>";
echo "<br /><br />";echo "<b>get_all_textbooks()</b><br />";
print_r(get_all_textbooks($db));
echo "</pre>";

/* insert_message()---------------------------------------------------- */
echo "<br /><br />";
echo "<b>insert_message(fromuser=3,touser=4, the subject, some body)</b>";
insert_message($db, 3, 4, "the subject", "some body");
echo "<br />success";


/*insert_purchase_request() --------------------------------------------*/
echo "<br /><br />";
echo "<b>insert_purchase_request(buyer=3,seller=4,bookid=1)</b>";
insert_purchase_request($db, 3, 4, 1);
echo "<br />success";

/* get_email() gets email by user id ------------------------------------*/
echo "<br /><br />";
echo "<b>get_email(userid = 5)</b>";
echo "<br />";
echo get_email($db, 5);


/* email_and_password_exists()------------------------------------------- */
echo "<br /><br />";
echo "<b>email_and_password_exists(jason@jason.jason, password1)</b><br />";
if(email_and_password_exists($db,'jason@jason.jason', "password1"))
{
  echo "email and pass exists";
}
else
{
  echo "not exists";
}


/* get_all_textbooks()---------------------------------------------------- */
echo "<pre>";
echo "<br /><br /><b>get_all_textbooks   Expected:2 results</b><br />";
print_r(get_all_textbooks($db));
echo "</pre>";


/* get_all_textbooks_not_removed()----------------------------------------- */
echo "<pre>";
echo "<br /><br /><b>get_all_textbooks_not_removed() Expected: 2 results</b><br />";
print_r(get_all_textbooks_not_removed($db));
echo "</pre>";


/* search_textbooks() (twice)--------------------------------------------- */
echo "<pre>";
echo '<br /><br /><b>search_textbooks("","","J")</b><br />';
print_r(search_textbooks($db, "", "", "J"));

echo '<br /><br /><b>search_textbooks("CSS","102","J") expected: no results</b><br />';
print_r(search_textbooks($db, "CSS", "102", "J"));
echo "</pre>";


/* get_inventory() ------------------------------------------------------*/
echo "<pre>";
echo "<br /><br /><b>get_inventory(3) GET INVENTORY for user_id = 3</b><br />";
print_r(get_inventory($db, 3));
echo "</pre>";


/* textbook_exists----------------------------------------------------- */
echo "<br /><br /><b>textbook_exists(2)</b><br />";
if(textbook_exists($db, 2))
  echo "true";
else
  echo "false";

echo "<br /><br /><b>textbook_exists(3)</b><br />";
if(textbook_exists($db, 3))
  echo "true";
else
  echo "false";

/* search_inventory(userid=1, "Intro", "32", "")-------------------------- */
echo "<pre>";
echo '<br /><br /><b>search_inventory(userid=3, "Intro", "32", "")</b><br />';
print_r(search_inventory($db, 3, "Intro", "32", ""));
echo "</pre>";

/* get_message_inbox(userid=4)--------------------------------------------- */
echo "<pre>";
echo "<br /><br /><b>get_message_inbox(user_id=4)</b><br />";
print_r(get_message_inbox($db, 4));
echo "</pre>";

/* remove_message(id=1)-------------------------------------------------- */
echo "<br /><br /><b>remove_message(id=1)</b><br />";
remove_message($db, 1);
echo "<br />success";

/* get_messsage_inbox() again ---------------------------------------*/
echo "<pre>";
echo "<br /><br /><b>Message Inbox of user_id = 4: Expected none</b><br />";
print_r(get_message_inbox($db, 4));
echo "</pre>";



/* remove_book(id)------------------------------------------------- */
echo "<pre>";
echo "<br /><br /><b>get_all_textbooks_not_removed()</b><br />";
print_r(get_all_textbooks_not_removed($db));
echo "</pre>";

echo "<pre>";
echo "<br /><br /><b>remove_book(1)</b>";
remove_book($db, 1);
echo "<br />success";
echo "<br /><br /><b>get_all_textbooks_not_removed</b><br />";
print_r(get_all_textbooks_not_removed($db));
echo "</pre>";


/* get_all_users() ------------------------------------------------------*/
echo "<pre>";
echo "<br /><br /><b>get_all_users()</b><br /><br />";
print_r(get_all_users($db));
echo "</pre>";

/* modify_user ---------------------------------------------------------*/
echo "<br /><br /><b>modify_user() userid 3</b> <br />";
modify_user($db, 3, "newemail@newemail.com", "newpassword", "newdisplayname");
echo "<br />success";

echo "<pre>";
echo "<br /><br /><b>Get all users, verify user was modified</b><br /><br />";
print_r(get_all_users($db));
echo "</pre>";



/* delete_user() -------------------------------------------------------*/

echo "<br /><br /><b>delete_user(3)</b><br /><br />";
delete_user($db, 3);
echo "<br />success";

echo "<pre>";
echo "<br /><br /><b>get_all_users():verify user is deleted</b><br /><br />";
print_r(get_all_users($db));
echo "</pre>";


 /* get_user_id(by email) --------------------------------------------*/
echo "<br /><br /><b>get_user_id(by email=bob@bob.bob)</b><br />";
echo get_user_id($db, 'bob@bob.bob');


/* get textbook by its ID ------------------------------------------ */
echo "<br /><br /><b>get_textbook_by_id(bookid=2)</b>";
echo "<pre>";
$bookbyid = get_textbook_by_id($db,2);
print_r($bookbyid);
echo "</pre>";

/* get_user_by_userid(id)------------------------------------------------*/
echo "<br /><br /><b>get_user_by_userid(3)</b><br />";
echo "<pre>";
$userbyid = get_user_by_userid($db, 3);
echo "<br />";
echo "User by id";
echo "<br />";
print_r($userbyid);
echo "</pre>";


/* get_all_purchase_requests()------------------------------------------------*/
echo "<br /><br /><b>get_all_purchase_requests(buyer3, seller4, book2)</b><br />";
echo "<pre>";
insert_purchase_request($db, 5, 4, 2);
$prs = get_all_purchase_requests($db);
echo "<br />";
echo "User by id";
echo "<br />";
print_r($prs);
echo "</pre>";


/* get_purchase_request_by_id()------------------------------------------------*/
echo "<br /><br /><b>get_purchase_request_by_id(2)</b><br />";
echo "<pre>";
$prs = get_purchase_request_by_id($db, 2);
echo "<br />";
print_r($prs);
echo "</pre>";

/* search_textbook_range() ------------------------------------------------*/
echo "<br /><br /><b>search_textbook_range(Intro,-,-,0,1) starting at 0, 1 result</b><br />";
echo "<pre>";
$books =search_textbook_range($db, "Intro", "", "", 0, 1);
echo "<br />";
print_r($books);
echo "</pre>";



}//end try
catch(PDOException $e)
{
  echo 'PDO Error: '.$e->getMessage()."\n";
}
