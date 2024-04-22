  

#  Welcome to Flux 🚀

## What is Flux?

Flux is a barebones yet versatile MVC PHP framework crafted for rapid web development. Its streamlined architecture allows you to quickly get up and running, providing essential tools without unnecessary complexity. Whether you're building a small project or a large-scale application, Flux offers the perfect balance of simplicity and flexibility. Embrace the efficiency of minimalism with Flux and propel your web development endeavours forward with ease.

## What can Flux do?

Flux distinguishes itself with its built-in functions, tailored to swiftly launch web applications or seamlessly produce APIs. Unlike many frameworks, Flux prioritizes efficiency without sacrificing functionality. Whether you're embarking on a web application venture or crafting APIs for seamless data exchange, Flux's intuitive features expedite the development process.

## Getting started

Flux is still in early stages, but out the box, when you have downloaded the project, you will have two views, one in a Home folder, and another in an About folder. If you navigate to "/about ", you will be greeted by some HTML on the page. When viewing the index.php in the About folder, we can see we are including both the header & footer, along with loading in a partial from the partial folder.

On the "/" or home path of the web app, you will be greeted with "No data". In the controllers folder, there are some functions which will create the boilerplate "User" table which points to the "User" class in the models folder.

In the routes folder, routes.php holds all the routing for the application. 

## Database Configuration

Inside the config folder, there is a config file where we can set the credentials of the database.

	class  Configuration  {

		public  static  $connection  =  [
			"servername"  =>  "localhost",
			"username"  =>  "root",
			"password"  =>  "",
			"database"  =>  ""
		];

	}

We can use the values like such:

	Configuration::$connection["servername"];
	Configuration::$connection["username"];
	Configuration::$connection["password"];
	Configuration::$connection["database"];

## **Loading in a view**

The view function is looking inside of the views folder, About, then index.php is being loaded. Notice '*.php*' is optional. In the routes file, on a 'GET' request we are loading in the file.

	// About Controller
	class  AboutController  {
		static  function  index()  {
			// Load index.php file in views/About folder
			view("About/index");
		}
	}

	// Routes file
	Http::get('/about',  function()  {
		$About  =  new  AboutController();
		$About->index();
	});


## Creating the Users table

**A post request to '/update-table' will create the User table**

A callback function can be optionally made

	Http::post('/update-table',  function()  {
		$User  =  new  Flux();
		
		$User->MigrateTable('User',  function()  {
			echo  Success('User table updated');
		});
	});


## Request Methods

In the routes file, there are already a few request examples being used, lile 'Http::get()'. There a few we can use in Flux...
	
	Http::get()
	Http::post()
	Http::path()
	Http::put()
	Http::delete()
	Http::patch()
	
## Flux API 🚀

If we take a look in the routes file, there a few methods to show how Flux works. 

	Http::get("/",  function()  {
		$User  =  new  UserController();
		$id  =  QueryString('id');

		if(!$id)  {
			// Get All People
			$User->GetAllUsers();
		}

		else  {
			// Get Single Person
			$User->GetUser($id);
		}

	});


	Http::post("/create-user",  function()  {
		$User  =  new  UserController();
		// Create Person
		$User->CreateUser();
	});


	Http::post("/edit-user",  function()  {
		$User  =  new  UserController();
		// Create Person
		$User->EditUser();
	});

	  
	Http::post('/update-table',  function()  {
		$User  =  new  Flux();
		$User->MigrateTable('User',  function()  {
			echo  Success('User table updated');
		});
	});

As you can see, we are calling the methods from out Controller class. We simply instantiate a new class object, then call the method. We use 'RenderJSON()' to output the data.

> Lets take a look inside the controller class



	class  UserController  {

		private  $db;

		function  __construct()  {
			$this->db  =  new  Flux();
		}

		public  function  GetAllUsers()  {
			try  {
				$UserModel = new User();
				$users = $UserModel->All();

				// $users = $this->db->All('User')->Exec();
				
				if($users)  {
					RenderJSON($users);
				}

				else  {
					NotFound('No Data');
					Status404();
				}

			}

			catch(Exception  $e)  {
				Error($e);
				Status500();
			}

		}


		public  function  GetUser($id)  {

			try  {

				if($id  &&  is_numeric($id))  {

					$person  =  $this->db->All('User')->Where('UserId')->Equals("$id")->Exec();

					if($person)  {
						RenderJSON($person);
					}

					else  {
						NotFound('No Data');
						Status404();
					}

				}

				else  {
					NotFound('No Data');
					Status404();
				}

			}

			catch(Exception  $e)  {
				Error($e);
				Status500();
			}

		}
		

		public  function  CreateUser()  {

			try  {
				$user  =  $this->db->GetModelData('User');
				$newUser  =  new  User(null,  $user->UserName,  $user->Email,  Hasher::Hash($user->Password));

				$this->db->InsertObject($newUser,  'User');
				RenderJSON($user);
				Status200();
			}

			catch(Exception  $e)  {
				Error($e);
			}

		}


		public  function  EditUser()  {

			try  {
				$user  =  $this->db->GetModelData('User');
				$editedUser  =  $this->db->Update('User')->Set('Email')->Equals("'$user->Email'")->Where('UserId')->Equals("'$user->UserId'")->Exec();
				RenderJSON($editedUser);
				Status200();
			}

			catch(Exception  $e)  {
				Error($e);
			}

		}

	}


As you can see, all the methods being called in the router file, are all created in the controller.


## Outputting Data

Don't want to output the data as JSON? Flux has you covered. The model is mapped to the table.

	public  function  GetAllUsers()  {
		try  {
			$users  =  $this->db->All('User')->Exec();
			
			if($users)  {
				foreach($users as $user)  {
					echo $user->UserName;
					echo $user->Email;
				}
			}

			else  {
				NotFound('No Data');
				Status404();
			}

		}
	}

## Helper methods

Flux has a number of helpful functions we can utilize. Here is the list:

	BaseURL();

> Return the base URL  - 0 arguments

	inc_header()
	
> Includes the header.php located in the 'base' folder  - 0 arguments

	inc_footer();

> Includes the footer.php located in the 'base' folder  - 0 arguments

	LoadPartial(); 
> Loads in any partial located in the 'partials' folder. - 0 arguments

	RenderJSON($data);
	
> Will render JSON out to the page, takes in the data as an argument - 1 argument

	Status200();
	
> Return status 200 - 0 arguments

	Status204();
	
> Return status 204 - 0 arguments

	Status301();
	
> Return status 301 - 0 arguments

	Status400();
	
> Return status 400 - 0 arguments

	Status401();
	
> Return status 401 - 0 arguments

	Status404();
	
> Return status 404 - 0 arguments

	Status405();
	
> Return status 405 - 0 arguments

	Status208();
	
> Return status 408- 0 arguments

	Status429();
	
> Return status 200 - 0 arguments

	Status500();
	
> Return status 500 - 0 arguments

	Status($code, $message);
    
> Return custom status, 1st argument being the code, 2nd being the
> message.  - 2 arguments

	Error($message);
> Return Error response with a custom message - 1 argument

	NotFound($message);
> 	Return Not Found response with a custom message - 1 argument

	Success($message);
> Return Success response with a custom message - 1 argument

	Warning($message);
> Return Warning response with a custom message - 1 argument

	Message($title, $message);
> Return Message response with a custom message and title- 2 argument

	dd()
	
> Die dump function

	GetSession($variable);
> Get the session, pass in session name - 1 argument

	SetSession($variable,  $value);

> Set the session, pass in session name, and the value - 2 arguments

	GetCookie($cookie);
> Get a cookie value -  1 argument

	QueryString($param);
> Returns the value of a query string, E.G. ?id=5 -  1 argument

	Title($title);
> Sets the title of the page - 1 argument

	GetJSON($url);
> Returns the JSON after calling the specified URL - 1 argument

	Redirect($url);
> Redirects the page to a specified URL

	PostValue($val);
> Returns the value of a POST requests value


## Flux Database Methods

Inside the core Flux class, there are some methods to help with interacting with the database.

> Make sure to add Exec() at the end of the statement to execute the
> query.

	Query($query, $params);
> Custom SQL query, with optional parameters for stored procedures.

	Select('SELECT * FROM ...')->Exec();
> Selecting data from the database

	All('User')->Exec();
> Selecting all data from the database

	Select()->From('User')->Exec();
> 	Select from

	InsertInto($table)
> Insert into table

	InsertInto('User')->Values('John Doe')->Exec();
> Insert into table with values

	Delete()
> Delete from table

	All('User')->Where('UserName')->Equals('John')->Exec()
> Get data where

	All('User')->Where('UserName')->Like('John')->Exec()
> 	Like method to get data like argument

	Update()
> Update table

	Set()
> Set data in the table

	Desc()
> Order by descending

	Asc()
> Order by ascending

	And()
> And query

	Or()
> Or query

	All('User')->Where('UserName')->Equals('John')

> Equals query
	
	stored_proc($sp,  $params  =  null)
> Call stored procedure

	$user = new User();
	InsertObject($user, 'User')
> Insert object into table based on class, E.G. creating a new user based on User class

	MigrateTable($className,  $cb  =  false);

	$User  =  new  Flux();
	$User->MigrateTable('User',  function()  {
		echo  Success('User table updated');
	});

> Migrate a table based on the model, E.G. User model

	DeleteTable($tableName);
> Delete table from database, pass in table name as argument


	GetModelData($className);

	
> Lets say a POST request has been made to '/add-user'
> We can grab all the data for that class like so:
> 
	$user  =  $this->db->GetModelData('User');
	$newUser  =  new  User(null,  $user->UserName,  $user->Email,  Hasher::Hash($user->Password));
> 
> And then add the user like above example

	$this->db->InsertObject($newUser,  'User');


## Validation

There are a few validation methods on the Validator class

	Email($email)
> Returns true/false if valid email

	String($str,  $min  =  1,  $max  =  5000)

> Returns true/false on String conditions
	
	Integer($number)
> Returns true/false if valid integer

	Numeric($number)
> Returns true/false if valid number

	URL($url)
> Returns true/false if valid URL

	Date($date,  $format  =  'd-m-Y')
> Returns true/false if valid Date

	StringCompare($str1,  $str2)
	Returns true/false is 2 strings are equal

## Hashing & Salt

Flux has a built in hash class which hashes a string with salt. Salt in options should be changed. By default we are using SHA256.

	class  Hasher  {

		private  static  $options  =  [
			"salt"  =>  "abcdef12345",
		];

		public  static  function  Hash($str)  {
			return  hash('sha256',  $str  .  Hasher::$options['salt']);
		}

	}