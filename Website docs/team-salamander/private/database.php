<?php

/* database.php: Contains all SQL queries and database library functions
									None of these functions perform validation checks. Those
									will be done at the processing level */

// TABSPACE = 2
//Author: Jason Kreski (kreskj@cs.uwindsor.ca)


/*-----------------------------------------------------------------------------|
List of functions in this file: functionname(args) -> return
-------------------------------------------------------------------------------|
	connect_to_db()

	get_email(db, userid) 		-> string
	get_user_id(db, email) 		-> string

	insert_user(db, email, password, display_name) 
	insert_textbook(db, title, isbn, author, seller_id, description, price)
	insert_message(db, from_id, to_id, subject, message_body)
	insert_purchase_request(db, buyer_id, seller_id, bookid)

	email_exists(db, email) 		-> bool
	user_id_exists(db, id) 		-> bool
	textbook_exists(db, id) 		-> bool
	email_and_password_exists(db, email, password) 		-> bool
  has_message(db, messageid, userid) --> bool

	get_all_users(db) 		-> array of users
  get_all_purchase_requests($db) -> array of purchase requests
	get_all_textbooks(db) 		-> array of all textbooks (newest to oldest)
	get_all_textbooks_not_removed(db) 		-> array of textbooks where remove flag == 0 (newest to oldest)
  get_textbook_by_id(db, bookid)

  get_user_by_userid(db, userid) -> array of user information
  get_purchase_request_by_id(db, id) -> array of purchase request information
	search_textbooks(db, title, isbn, author) 		-> array of textbooks (newest to oldest)
  search_textbook_range(db, title, isbn, author, startindex, numresults)

	get_inventory(db, user_id) 		-> array of textbooks (newest to oldest)
	search_inventory(db, seller_id, title, isbn, author) 		-> array of textbooks (newest to oldest)
	get_message_inbox(db, to_id) 		-> array of messages (newest to oldest)

	remove_message(db, message_id)
	remove_book(db, bookid)

	modify_user(db, id, newemail, newpassword, newdisplayname)
	delete_user(db, id)

	regenerate_database(db);
---------------------------------------------------------------------------------*/



/*-----------------------------------------------------------------
connect_to_db: Connects to the database and returns 
								the connection object 
-----------------------------------------------------------------*/
function connect_to_db()
{
	global $db_host;
	global $db_name;
	global $db_username;
	global $db_password;

	$conn = false;

		/* Connect to the database */
		$conn = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_username, $db_password);

		/* Disable the emulatrion of prepared statements */
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		/* Return the connection object*/
		return $conn;

	return $conn;
}




/* -------------------------------------------------------------------------------------
get_email: takes a user_id and retrieves it's cooresponding email. It assumes the id exists.
-------------------------------------------------------------------------------------*/
function get_email($db, $user_id)
{
	$sql = "SELECT email FROM users where id = :user_id";
	$st = $db->prepare($sql);
	$st->execute(array(':user_id'=>$user_id));
	$us = $st->fetchAll();
	return $us[0]['email'];
}

/* -------------------------------------------------------------------------------------
get_user_id: takes an email and retrieves it's cooresponding user id. It assumes the email exists
-------------------------------------------------------------------------------------*/
function get_user_id($db, $email)
{
	$sql = "SELECT id FROM users where email = :email";
	$st = $db->prepare($sql);
	$st->execute(array(':email'=>$email));
	$us = $st->fetchAll();
	return $us[0]['id'];

}

/* -----------------------------------------------------------------------
insert_user: inserts a user into the database
CONSTRAINT: email must not already exist or it will be an error
-------------------------------------------------------------------------*/
function insert_user($db, $email, $password, $display_name)
{
	/* Hash the password */
	$password = md5($password);

	/* Create the query */
	$sql = 'INSERT INTO users (email, password, display_name) VALUES (:email, :password, :display_name)';

	/* Execute the query */
	$st = $db->prepare($sql);
	$st->execute(array(':email' =>$email, ':password'=> $password, ':display_name'=> $display_name));
}



/* -----------------------------------------------------------------------
insert_textbook: inserts a textbook into the textbooks table
	input: title,isbn,author,seller_email,description(strings)
					price must be in the form of "333.33" maximum 5 digits
	CONSTRAINT: $seller_email MUST exist the USERS table or it will be an error
-------------------------------------------------------------------------*/
function insert_textbook($db, $title, $isbn, $author, $seller_id, $description, $price)
{
	/* Create the query */
	$sql = "INSERT INTO textbooks (title, isbn, author, seller_id, description, price, date_time, removed) 
	VALUES (:title, :isbn, :author, :seller_id, :description, :price, NOW(), 0)";

	/* Execute the query */
	$st = $db->prepare($sql);
	$st->execute(array(':title' =>$title, ':isbn'=> $isbn, ':author'=> $author,
									 ':seller_id' => $seller_id, ':description'=> $description, ':price'=>$price));
}



/* -----------------------------------------------------------------------
insert_message: inserts a message into the messages table
		CONSTRAINT: $from_email and $to_email MUST exist in users 
									or it will be an error.
-------------------------------------------------------------------------*/
function insert_message($db, $from_id, $to_id, $subject, $message_body)
{
	/* Create the query */
	$sql = 'INSERT INTO messages (from_id, to_id, subject, message_body, date_time) 
					VALUES (:from_id, :to_id, :subject, :message_body, NOW())';

	/* Execute the query */
	$st = $db->prepare($sql);
	$st->execute(array(':from_id' =>$from_id, ':to_id'=> $to_id,
										 ':subject'=>$subject, ':message_body'=> $message_body));
}



/* -----------------------------------------------------------------------
insert_purchase_require: inserts a purchase request into the purchase_...
													requests table.
-------------------------------------------------------------------------*/
function insert_purchase_request($db, $buyer_id, $seller_id, $bookid)
{
	/* Create the query */
	$sql = 'INSERT INTO purchase_requests (buyer_id, seller_id, bookid, date_time) 
					VALUES (:buyer_id, :seller_id, :bookid, NOW())';

	/* Execute the query */
	$st = $db->prepare($sql);
	$st->execute(array(':buyer_id' =>$buyer_id, ':seller_id'=> $seller_id,
										 ':bookid'=> $bookid));
}



/*-------------------------------------------------------------------
email_exists: takes an email and returns true iff it exists in the users
							table
--------------------------------------------------------------------*/
function email_exists($db, $email)
{
	$sql = 'SELECT * FROM users where email=:email';
	$st = $db->prepare($sql);
	$st->execute(array(':email'=>$email));
	$us = $st->fetchAll();
	return (count($us) >= 1);
}


/*------------------------------------------------------------------
user_id_exists: takes a user ID and returns true iff it exists in the 
									user's table
-----------------------------------------------------------------*/
function user_id_exists($db, $user_id)
{
	$sql = 'SELECT * FROM users where id=:id';
	$st = $db->prepare($sql);
	$st->execute(array(':id'=>$user_id));
	$us = $st->fetchAll();
	return (count($us) >= 1);
}


/*--------------------------------------------------------------------- 
textbooks_exits: returns true iff the texbook id exists in the database 
											(useful for validation checking) 
---------------------------------------------------------------------*/
function textbook_exists($db, $id)
{
	$sql = 'SELECT * FROM textbooks where id=:id';
	$st = $db->prepare($sql);
	$st->execute(array(':id'=>$id));
	$us = $st->fetchAll();
	return (count($us) >=1);
}

/*-------------------------------------------------------------------
exists_email_and_password: takes an email and password combination and
														checks it exists in the database
														(useful for log in validation)
--------------------------------------------------------------------*/
function email_and_password_exists($db, $email, $password)
{
	$password = md5($password);
	$sql = "SELECT * FROM users where email=:email and password=:password";
	$st = $db->prepare($sql);
	$st->execute(array(':email'=>$email, ':password'=>$password));
	$us = $st->fetchAll();
	return (count($us) >= 1);
}

/* -------------------------------------------------------------------------------------
has_message: checks is a message belongs to a particular user
-------------------------------------------------------------------------------------*/
function has_message($db, $user_id, $message_id)
{
	$sql = "SELECT * FROM messages where to_id= :to_id and id= :message_id";
	$st = $db->prepare($sql);
	$st->execute(array(':to_id'=>$user_id, ':message_id'=>$message_id));
	$us = $st->fetchAll();
	return $us;
}

/* ---------------------------------------------------------------------- 
	get_all_users: returns an array with a list of all users 
						(useful for debugging the ADMIN page)
------------------------------------------------------------------------*/
function get_all_users($db)
{
 $sql = "SELECT * FROM users";
 $st= $db->prepare($sql);
 $st->execute();
 $us = $st->fetchAll();
 return $us;
}

/* ---------------------------------------------------------------------- 
	get_all_purchase_requests(): returns a 2D array of all purchase requests
------------------------------------------------------------------------*/
function get_all_purchase_requests($db)
{
 $sql = "SELECT * FROM purchase_requests";
 $st= $db->prepare($sql);
 $st->execute();
 $us = $st->fetchAll();
 return $us;
}

/*-------------------------------------------------------------------
get_all_textbooks: returns a 2D array of textbooks with each row 
										representing one textbook.
--------------------------------------------------------------------*/
function get_all_textbooks($db)
{
	$sql = 'SELECT * FROM textbooks ORDER BY date_time DESC';
	$st = $db->prepare($sql);
	$st->execute();
	$us = $st->fetchAll();
	return $us;
}


/* -------------------------------------------------------------------------------------
get_all_textbooks_not_removed: Returns an array of all
																	 textbooks that have not been removed
-------------------------------------------------------------------------------------*/
function get_all_textbooks_not_removed($db)
{

	$sql = 'SELECT * FROM textbooks where removed=0 ORDER BY date_time DESC';
	$st = $db->prepare($sql);
	$st->execute();
	$us = $st->fetchAll();
	return $us;
}



/* -------------------------------------------------------------------------------------
get_textbook_by_bookid() Returns an array of all
																	 textbooks that have not been removed by user id
-------------------------------------------------------------------------------------*/
function get_textbook_by_id($db, $bookid)
{

	$sql = 'SELECT * FROM textbooks where removed=0 and id='.$bookid;
	$st = $db->prepare($sql);
	$st->execute();
	$us = $st->fetchAll();
	return $us[0];
}

/* -------------------------------------------------------------------------------------
get_user_by_userid() Returns an array of all
																	 textbooks that have not been removed by user id
-------------------------------------------------------------------------------------*/
function get_user_by_userid($db, $userid)
{

	$sql = 'SELECT * FROM users where id='.$userid;
	$st = $db->prepare($sql);
	$st->execute();
	$us = $st->fetchAll();
	return $us[0];
}

/* -------------------------------------------------------------------------------------
get_purchase_request_by_id() Returns an array of all
																	 textbooks that have not been removed by user id
-------------------------------------------------------------------------------------*/
function get_purchase_request_by_id($db, $id)
{

	$sql = 'SELECT * FROM purchase_requests where id='.$id;
	$st = $db->prepare($sql);
	$st->execute();
	$us = $st->fetchAll();
	return $us[0];
}

/*-------------------------------------------------------------------
search_textbooks: 	returns a 2D array of textbooks with each row 
										representing one textbook. 
										
										Note: If title,isbn and/or author are not included
										in the search, supply them with empty strings 
										and they won't be included in the query.

										If all 3 strings are empty, this query returns
										nothing. See "get all textbooks" for getting 
										them all.

										This search is by SUBSTRING. So a search
										of title="Computer" will return restults such as
										title="Intro to Computer Science" becuase "computer"
										exists in the title.
--------------------------------------------------------------------*/
function search_textbooks($db, $title, $isbn, $author)
{
	$titlekey = ""; //sql colomn name
	$isbnkey = ""; //sql column name 
	$authorkey = ""; //sql column name
	$titlevalue = ""; // sql column value
	$isbnvalue = ""; // sql column value
	$authorvalue = ""; // sql column value

	/* All empty strings returns nothing */
	if($title == "" && $isbn == "" && $author == "")
	{
		$titlekey = "1";
		$isbnkey = "1";
		$authorkey = "1";
		$titlevalue = "0";
		$isbnvalue = "0";
		$authorvalue = "0";
	}
	else // If not an empty search
	{

		if($title == "") // No search by title
		{
			$titlekey = '1';
			$titlevalue = '1';
		}
		else // Include search by title
		{
			$titlekey = 'title';
			$titlevalue = "%".$title."%"; 
		}

		if($isbn == "") // No search by isbn
		{
			$isbnkey = '1';
			$isbnvalue = '1';
		}
		else // include search by isbn
		{
			$isbnkey = 'isbn';
			$isbnvalue = "%".$isbn."%";
		}

		if($author == "") // no search by author
		{
			$authorkey = '1';
			$authorvalue = '1';
		}
		else // include search by author
		{
			$authorkey = 'author';
			$authorvalue = "%".$author."%";
		}
	}

		/* Run the query */
		$sql = "SELECT * FROM textbooks where 
						".$titlekey." LIKE :title and ".$isbnkey." LIKE :isbn and 
						".$authorkey." LIKE :author and removed=0
						ORDER BY date_time DESC";

		$st = $db->prepare($sql);
		$st->execute(array(':title' => $titlevalue, ':isbn'=>$isbnvalue, ':author'=>$authorvalue));
		$us = $st->fetchAll();
		return $us;
}


/*-------------------------------------------------------------------
search_textbook_range: 	returns a 2D array of textbooks with each row 
										representing one textbook. Returns a range of
                    results. Example (30-40) useful for breaking
                    apart long results.
                    If you want to display results 30-40 then youd pass in
                    30, 10 for $numstart and $numresults  
--------------------------------------------------------------------*/
function search_textbook_range($db, $title, $isbn, $author, $numstart, $numresults)
{
	$titlekey = ""; //sql colomn name
	$isbnkey = ""; //sql column name 
	$authorkey = ""; //sql column name
	$titlevalue = ""; // sql column value
	$isbnvalue = ""; // sql column value
	$authorvalue = ""; // sql column value

	/* All empty strings returns nothing */
	if($title == "" && $isbn == "" && $author == "")
	{
		$titlekey = "1";
		$isbnkey = "1";
		$authorkey = "1";
		$titlevalue = "0";
		$isbnvalue = "0";
		$authorvalue = "0";
	}
	else // If not an empty search
	{

		if($title == "") // No search by title
		{
			$titlekey = '1';
			$titlevalue = '1';
		}
		else // Include search by title
		{
			$titlekey = 'title';
			$titlevalue = "%".$title."%"; 
		}

		if($isbn == "") // No search by isbn
		{
			$isbnkey = '1';
			$isbnvalue = '1';
		}
		else // include search by isbn
		{
			$isbnkey = 'isbn';
			$isbnvalue = "%".$isbn."%";
		}

		if($author == "") // no search by author
		{
			$authorkey = '1';
			$authorvalue = '1';
		}
		else // include search by author
		{
			$authorkey = 'author';
			$authorvalue = "%".$author."%";
		}
	}

		/* Run the query */
		$sql = "SELECT * FROM textbooks where 
						".$titlekey." LIKE :title and ".$isbnkey." LIKE :isbn and 
						".$authorkey." LIKE :author and removed=0
						ORDER BY date_time DESC LIMIT $numstart, $numresults";

		$st = $db->prepare($sql);
		$st->execute(array(':title' => $titlevalue, ':isbn'=>$isbnvalue, ':author'=>$authorvalue));
		$us = $st->fetchAll();
		return $us;
}

/*-------------------------------------------------------------------
get_inventory: Gets a sellers inventorys as a 2D list of textbooks
--------------------------------------------------------------------*/
function get_inventory($db, $seller_id)
{
	$sql = 'SELECT * FROM textbooks where seller_id= :seller_id ORDER BY date_time DESC';
	$st = $db->prepare($sql);
	$st->execute(array(':seller_id'=> $seller_id));
	$us = $st->fetchAll();
	return $us;
}


/*-------------------------------------------------------------------------------------
search_inventory: Searches a user's inventory for a textbook. It uses the same method 
									as search_textbooks() except it's applied to one user's textbooks
-------------------------------------------------------------------------------------*/
function search_inventory($db, $seller_id, $title, $isbn, $author)
{
	/* Copy and pasted code from "search_textbooks" I can't just wrap it in another function because of how the
				prepared statement works */
	$titlekey = ""; //sql colomn name
	$isbnkey = ""; //sql column name 
	$authorkey = ""; //sql column name
	$titlevalue = ""; // sql column value
	$isbnvalue = ""; // sql column value
	$authorvalue = ""; // sql column value

	/* All empty strings returns nothing */
	if($title == "" && $isbn == "" && $author == "")
	{
		$titlekey = "1";
		$isbnkey = "1";
		$authorkey = "1";
		$titlevalue = "0";
		$isbnvalue = "0";
		$authorvalue = "0";
	}
	else // If not an empty search
	{

		if($title == "") // No search by title
		{
			$titlekey = '1';
			$titlevalue = '1';
		}
		else // Include search by title
		{
			$titlekey = 'title';
			$titlevalue = "%".$title."%"; 
		}

		if($isbn == "") // No search by isbn
		{
			$isbnkey = '1';
			$isbnvalue = '1';
		}
		else // include search by isbn
		{
			$isbnkey = 'isbn';
			$isbnvalue = "%".$isbn."%";
		}

		if($author == "") // no search by author
		{
			$authorkey = '1';
			$authorvalue = '1';
		}
		else // include search by author
		{
			$authorkey = 'author';
			$authorvalue = "%".$author."%";
		}
	}
		/* Run the query */
		$sql = "SELECT * FROM textbooks where ".$titlekey." LIKE :title and 
		".$isbnkey." LIKE :isbn and ".$authorkey." 
		LIKE :author and seller_id = :seller_id ORDER BY date_time DESC";

		$st = $db->prepare($sql);
		$st->execute(array(':title' => $titlevalue, ':isbn'=>$isbnvalue, ':author'=>$authorvalue, ':seller_id'=>$seller_id));
		$us = $st->fetchAll();
		return $us;
}



/* -------------------------------------------------------------------------------------
get_message_inbox: returns and array of message records that are associated with a user.
-------------------------------------------------------------------------------------*/
function get_message_inbox($db, $user_id)
{
	$sql = "SELECT * FROM messages where to_id= :to_id ORDER BY date_time DESC";
	$st = $db->prepare($sql);
	$st->execute(array(':to_id'=>$user_id));
	$us = $st->fetchAll();
	return $us;
}


/*-------------------------------------------------------------------------------------
remove_message: removes a message by it's id.
-------------------------------------------------------------------------------------*/
function remove_message($db, $id)
{
	$sql = "DELETE from messages where id = :id";
	$st = $db->prepare($sql);
	$st->execute(array(':id'=>$id));
	return;
}



/*-------------------------------------------------------------------------------------
remove_book: removes a book by it's id
-------------------------------------------------------------------------------------*/
function remove_book($db, $bookid)
{
	$sql = "UPDATE textbooks SET removed = 1 where id = :id";
	$st = $db->prepare($sql);
	$st->execute(array(':id'=>$bookid));
	return;
}

/*-------------------------------------------------------------------------------------
modify_user: takes a user id, and updates it with a new email, password and display name.
-------------------------------------------------------------------------------------*/
function modify_user($db, $user_id, $new_email, $new_password, $new_display_name)
{
	$new_password = md5($new_password);
	$sql = "UPDATE users SET email=:new_email, password=:new_password, display_name=:new_display_name WHERE id = :user_id";
	$st = $db->prepare($sql);
	$st->execute(array(':new_email'=>$new_email, ':new_password'=>$new_password, ':new_display_name'=>$new_display_name, ':user_id' => $user_id));
	return;
}


/* -------------------------------------------------------------------------------------
		delete_user: removes the user from the user's table as well as it 
		removes ALL records in ALL other tables which have a reference to this user.
		So all associated textbooks, purchase_requests, and messages will also be deleted 
		-------------------------------------------------------------------------------------*/
function delete_user($db, $user_id)
{
	$sql = "DELETE FROM purchase_requests where buyer_id = :user_id1 or seller_id = :user_id2";
	$st = $db->prepare($sql);
	$st->execute(array(':user_id1'=>$user_id, ':user_id2'=>$user_id));

	$sql = "DELETE FROM messages where from_id = :user_id1 or to_id = :user_id2";
	$st = $db->prepare($sql);
	$st->execute(array(':user_id1'=>$user_id, ':user_id2'=>$user_id));


	$sql = "DELETE FROM textbooks where seller_id = :user_id";
	$st = $db->prepare($sql);
	$st->execute(array(':user_id'=>$user_id));

	$sql = "DELETE FROM users where id = :user_id";
	$st = $db->prepare($sql);
	$st->execute(array(':user_id'=>$user_id));
}


/*-----------------------------------------------------------------
 regenerate_database.php 
 This file, when ran will drop all the tables
 created by this application and recreate them with empty data. 
 This is useful for debugging and going back to a fresh state. This 
 will also include a function to fill the database with false "test"
 data for use in debugging the database i
 Author: Jason Kreski (kreskij@uwindsor.ca)
 ----------------------------------------------------------------*/
/*  ------------------------------------------------------------
regenerate_database: take the database connection object and
				 drops all tables in the database and 
				recreates them to contain no data.
	------------------------------------------------------------*/
function regenerate_database($db)
{
	/* SQL code to drop the tables -----------*/
	$purchase_requests_drop = "DROP TABLE IF EXISTS purchase_requests";
	$messages_drop = "DROP TABLE IF EXISTS messages";
	$textbooks_drop = "DROP TABLE IF EXISTS textbooks";
	$users_drop = "DROP TABLE IF EXISTS users";

	/* SQL code to create tables -----------*/
	/* users table*/
	$users_create = <<<ZZEOF
	CREATE TABLE users
	(
		id int NOT NULL AUTO_INCREMENT,
		email varchar(255) NOT NULL,
		password varchar(255) NOT NULL,
		display_name varchar(255) NOT NULL,

		PRIMARY KEY (id)
	)
ZZEOF;

	$textbooks_create = <<<ZZEOF
	CREATE TABLE textbooks
	(
		id int NOT NULL AUTO_INCREMENT,
		title varchar(512) NOT NULL,
		isbn varchar(255) NOT NULL,
		author varchar(512) NOT NULL,
		seller_id int NOT NULL,
		description text NOT NULL,
		price decimal(5,2) NOT NULL,
		date_time DATETIME NOT NULL,
		removed tinyint NOT NULL,
		PRIMARY KEY(id),
		FOREIGN KEY(seller_id) REFERENCES users(id)
	)
ZZEOF;

	/* messages table */
	$messages_create = <<<ZZEOF
	CREATE TABLE messages
	(
		id int NOT NULL AUTO_INCREMENT,
		from_id int NOT NULL,
		to_id int NOT NULL,
		subject varchar(255) NOT NULL,
		message_body varchar(255) NOT NULL,
		date_time DATETIME NOT NULL,

		PRIMARY KEY (id),
		FOREIGN KEY(to_id) REFERENCES users(id),
		FOREIGN KEY(from_id) REFERENCES users(id)
	)
ZZEOF;


	$purchase_requests_create = <<<ZZEOF
	CREATE TABLE purchase_requests
	(
		id int NOT NULL AUTO_INCREMENT,
		buyer_id int NOT NULL,
		seller_id int NOT NULL,
		bookid int NOT NULL,
		date_time DATETIME NOT NULL,

		PRIMARY KEY (id),
		FOREIGN KEY(buyer_id) REFERENCES users(id),
		FOREIGN KEY(seller_id) REFERENCES users(id),
		FOREIGN KEY(bookid) REFERENCES textbooks(id)
	)
ZZEOF;


	/* Run all queries ----------------*/

	$db = connect_to_db();

	/* Drop tables */
	$db->exec($purchase_requests_drop);
	$db->exec($messages_drop);
	$db->exec($textbooks_drop);
	$db->exec($users_drop);

	/* Create tables */
	$db->exec($users_create);
	$db->exec($textbooks_create);
	$db->exec($messages_create);
	$db->exec($purchase_requests_create);

	echo "Database Regenerated Sucessfully<br />";

  insert_user($db, "GUEST", "", "GUEST");
  global $admin_password;
  insert_user($db, "ADMIN", $admin_password, "ADMIN");
	return;
}

/* fill_sample_data(): fills the database with sample data */
function fill_sample_data()
{
  /* 
    insert_user(db, email, password, display_name) 
    insert_textbook(db, title, isbn, author, seller_id, description, price)
    insert_message(db, from_id, to_id, subject, message_body)
    insert_purchase_request(db, buyer_id, seller_id, bookid)
    */

    $db = connect_to_db();
    regenerate_database($db);

    
    insert_user($db, "bob@bob.ca", "pass1", "Bob");
    insert_user($db, "ralf@ralf.ca", "pass2", "Ralf");
    insert_user($db, "ron@ron.ca", "pass3", "Ron");
    insert_user($db, "cathy@cathy.ca", "pass4", "Cathy");
    insert_user($db, "melissa@melissa.ca", "pass5", "Missy");
    insert_user($db, "arnold@arnold.ca", "pass6", "Arney");
    insert_user($db, "katie@katie.ca", "pass7", "Katie");
    /* User with long informtion */
    insert_user($db, "jasfidofjoajfdaio;@dfjiaosdjiaosjdi.sajfdio", "jdsa", "fjsadiojjfadsio");


    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS2', '123456788', 'bobby bob2', '3', 'good cond.', '35.55');
    insert_textbook($db, 'Intro to CS3', '123456789', 'bobby bob3', '3', 'good cond.', '36.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '2', 'good cond.', '37.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS1', '123456787', 'bobby bob', '3', 'good cond.', '34.55');
    insert_textbook($db, 'Intro to CS2', '123456788', 'bobby bob2', '3', 'good cond.', '38.55');
    insert_textbook($db, 'Intro to CS3', '123456789', 'bobby bob3', '4', 'good cond.', '39.55');
    /* A textbook with long information for testing purposes */
    insert_textbook($db, 'IntrotoCS3 Probability and Statistics thisis a longtitleoneword', '1234567890123', 'bobbybobbobbobhasalongname', '8', 'goodcondalongdescriptioniswhatthistextbookhashopefullyitfits', '139.55');

    
    insert_message($db, 1, 5, 'sample subject', 'sample message1');
    insert_message($db, 1, 5, 'sample subject', 'sample message2');
    insert_message($db, 1, 5, 'sample subject', 'sample message3');
    insert_message($db, 1, 3, 'sample subject', 'sample message4');
    insert_message($db, 1, 3, 'sample subject', 'sample message4');
    /* A long message to a long user */
    insert_message($db, 1, 8, 'sample subject this subject is deliberately really long for the purpose of testing the user interface', 'sample message4 this message is deliberately really long in order to test the user interface, hopefully it works with this really long message or it will make the users unhappy. We dont want unhappy user because they will go and use facebook to exchange textbooks. Now for a really long string to see how it breaks words: djasjdfkasjdkjfalkdjfksa;jdklkasdjfkla;sjdklfajkdsjfalskdjf;sadljfa;lskdjl;fjasdjfsadl;fjsadjfklasjlkjfklasjdklfa;lkdjfa;lkdjf;laksdj;flaskdjfl;ajskldjfklasjdkfjas;kldjfla;djkfal');

    insert_purchase_request($db, 3, 4, 1);
    insert_purchase_request($db, 3, 4, 2);
    insert_purchase_request($db, 3, 2, 3);
    insert_purchase_request($db, 4, 3, 1);
    insert_purchase_request($db, 5, 4, 1);

    /* A purchase request to a long user with a long textbook */
    insert_purchase_request($db, 8, 3, 6); 

    echo "data created sucessfully";
  }
?>
