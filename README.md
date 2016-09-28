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

PSM Function | Documentation
------------ | -------------
conn(), connection() | Returns the PDO handler.
query(**statement**, **bind**, **loop**) | All of these functions you won't need to use the bind variable, but we recommend that you do. if you do - will prepare and execute the statement given, will then loop the callback function you give it.
static_query(**statement**, **bind**) | Prepares and executes the query, mainly used for *INSERT* queries, will also return the PDO Query Object.
query_set(**statement**, **bind**) | Returns the first row gathered from the query.
row_count(**statement**, **bind**), rc() | Returns the row count of the query.
glid(), last_id() | Gets the last ID inserted from the PDO object.
update(**table**,**updates**,**where**,**binds**) | Takes tables as column => data, goes into table and finds the where statement, and executes using binds.
getall(**table**, **columns**) | Gets each entry of the column in the table, example users and username, will get each username from the table users.
plus(**table**, **col**, **id**, **by**) |  Go into the **table**, find the column **col** with id **id** and increase by **by**.
post(**value**, **do**) | Checks the **value**, if it's something like *sub = login*, will check if the post value *sub* = *login*, if so do the callback **do** or if the value is something like *sub*, will just check if it's set and perform the **do** callback function if so.
insert(**table**, **inserts**) | Will insert into **table**, using the array **inserts** in a structure like `column => data`, will auto-bind for you.
if\_entry\_exists(**array**) | Array will contain a sub-array called *info* - This contains `table = table`, `where = binding_where_statement`, `binds = array containing your binds`. Then the array will contain two other pieces, called `true` and `false`, containing the output of `if_entry_exists`, itll look like: ![Array Example](http://image.prntscr.com/image/806149cd7b1b42c2919a34e592937375.png)
table_exists(**table**) | Returns true or false if the **table** exists in your selected database
get_cols(**table**) | Returns an array containing the columns of a table

## class_query(*classname*)

This is a bit of a confusing function, so it's best to just give an example then explain after:

```php
# The class we want to full (fill the public variables)
class User {
  public $statementinfo = [
    'statement' => "SELECT * FROM users WHERE id = :id",
    'binds'     => [':id' => 1]
  ];
  
  public $id, $username, $password, $lastlogin;
}

$set = $psm->class_query('User');
echo $set->username;
```

This function will go into the class **classname**, and retrieve the public variables **statementinfo->statement** and **statementinfo->binds**, and as you can guess, perform the query given.
It'tt then get the query data back and insert them into the public variables according to the columns.
This is the same as using the **query** function and asking for it to return an object, but instead, you can make functions inside of the class, for instance:

```php
# Function inside of User class.
public function get_profile_link() {
  return "<a href='/profile/{$this->i}'>{$this->username}</a>";
}
```

Then call it with **$set->get_profile_link()** and bam, you get the profile link returned!
