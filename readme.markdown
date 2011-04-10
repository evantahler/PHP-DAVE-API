Who is _DAVE_?
============

DAVE is a minimalist, multi-node, transactional API framework written in PHP

DAVE is an acronym that stands for Delete, Add, Edit, and View. These 4 methods make up the core functionality of many transactional web applications. The DAVE API aims to simplify and abstract may of the common tasks that these types of APIs require.  DAVE does the work for you, and he's not CRUD.  Dave was built to be both easy to use, but to be as simple as possible.  I was tiered of bloated frameworks that were designed to be monolithic applications which include M's, V's, and C's together in a single running application.

The DAVE API defines a single access point and accepts GET, POST, or COOKIE input. You define "Action's" that handle the input, such as "AddUser" or "GeoLocate". The DAVE API is NOT "RESTful", in that it does not use the normal verbs (Get, Put, etc). This was chosen to make it as simple as possible for devices/users to access the functions, including low-level embedded devices which may have trouble with all the HTTP verbs.  To see how simple it is to handle basic actions, this package comes with a basic user system included. Look in `/Actions/examples` to see the logic behind adding, editing, viewing, and deleting users. This includes logging in.

The DAVE API understands 2 types of security methodology. "Public" actions can be called by anyone, and then can implement optional user-based security (checking userIDs and PasswordHashes?). Optionally, certain Actions can be defined as "Private", and will require a defined developer to authenticate with every request. This requires your developers to provide an MD5 hash of their APIKey and private DeveloperID to authenticate with. You can mix private and public actions.  Of course, you can make your own actions for this as well!

Philosophical Questions
-----------------------

If you have ever asked these questions of other web-frameworks, then DAVE might be the right fit for you:

* Why do we really need a controller?
* Why are linear actions so hidden in object abstraction?
* Why can't I de-couple my M's, V's and C's?
* Why can't my test suite use the real application?
* Is there an option for a non-restFUL API?
* Why isn't there an easier way to develop and test locally with PHP?
* Why are there no modern PHP API Frameworks?!

Features
--------
* Abstraction of basic DAVE (Delete, Add, Edit, View) actions
* Active Database Modeling on-the-fly or fixed per your requirements
* (optional) Objects for Database entities with DAVE abstraction
* Built with a Multi-Node system in mind, but works great on a single machine as well
* Developer-based authentication in tandem with user-level authentication
* Rate Limiting for client connections
* Class-based abstraction of mySQL connections
* Built-in support for multiple types of Caching (Flat File, mySQL, memcache)
* CRON processing of delayed events
* Simple error handling and input sanitization
* XML, JSON, Serialized PHP output types built in
* End-to-end spec testing framework for the API including custom assertions

Stand-Alone Development Webserver written in PHP [ SERVER.php & script_runner.php ]
-----------------------------------------------------------------------------------
To help with development, a single-threaded multi-request webserver is a part of this project.  This will allow you to locally run this framework in "development mode".  This webserver is written entirely in PHP and has support for basic static file-types (css, js, images, html) along with the sand-boxed execution of PHP scripts (including all of those required for this framework.).  The server currently provides the normal $_GET, $_POST, $_COOKIE, $_REQUEST arrays and a basic emulation of $SERVER.  Due to metaprogramming limitations in the default PHP installs on most servers/machines, it is impossible to modify the behavior of header() and setcookie().  To remedy this, please use _header() and _setcookie() in your DAVE projects.  These functions will first attempt to use the default versions of these functions, and if they fail (AKA when using the StandAlone server), will emulate their behavior in other ways.  This server implementation was inspired by nginx and rails unicorns, and makes use of spawning OS-level processes to do the heavy lifting for each request.

You certainly don't need to use the bundled SERVER to run dave.  For production deployment, upload DAVE to `/var/www/html` (or however you normally deploy).  The SERVER was included to allow for local test-driven development common to other frameworks/languages to which the modern web developer may be accustomed too.

Run "php SERVER.php" from within the project directory to get started.  Point your browser at http://localhost:3000 

Requirements
------------
* PHP 5.2+
* mySQL server 5.1+
* Web-server (tested with Apache 2.0+)
* root access to web-server with CRON

Actions you can try [[&Action=..]] which are included in the framework:
-----------------------------------------------------------------------
* DescribeActions: I will list all the actions available to the API
* DescribeTables: I am an introspection method that will show the results of the auto-described available tables and cols.  I will show weather or not a col is required and if it is unique
* CacheTest: Send a Hash [[ &Hash=MyString ]] and I will first store it in the cache, and on subsequent calls retrieve the vale from the cache until expired.  Change the value of the Hash and note the old value will be displayed until it expires
* LogIn: Example user system.  Follow returned error messages.
* UserAdd: Example user system.  Follow returned error messages.
* UserDelete: Example user system.  Follow returned error messages.
* UserEdit: Example user system.  Follow returned error messages.
* UserView: Example user system.  Follow returned error messages.
* ObjectTest: An example of on-the-fly database object manipulation.  Check the code for this file to see how simple it is.
* CookieTest: Will set cookies in your browser.  Use this to test SERVER's implementation of _setcookie()
* SlowAction: A simple action that will sleep for a number of seconds.  Use this to test SERVER's non-blocking implementation by making a slow request, and then other requests to ensure that a slow request will not block other actions from processing.
	
Example Site: http://dave.evantahler.com

QuickStart
----------
You can get started on your local machine in 5 minutes!  This tutorial is for Unix-like machines (OSX OK!).  We'll be using the included stand-alone server for development. You certainly don't need to use the bundled SERVER to run dave.  For production deployment, upload DAVE to `/var/www/html` (or however you normally deploy).

* Instal PHP (OSX users already have it, Linux folks yum/apt install php5)
* Install mySQL 5 [OSX: http://www.mysql.com/] [Linux: yum/apt install mysql-server and mysql-client)
* Get clone this project
  * `git clone git://github.com/evantahler/PHP-DAVE-API.git /path/where/you/want/it` OR just hit the `download` button above
* Rename `/API/CONFIG.example.php` to `/API/CONFIG.php`
* Configure CONFIG.php.  Assuming we will be running mySQL locally as root with no password, all you should need to change is:
  * `$CONFIG['systemTimeZone']`  (check http://www.php.net/manual/en/timezones.php for how to define your timezone)
  * `$CONFIG['App_dir']`  (this is where the /API folder is)
* setup mySQL for DAVE
  * create a mysql database called "daveapi"
    * (from the command line)
      * `mysql -u root`
      * `create database daveapi;`
      * `exit`
  * load in the default tables
      * `mysql -u root daveapi < BaseDBs/API_WITH_USER_TABLE.sql`
* Install the CRON.php file to run on a set frequency.  Note that paths have to be absolute.
  ** `crontab -e`
  ** `*/1 * * * * /usr/bin/php /path/to/CRON.php > /path/to/CRON_LOG.txt`
* start up the webserver 
  * `php /SERVER/SERVER.php`
* Visit Dave
  * Browser: `http://localhost:3000&OutputType=XML`
  * CURL + time: `time curl -v http://127.0.0.1:3000/ -d "OutputType=XML"`

That's it!  You should see JSON and XML output that describes all of the actions that the server could preform, and an Error asking you to supply an action.  Give `http://localhost:3000/?OutputType=XML&Action=ObjectTest` or `http://localhost:3000/?OutputType=PHP&Action=SlowAction` a try to see some basic examples.

TODO for V1
-----------
* Create a way to test an action with ROLLBACK off of the 'production' API
  * Create a top-level param RollBack=RollBackPassword which will place the entire action within a mySQL transaction
  * Allow the test suite to access this resource
