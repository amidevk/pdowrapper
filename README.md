## Installing the package:
You can install the package by issuing below command from your app command line or shell prompt:

```
git clone https://github.com/amidevk/pdowrapper.git
```

Once installed you can import the library to your script by adding below statements:

```

require 'pdowrapper/src/Db.php';

use Ami\Db;
```

## Establishing Database Connection
There are few different ways you can establish a database connection:

**Syntax :**
`$db = new Db('database_name'[, config_parameters ]);`

Below lists the **config parameters** you can pass to the constructor:

- **port** : pass this option to override the default port which is set to 3306 
- **host** : pass this option to override the default hostname which is set to 'localhost'
- **charset** : pass this option to override the default charset which is set to 'utf8'
- **fetch_mode** : sets the fetch mode. there are two values that you can pass 
      - **FETCH_ASSOC** : in this mode the results will return as an associative array
      - **FETCH_OBJ** : in this mode the results will return as an object. this is the default fetch mode if you didn't explicitly set the fetch mode
- **user** : sets the database username. if not set this will default to 'root'
- **pass** : sets the database password. if not set this will default to an empty string (no password)
- **ssl** : enables or disables ssl connection. default to false. if this is set to true you'll have to set the 'ssl_ca' also
- **ssl_ca** : sets the full file system path to your ssl certificate (eg: /path/to/certificate.pem)
- **prepare** : enables or disables prepared statements. enabled by default for improved security and it is recommended to leave this setting as it is.
- **persist** : enables or disables the persistent connection. not enabled by default.

Below lists few scenarios you can use depending on your requirement:

- Connecting to mysql server at localhost with default 'root' user and no password
```
	try{
		$db = new Db('your_db_name');
	}catch(Exception $e){
		die($e->getMessage());
	}
```
- Connecting to mysql server at localhost with different username/password combination
```
	try{
		$db = new Db('your_db_name', array(
		               'user' => 'your_user_name', 
		               'pass' => 'your_password') 
		       );
	}catch(Exception $e){
		die($e->getMessage());
	}
```
- Connecting to mysql server at localhost with user/password with prepared statements disabled
```
	try{
		$db = new Db('your_db_name', array(
		               'user' => 'your_user_name',  
		               'pass' => 'your_password',  
		               'prepare' => false));
	}catch(Exception $e){
		die($e->getMessage());
	}
```
- Connecting to mysql server at localhost with default user/password, fetch method set to array
```
	try{
		$db = new Db('your_db_name', array(
		               'fetch_mode' => 'FETCH_ASSOC') 
		);
	}catch(Exception $e){
		die($e->getMessage());
	}
```

Once you've establish the database connection, you can execute queries. To facilitate queries, this class gives you four methods:

- `$db->get()` for SELECT queries
- `$db->add()` for INSERT queries
- `$db->update()` for UPDATE queries
- `$db->delete()` for DELETE queries

Performing SELECT Queries
---------------------------------------
**Syntax :**
`$db->get('query'[, array][, boolean]);`

**In prepared statement mode**

- Query with no parameters (fetches a single row)

  ```$result = $db->get('SELECT * FROM tablename', array());```
- same query but this time fetches all rows:

  ```$result = $db->get('SELECT * FROM tablename', array(), true);```
- query with parameters (fetches a single row)

  ```$result = $db->get('SELECT * FROM tablename WHERE id = ?', array(10));```
- same query fetching all rows:

  ```$result = $db->get('SELECT * FROM tablename WHERE id = ?', array(10), true);```


**In non-prepared statement mode** 

- Fetching a single row in a non-prepared query. we have skipped out second and third parameters

  ```$result = $db->get('SELECT * FROM tablename');```
- Fetching all rows. now we have passed two additional parameters. second parameter is given as null, third parameter is set to true

  ```$result = $db->get('SELECT * FROM tablename WHERE id = 1', null, true);```
  

Using the Result
---------------------------------------
- When you have a single row, you can use it as shown below:
   - If fetched as an object
 
     ```$result->field_name```
   - If fetched as an array
   
     ```$result['field_name']```
- When you have an array of rows, you can use foreach() to retrieve each individual rows:
```
    foreach($result as $row) 
	{	
		$row->field_name  OR $row['field_name']
	}
```


Performing INSERT Queries
---------------------------------------
**Syntax :**
`$db->add('query'[, array_of_values ]);`

- Inserting a record in **prepared statement** mode
```
		try{
			$res = $db->add('INSERT INTO tablename (
			                `field_1`,  
			                `field_2`,  
			                `field_n` 
			) VALUES (?,?,?)', array(
			     'value1',  
			     'value2',  
			     'valueN' 
			));
		}catch(Exception $e){
			die($e->getMessage());
		}
```
- Inserting a record in **non-prepared** statement mode
```
		try{
			$res = $db->add('INSERT INTO tablename( 
			                `field_1`,  
			                `field_2`,  
			                `field_n`) VALUES ( 
			       'value1', 
			       'value2', 
			       'value3')' 
			);
		}catch(Exception $e){
			die($e->getMessage());
		}
```
If the insert operation was successful, ```$db->add()``` will return the **id** of the record that was added. If it has failed, the return value will be **false**.

Performing UPDATE Queries
---------------------------------------
**Syntax :**
`$db->update('query'[, array_of_values ]);`

- Updating a record in **prepared** statement mode
```
		try{
			$res = $db->update( 
			     'UPDATE tablename SET  `field_x` = ?  
			      WHERE `id` = ?', array( 
			             'value_for_field_x',  
			             'value_for_id' 
			      ));
		}catch(Exception $e){
			die($e->getMessage());
		}
```
- Updating a record in **non-prepared** statement mode
```
		try{
			$res = $db->update( 
		       'UPDATE tablename SET `field_x` = "valueX"  
		          WHERE `id` = 1');
		}catch(Exception $e){
			die($e->getMessage());
		}
```

If the update operation was successful, `$db->update()` will return the **number of affected rows** by the operation. If nothing was affected the return value will be **0**. If the query has failed, the return value will be false. 

Performing DELETE Queries
---------------------------------------
**Syntax :**
`$db->delete('query'[, array_of_values ]);`

- Delete a record in prepared statement mode
```
		try{
			$res = $db->delete('DELETE FROM tablename  
			  WHERE `id` = ?', array('value_for_id'));		
		}catch(Exception $e){
			die($e->getMessage());
		}
```
- Delete a record in **non-prepared** statement mode
```
		try{
			$res = $db->add('DELETE FROM tablename 
			  WHERE `id` = 1');
		}catch(Exception $e){
			die($e->getMessage());
		}
```
If the delete operation was successful, `$db->delete()` will return the **number of affected rows** by the operation. If nothing was affected the return value will be **0**. If the query has failed, the return value will be **false**.

Testing with PHPUnit
-------------------------------
tests/DbTest.php has number of test cases that can verify the intended functionality of the class. To execute the tests please follow below guidelines:

- Run `composer install` on your app directory to install **phpunit**
- Open phpmyadmin and create a new database with any name you like. create a new table in it called `countries`. Make sure it has following two fields:
   - `id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY`
   - `name VARCHAR(100)`
- Open tests/DBTest.php in your code editor and: 
   - Replace all entries of 'testdb' with your database name
   - Replace all entries of 'homestead' with your database username (i.e. root)
   - Replace all entries of 'secret' with your database password (i.e. '')
- Now in the command line you can run the test by issuing below command:

   `phpunit tests/DbTest`