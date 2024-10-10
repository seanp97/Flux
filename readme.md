
#  Welcome to Flux 🚀

##  Flux Project Documentation (with Source Code)

#  What is Flux?


## Flux is a PHP-based web application that follows a modular MVC (Model-View-Controller) architecture. It includes various components such as controllers, models, views, routes, and core utilities to build web applications and API's.

###  Table of Contents

1.  [Project Structure](#project-structure)

2.  [Controllers](#controllers)

3.  [Models](#models)

4.  [Views](#views)

5.  [Routes](#routes)

6.  [Core Utilities](#core-utilities)

7.  [Configuration](#configuration)

8.  [CLI Tools](#cli-tools)

9.  [Database Methods](#database-methods)

10.  [Helper Functions](#helper-functions) 

  

##  Project Structure

  

The project follows this directory structure:

  
  

###  Explanation:

-  **base/**: Contains reusable base templates like `header.php` and `footer.php`.

-  **cli/**: Command-line interface tools for tasks like migrations and model creation.

-  **config/**: Holds configuration settings for the application.

-  **controllers/**: Contains controller logic for handling user interactions and routing to views.

-  **core/**: Core utility functions and helper classes for the framework.

-  **css/**: Stylesheets for the project.

-  **js/**: JavaScript files for interactivity.

-  **models/**: Contains the models representing business logic and data.

-  **partials/**: Reusable partial views (e.g., `hello.php`).

-  **routes/**: Defines application routes.

-  **views/**: Holds view templates corresponding to different pages.

---


##  Controllers

Controllers are responsible for handling requests and returning responses. Below are the controllers in the project along with their source code:


###  HomeController.php

Here in the HomeController we are creating an index method which simply ouputs 'Hello World' which we can then call in the routes.php file.

	class HomeController {
		public function index() {
			echo 'Hello World';
		}
	}


##  Models

In the models folder we can define model entities which map to the database. When we run the migration command, the table will be created within our database.

	class User {
		public $id;
		public $name;
		public $email;
		
		public function __construct($id, $name, $email) {
			$this->id = $id;
			$this->name = $name;
			$this->email = $email;
		}
	}

  
##  Views

### In the views folder, you can create folders with your php files to display out on the front end

### Home/index.php

	<h1>Home Page</h1>
	<p>Welcome to the Home page.</p>

  
### About/index.php

As you can see here in About/index.php we are setting the including the header & footer file, setting the title, loading a partial view, as well as outputting data we got from the AboutController.php file - *see below*

	<?php
		// Include header.php in base folder
		inc_header();
		
		// Set title
		Title('About Page');
	?>
	
	<h5>About</h5>
	<?php  LoadPartial('hello'); ?>

	<?php
		
		// Check if there are users
		if($all_users) {]
			// Check if there is more one user
			if(is_array($all_users)) {
				// Loop through users and output property value
				foreach($all_users  as $user) {
					echo $user->Email  .  '</br>';
				}
			}
			
			// If one user, output property value
			else {
				echo $all_users->Email;
			}

		}
		
		// Output no results
		else {
			echo 'No users';
		}

	?>
	
	// Include footer.php in base folder
	<?php  inc_footer(); ?>

### AboutController

	class  AboutController {
		public  static  function  index($all_users) {
			// Load index.php file in views/About folder
			// Pass data to About/index.php file
			view("About/index", compact('all_users'));
		}
	}


##  Routes

### In the routes folder you will find routes.php and here you can map out the endpoints

	Http.get('/', function() {
		HomeController::index();
	});
	
	Http.post('/', function() {
		echo 'POST request recieved';
	});
	
	Http.put('/', function() {
		echo 'PUT request recieved';
	});
	
	Http.delete('/', function() {
		echo 'DELETE request recieved';
	});

	Http.patch('/', function() {
		echo 'PATCH request recieved';
	});

##  Configuration

Flux uses MYSQL as the database driver.

	class Configuration {

		//MYSQL connection properties
		public static $connection = [
			"servername" => "localhost",
			"username" => "root",
			"password" => "",
			"database" => ""
		];

		public static function AccessControlAllowOrigin($cors = "null") {
			header("Access-Control-Allow-Origin: $cors");
		}
	}
  

##  CLI Tools

###  Model

	php ./cli/flux-model <ModelName>

###  Controller

	php ./cli/flux-controller <ControllerName>

###  Migrate

	php ./cli/flux-migrate <ModelName>


## Database Methods

Here we have a FactController for creating an API.  Lets take a look at the code..

	<?php

	class  FactController {

		private $db;

		function __construct() {
			$this->db  =  new  Flux();
		}
	 
		// Get all facts
		public  static  function  index() {
			try {
				// Get all Facts
				$facts  =  Fact::All()::Exec();
				
				//Check if there are facts
				if(!$facts) {
					Return not found with custom message
					NotFound('No facts found');
				}
				// Output all facts as JSON
				Ok($facts);

			}
			
			catch(Exception $e) {
				throw new  Error($e);
			}
		}

		// Get single fact
		public  static  function  show($id) {
			try {
				// Find one fact
				$fact  =  Fact::FindOne($id)::Exec();
				
				// Check there is a fact with given id
				if(!$fact) {
					//Return not found with custom message
					NotFound('No fact found');
				}
				// Output fact as JSON
				Ok($fact);
			}

			catch(Exception $e) {
				throw new  Error($e);
			}
		}

	  
		// Create Fact
		public  static  function  create() {
			try {
				// Creating a $fact variable which grabs all the properties of given model
				$fact  =  Fact::ModelData();
	
				// Insert $fact object into 'Fact' table
				Fact::InsertObject($fact, 'Fact');
				
				// Output added fact as JSON
				Ok($fact);
			}
			
			catch(Exception $e) {
				throw new  Error($e);
			}

		}

		// Delete Fact with single id
		public  static  function  destroy($id) {
			try {
				// Delete fact with given id
				Fact::Delete($id)::Exec();
		
				// Return custom Ok message with string
				Ok('Fact deleted');
			}

			catch(Exception $e) {
				throw new  Error($e);
			}
		}
		

Now to take a look at the routes.php file to understand how we are calling these methods...

### routes.php

	Http::get('/api', function() {
		// Calling index method from FactController
		FactController::index();
	});

	// grab id from {id} and pass into function. Make sure is also passed in controller
	Http::get('/api/{id}', function($id) {
		FactController::show($id);
	});

	Http::post('/api', function() {
		FactController::create();
	});

	Http::post('/api/{id}', function($id) {
		FactController::destroy($id);
	});

## Helper functions

In Flux, we get a range of helper functions to make development easier. These methods can be called in our code.

**BaseURL()**

-   Returns the base URL of the current request.

**inc_header()**

-   Includes the header file (`./base/header.php`).

**inc_footer()**

-   Includes the footer file (`./base/footer.php`).

**LoadPartial()**

-   Includes a partial template based on the provided name.

**RenderJSON()**

-   Renders the given data as JSON.

**Ok()**

-   Sends a 200 OK response with the provided value.

**BadRequest()**

-   Sends a 400 Bad Request response with the provided value.

**NotFound()**

-   Sends a 404 Not Found response with the provided value.

**Created()**

-   Sends a 201 Created response with the provided value.

**NoContent()**

-   Sends a 204 No Content response with the provided value.

**Unauthorized()**

-   Sends a 401 Unauthorized response with the provided value.

**Forbid()**

-   Sends a 403 Forbidden response with the provided value.

**Status200()**

-   Sets the HTTP status code to 200 OK.

**Status204()**

-   Sets the HTTP status code to 204 No Content.

**Status301()**

-   Sets the HTTP status code to 301 Permanent Redirect.

**Status302()**

-   Sets the HTTP status code to 302 Temporary Redirect.

**Status400()**

-   Sets the HTTP status code to 400 Bad Request.

**Status401()**

-   Sets the HTTP status code to 401 Unauthorized Error.

**Status403()**

-   Sets the HTTP status code to 403 Forbidden.

**Status404()**

-   Sets the HTTP status code to 404 Not Found.

**Status405()**

-   Sets the HTTP status code to 405 Method Not Allowed.

**Status408()**

-   Sets the HTTP status code to 408 Request Timeout.

**Status429()**

-   Sets the HTTP status code to 429 Too Many Requests.

**Status500()**

-   Sets the HTTP status code to 500 Internal Server Error.

**Status()**

-   Sets the HTTP status code and message.

**Error()**

-   Renders an error message as JSON.

**Success()**

-   Renders a success message as JSON.

**Warning()**

-   Renders a warning message as JSON.

**Message()**

-   Renders a custom message as JSON.

**dd()**

-   Dumps variables and dies.

**GetSession()**

-   Gets a session variable.

**SetSession()**

-   Sets a session variable.

**GetCookie()**

-   Gets a cookie value.

**QueryString()**

-   Gets a query string parameter.

**Title()**

-   Sets the document title.

**GetJSON()**

-   Gets JSON data from a URL.

**Redirect()**

-   Redirects to a specified URL.

**PostValue()**

-   Gets a POST value.

**GUID()**

-   Generates a GUID.
