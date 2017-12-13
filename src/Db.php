<?php
namespace Ami;

class Db {

	const DB_DRIVER = 'mysql';

	public $dbh;
	public $ssl = false;

	private $database = '';
	private $port = 3306;	
	private $host = 'localhost';
	private $charset = 'utf8';
	private $fetch_mode = \PDO::FETCH_OBJ;
	private $user = 'root';
	private $pass = '';
	private $dsn = '';
	private $ssl_ca = '';
	private $prepare = true;
	private $persist = false;

	/**
	 * Constructor
	 */

	public function __construct($db, $config = [])
	{

		// throw an exception if database name is missing (no need)
		// if(!$db) throw new Exception('No database set!');

		$this->database = $db;

		if(!empty($config))
		{
			// sets a list of config settings to look for
			$config_keys = ['port','host','charset','fetch_mode','user','pass','ssl','ssl_ca','prepare','persist'];
			
			// override default values if necessary
			foreach($config_keys as $key)
			{
				if(isset($config[$key]))
				{
					if($key == 'fetch_mode')
					{
						switch($config[$key])
						{
							case 'FETCH_ASSOC':
								$this->fetch_mode = \PDO::FETCH_ASSOC;
							break;

							case 'FETCH_OBJ':
								$this->fetch_mode = \PDO::FETCH_OBJ;
							break;
						}
					}
					else
					{
						$this->$key = $config[$key];
					}
					
				}
			}			
		}


		// set dsn
		$this->dsn = self::DB_DRIVER.':dbname='.$this->database.';host='.$this->host.';port='.$this->port.';charset='.$this->charset;

		$params = [];

		// persistent?
		if($this->persist)
		{
			$params[\PDO::ATTR_PERSISTENT] = TRUE;
		}

		// disable prepared queries
		if(!$this->prepare)
		{
			$params[\PDO::MYSQL_ATTR_DIRECT_QUERY] = TRUE;
		}

		// ssl
		if($this->ssl)
		{
			if(!$this->ssl_ca) throw new Exception('SSL Certificate Not Set!');

			if(!file_exists($this->ssl_ca)) throw new \Exception('SSL Certificate Cannot be Found!');

			$params[\PDO::MYSQL_ATTR_SSL_CA] = $this->ssl_ca;
		}

		// establish the connection (this will throw a PDOException if fails)
		$this->dbh = new \PDO($this->dsn, $this->user, $this->pass, $params);


		// set attributes
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, $this->fetch_mode);

	}


	private function _prepare($query, $data)
	{
		$statement = $this->dbh->prepare($query);

		/** bind parameters */
		$paramcounter = 1;
		
		if(count($data) > 0)
		{
			foreach($data as $val)
			{
				$statement->bindValue($paramcounter, $val);
				$paramcounter++;
			}			
		}

                
		return $statement;
	}

	private function _result($action, $args, $statement)
	{
		switch($action)
		{
			case 'get':
				return (isset($args[2]) && $args[2] == true) ? $statement->fetchAll() : $statement->fetch();
			break;

			case 'add':
				// return lastInsertId
				return $this->dbh->lastInsertId();
			break;

			case 'update':
			case 'delete':
				// return rowCount affected by the operation
				return $statement->rowCount();
			break;

		}		
	}		


	public function __call($name, $args)
	{
		if(!in_array($name, ['get','add','update','delete'])) throw new Exception('Invalid Method Call!');


		if($this->prepare)
		{
			if(!isset($args[0]) || !isset($args[1]))
			{
				// query and/or data missing
				throw new \Exception('Invalid Arguments supplied for the prepared statement!');
			}

			$statement = $this->_prepare($args[0], $args[1]);

			
			if($statement->execute())
			{
				return $this->_result($name, $args, $statement);
			}
			else
			{
				return false;
			}
		}
		else
		{
			// escape
			//$safequery = $this->dbh->quote($args[0]);		-> this causing an error
			$safequery = $args[0];

			// execute
			$res = $this->dbh->query($safequery);		// this returns a PDOStatement object if succeeds

			if($res)
			{
				return $this->_result($name, $args, $res);
			}
			else
			{
				return false;
			}			
		}
	}


}