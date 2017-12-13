<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsType;
use Ami\Db;

/**
 * @coversDefaultClass \Ami\Db
 */
class DbTest extends TestCase
{
	public function testObjectInstantiationSuccess()
	{
		$this->assertInstanceOf(Db::class, new Db('testdb', array('user' => 'homestead', 'pass' => 'secret')));
	}

	public function testObjectInstantiationFailure()
	{
		$this->expectException(PDOException::class);
		
		new Db('testdd', array('user' => 'homestead', 'pass' => 'secret'));
	}

	public function testPreparedInsertSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->add('INSERT INTO countries (`name`) VALUES (?)', array('Australia')));

	}

	public function testNotPreparedInsertSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->add('INSERT INTO countries (`name`) VALUES ("Brazil")'));
	}

	public function testExceptionOnNonPreparedQueryForPreparedConnection()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->expectException(Exception::class);

		$db->add('INSERT INTO countries (`name`) VALUES ("India")');		// this will raise an exception		
	}

	public function testExceptionOnPreparedQueryForNonPreparedConnection()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->expectException(PDOException::class);

		$db->add('INSERT INTO countries (`name`) VALUES(?)', array('New Zealand'));
	}

	public function testPreparedSelectSuccessOnObjectFetch()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertInternalType(IsType::TYPE_OBJECT, $db->get('SELECT * FROM countries', array()));		
	}

	public function testPreparedSelectSuccessOnArrayFetch()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'fetch_mode' => 'FETCH_ASSOC'));

		$this->assertInternalType(IsType::TYPE_ARRAY, $db->get('SELECT * FROM countries', array()));		
	}

	public function testPreparedUpdateSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->update('UPDATE countries SET `name` = ? WHERE `id` = ?', array('Ireland',1)));		
	}

	public function testNonPreparedUpdateSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->update('UPDATE countries SET `name` = "West Indies" WHERE `id` = 1'));	
	}

	public function testPreparedUpdateNotAffected()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertEquals(0, $db->delete('UPDATE countries SET `name` = ? WHERE `id` = ?', array('Mars',999)));
	}	

	public function testNonPreparedUpdateNotAffected()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->assertEquals(0, $db->delete('UPDATE countries SET `name` = "Mars" WHERE `id` = 999'));
	}	

	public function testPreparedDeleteSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->delete('DELETE FROM countries WHERE `id` = ?', array(1)));		
	}

	public function testNonPreparedDeleteSuccess()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->assertInternalType(IsType::TYPE_NUMERIC, $db->delete('DELETE FROM countries WHERE `id` = 1'));	
	}	

	public function testPreparedDeleteNotAffected()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret'));

		$this->assertEquals(0, $db->delete('DELETE FROM countries WHERE `id` = ?', array(66)));
	}	

	public function testNonPreparedDeleteNotAffected()
	{
		$db = new Db('testdb', array('user' => 'homestead', 'pass' => 'secret', 'prepare' => false));

		$this->assertEquals(0, $db->delete('DELETE FROM countries WHERE `id` = 66'));
	}	
}