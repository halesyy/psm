# PSM
## Intro
PSM is a PDO (PHP Database Objects) re-write, formatted to be easy.

## Install
PSM is loaded with a default 3 files, your **PSMMain.php**, **PSMQuery.php** and **PSMExtra.php**, each of these classes are necesary to the main, *PSM* class.

**You can default install the class with some code like:**
```php
require_once('classes/PSMExtra.php');
require_once('classes/PSMQuery.php');
require_once('classes/PSMMain.php');

$psm = new PSM('localhost test root EM');
```

And there you have it! A simple **PSM** connection!
You will most likely put this in a header file in your PHP code.


## Documentation
When making a new PSM object, you are given a lot of functions :smirk:

> **If you don't really know any PHP OOP, I recommend you understand how classes work and functions work together, you can read up on it [here - php manual](http://php.net/manual/en/language.oop5.php) or [here - youtube tutorial](https://www.youtube.com/watch?v=ipp4WPDwwvk&list=PLfdtiltiRHWF0RicJb20da8nECQ1jFvla)**

To make a simple PSM object just do the following code, and we can inspect it after:
**(Make sure to require the PSM files as done in the install section)**

```php
$psm = new PSM('localhost test root EM');
```

Each paramater is seperated by a space, and the input string really looks like 

**HOST - DATABASE - USERNAME - PASSWORD**

And I used **EM** in the password paramter as that is how we represent a space for a password in PSM, so it's like saying,
Connect too *localhost*, use the database *test* and give the username *root* and password ***space***


### Drivers / Options
PSM does allow for some simple but useful drivers (*options*)
If you have tried to connect to a database and failed connecting with the message:

![PSM Base Error Message](http://image.prntscr.com/image/47376974050b4d4c9f1cb243490b3959.png)

You can add the driver **safeconnection => false** and you'll get a new set of information, shown here:

![PSM Unsafe Error Message](http://image.prntscr.com/image/140bd1763bb54f138b52f4f22e7c617b.png)


### How to add a driver
```php
$psm = new PSM('localhost test root EM',[
  'safeconnection' => false
]);
```
To add a driver, simple add an *associative* array in the second paramater with the drivers you want.
As PSM lives for longer, new drivers will be released when asked, if you want a special driver added, please open an issue.


### Some basic functions
> If you're really interested, take a look in the file **classes/PSMQuery.php**, as that contains all the classes, make a new function edit and play pull request it! I'd love to add it to the repo.

**Some info**
Each function will usually have a "safe function" pair, if a function uses ONLY a query, it will most likely have a version of the query that uses binds.

I'm working on making functions where you don't even have to write the SQL query anymore, but with time that will come :innocent:

Function | Information
-------- | -----------
**Functions** | **That will be removed at some point-in-time, but for now are stable and work**
c(), connection() | Returns the PDO handler.
sQuery(**statement**, **binding**, **loop function**) | Will take the **statement** and prepare it, then execute it with the given **binding** then loop the function **callback** - the callback function is given 3 variables, **$row, $psm and a $\_POST object** for the loop.
query() | *Deprecated*
statQuery(**statement**) | Performs the query statically, meaning a simple $database->query(**statement**)
sstatQuery(**statement**, **binds**) | Prepares the query given, will the execute with **binds**.
retQuery(**statement**) | Returns a pure PDO query.
sretQuery(**statement**, **binds**) | Returns a binded PDO query.
gqs(**statement**) | **gqs = GET QUERY SET**, returns the first row of the query, example **$psm->gqs("SELECT * FROM users");** - will return the first row in that query, so **id = 1**
gsqs(**statement**, **binds**) | Does the same as **gqs** but will instead bind the query before getting the first row.
grc(**statement**) | Gets the row count of the query - The amount of rows.
gsrc(**statement**, **binds**) | Does the same as **grc** but will instead bind the query before getting the row count.
glid() | Get's the equivalent of *$pdo->lastInsertId()* - Will get the last ID that was inserted.
**Functions** | **Functions that are currently not fully-wanted in the main code-base of functions we recommend, but are awesome soon-to-be features**
upadate(**table**, **updates**, **where = false**, **binds = false**, **debug = false**) | The **update** function is meant to generate the **UPDATE TABLE SET x = y** queries for you and execute it using binding if you want. **table** is the table in your MySQL database that you want to have the changes occur in, **where** is a **WHERE x = y** statement, if you're planning on using a binding array, go ahead and make it something like **x = :y** in the **where** attribute, if you aren't, just append in the ', like **x = \\'y\\'**, **binds** is your binding array, **debug** will echo out the statement that was created if wanted.
getall(**table**, **column**) | This will go into the **table** given and return an array of each entry with the **column**, so, if you say **table** = users, and **column** = username, this will return an array of all the usernames in the table, good for making *that email is already in use* if that's what your app needs :smile:
select, col, where, order, print_query, build | This isn't fully recommended as of now as it needs some polishing up, but this is a set of functions that let you create a query, that can example go in a foreach loop and work correctly, *example* - $psm->select('users')->col('\')->where('id = :id',true)->build('bind', [':id' => 1]) - And if you wrapped that in a foreach loop, that would work.
plus(**table**, **col**, **id**, **by**) | This is a function that will go into **table**, and increase the **column** at id **id** by **by**, simple increasing statement.
post(**value**, **do**) | This is a neat function! And I will always have a spot in my :heart: for it, but this function will take a look at the **value**, if it's a single word, it'll check if that POST request exists, if the **value** is something like *sub = login*, itll check if *$_POST['sub'] == 'login'* - and if so, the function will do the given **do** function supplied (**do** is given 3 paramaters - **psm**, **post object** and **post array**.
insert($array) | This is one of the most recently updated functions, meaning its *awesome*, just supply an array, this will be your example array: `['table' => 'users', 'username' => 'jack']` - This function will automatically bind your query so its safe and insert it for you, easy!
pHelp(**query**) | This function acts as a *debugger* - And will display *not return* an array that contains a bunch of debugging information.
