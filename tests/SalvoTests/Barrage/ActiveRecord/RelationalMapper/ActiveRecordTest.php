<?php
namespace SalvoTests\Barrage\ActiveRecord\RelationalMapper;

use SalvoTests\Barrage\BaseTestCase;

class ActiveRecordTest extends BaseTestCase
{
	/**
	 * This function is used to setup and tear down the database for the tests in this class
	 * Override in the child child class to load data into the database
	 *
	 * @return string The path to the data file to load for these tests
	 */
	public function getDataFileLocations()
	{
		return array(__DIR__ . '/yml/ActiveRecordTest.yml');
	}

	public function getSchema()
	{
		return array
		(
			'ut_barrage' => array
			(
				'sTaT_uteS',
				'types',
				'users'
			)
		);
	}

	/**
	 * @test
	 */
	public function createEmptyObject()
	{
		$type = new Type();

		$this->assertEquals(null, $type->id);
		$this->assertEquals(null, $type->title);
		$this->assertEquals(null, $type->global);

		$status = new Status();

		$this->assertEquals(null, $status->getId());
		$this->assertEquals(null, $status->getTitle());
		$this->assertEquals(null, $status->getGlobal());

		$user = new User();

		$this->assertEquals(null, $user->id);
		$this->assertEquals(null, $user->firstName);
		$this->assertEquals(null, $user->lastName);
		$this->assertEquals(null, $user->username);
		$this->assertEquals(null, $user->password);
		$this->assertEquals(null, $user->email);
		$this->assertEquals(null, $user->typeId);
		$this->assertEquals(null, $user->statusId);
	}

	/**
	 * @test
	 */
	public function resetNewObject()
	{
		$type = new Type();

		$type->id = 123;
		$type->title = 'test';
		$type->global = 0;

		$this->assertEquals(123, $type->id);
		$this->assertEquals('test', $type->title);
		$this->assertEquals(0, $type->global);

		$type->reset();

		$this->assertEquals(null, $type->id);
		$this->assertEquals(null, $type->title);
		$this->assertEquals(null, $type->global);

		$status = new Status();

		$status->setId(234);
		$status->setTitle('test2');
		$status->setGlobal(1);

		$this->assertEquals(234, $status->getId());
		$this->assertEquals('test2', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());

		$status->reset();

		$this->assertEquals(null, $status->getId());
		$this->assertEquals(null, $status->getTitle());
		$this->assertEquals(null, $status->getGlobal());

		$user = new User();

		$user->id = 1;
		$user->firstName = 'Ryan';
		$user->lastName = 'Zec';
		$user->username = 'ryan.zec';
		$user->password = 'password';
		$user->email = 'test@test.com';
		$user->typeId = '1';
		$user->statusId = 1;

		$this->assertEquals(1, $user->id);
		$this->assertEquals('Ryan', $user->firstName);
		$this->assertEquals('Zec', $user->lastName);
		$this->assertEquals('ryan.zec', $user->username);
		$this->assertEquals('password', $user->password);
		$this->assertEquals('test@test.com', $user->email);
		$this->assertEquals('1', $user->typeId);
		$this->assertEquals(1, $user->statusId);

		$user->reset();

		$this->assertEquals(null, $user->id);
		$this->assertEquals(null, $user->firstName);
		$this->assertEquals(null, $user->lastName);
		$this->assertEquals(null, $user->username);
		$this->assertEquals(null, $user->password);
		$this->assertEquals(null, $user->email);
		$this->assertEquals(null, $user->typeId);
		$this->assertEquals(null, $user->statusId);
	}

	/**
	 * @test
	 */
	public function createObjectFromPrimaryKey()
	{
		$type = new Type(1);

		$this->assertEquals('1', $type->id);
		$this->assertEquals('none', $type->title);
		$this->assertEquals('1', $type->global);

		$status = new Status(1);

		$this->assertEquals(1, $status->getId());
		$this->assertEquals('active', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());

		$user = new User(1);

		$this->assertEquals(1, $user->id);
		$this->assertEquals('Ryan', $user->firstName);
		$this->assertEquals('Zec', $user->lastName);
		$this->assertEquals('ryan.zec', $user->username);
		$this->assertEquals('password', $user->password);
		$this->assertEquals('test@test.com', $user->email);
		$this->assertEquals(1, $user->typeId);
		$this->assertEquals(1, $user->statusId);
		$this->assertEquals('active', $user->status);
	}

	/**
	 * @test
	 */
	public function resetObjectFromPrimaryKey()
	{
		$type = new Type(1);

		$type->title = 'test';
		$type->global = 0;

		$this->assertEquals('test', $type->title);
		$this->assertEquals(0, $type->global);

		$type->reset();

		$this->assertEquals('1', $type->id);
		$this->assertEquals('none', $type->title);
		$this->assertEquals('1', $type->global);

		$status = new Status(1);

		$status->setTitle('test2');
		$status->setGlobal(1);

		$this->assertEquals('test2', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());

		$status->reset();

		$this->assertEquals(1, $status->getId());
		$this->assertEquals('active', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());

		$user = new User(1);

		$user->firstName = 'yan';
		$user->lastName = 'ec';
		$user->username = 'yan.zec';
		$user->password = 'assword';
		$user->email = 'est@test.com';
		$user->typeId = '0';
		$user->statusId = 0;

		$this->assertEquals('yan', $user->firstName);
		$this->assertEquals('ec', $user->lastName);
		$this->assertEquals('yan.zec', $user->username);
		$this->assertEquals('assword', $user->password);
		$this->assertEquals('est@test.com', $user->email);
		$this->assertEquals('0', $user->typeId);
		$this->assertEquals(0, $user->statusId);

		$user->reset();

		$this->assertEquals(1, $user->id);
		$this->assertEquals('Ryan', $user->firstName);
		$this->assertEquals('Zec', $user->lastName);
		$this->assertEquals('ryan.zec', $user->username);
		$this->assertEquals('password', $user->password);
		$this->assertEquals('test@test.com', $user->email);
		$this->assertEquals(1, $user->typeId);
		$this->assertEquals(1, $user->statusId);
		$this->assertEquals('active', $user->status);
	}

	/**
	 * @test
	 */
	public function insertNewObject()
	{
		$type = new Type();

		$type->id = 123;
		$type->title = 'test';
		$type->global = 0;

		$this->assertEquals('new', $type->getDataSourceStatus());

		$type->save();

		$this->assertEquals('loaded', $type->getDataSourceStatus());

		$this->assertEquals(123, $type->id);
		$this->assertEquals('test', $type->title);
		$this->assertEquals(0, $type->global);

		$status = new Status();

		$status->setId(234);
		$status->setTitle('test2');
		$status->setGlobal(1);

		$this->assertEquals('new', $status->getDataSourceStatus());

		$status->save();

		$this->assertEquals('loaded', $status->getDataSourceStatus());

		$this->assertEquals(234, $status->getId());
		$this->assertEquals('test2', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());

		$user = new User();

		$user->firstName = 'Ryan';
		$user->lastName = 'Zec';
		$user->username = 'ryan.zec';
		$user->password = 'password';
		$user->email = 'test@test.com';
		$user->typeId = '1';
		$user->statusId = 1;

		$this->assertEquals(null, $user->id);
		$this->assertEquals('new', $user->getDataSourceStatus());

		$user->save();

		$this->assertEquals('loaded', $user->getDataSourceStatus());
		$this->assertEquals(3, $user->id);

		$databaseLoadedUser = new User($user->id);

		$this->assertEquals($user->id, $databaseLoadedUser->id);
		$this->assertEquals('Ryan', $databaseLoadedUser->firstName);
		$this->assertEquals('Zec', $databaseLoadedUser->lastName);
		$this->assertEquals('ryan.zec', $databaseLoadedUser->username);
		$this->assertEquals('password', $databaseLoadedUser->password);
		$this->assertEquals('test@test.com', $databaseLoadedUser->email);
		$this->assertEquals('1', $databaseLoadedUser->typeId);
		$this->assertEquals('1', $databaseLoadedUser->statusId);
		$this->assertEquals('active', $databaseLoadedUser->status);
	}

	/**
	 * @test
	 */
	public function updateExistingObject()
	{
		$type = new Type(1);

		$type->title = 'test';
		$type->global = 0;

		$type->save();

		$databaseLoadedType = new Type(1);

		$this->assertEquals(1, $databaseLoadedType->id);
		$this->assertEquals('test', $databaseLoadedType->title);
		$this->assertEquals(0, $databaseLoadedType->global);

		$status = new Status(1);

		$status->setTitle('test2');
		$status->setGlobal(1);

		$status->save();

		$databaseLoadedStatus = new Status(1);

		$this->assertEquals(1, $databaseLoadedStatus->getId());
		$this->assertEquals('test2', $databaseLoadedStatus->getTitle());
		$this->assertEquals(1, $databaseLoadedStatus->getGlobal());

		$user = new User(1);

		$user->firstName = 'Rya';
		$user->lastName = 'Ze';
		$user->username = 'ryan.ze';
		$user->password = 'passwor';
		$user->email = 'test@test.co';
		$user->typeId = '1';
		$user->statusId = 2;
		$user->save();

		$this->assertEquals(1, $user->id);

		$databaseLoadedUser = new User($user->id);

		$this->assertEquals($user->id, $databaseLoadedUser->id);
		$this->assertEquals('Rya', $databaseLoadedUser->firstName);
		$this->assertEquals('Ze', $databaseLoadedUser->lastName);
		$this->assertEquals('ryan.ze', $databaseLoadedUser->username);
		$this->assertEquals('passwor', $databaseLoadedUser->password);
		$this->assertEquals('test@test.co', $databaseLoadedUser->email);
		$this->assertEquals('1', $databaseLoadedUser->typeId);
		$this->assertEquals('2', $databaseLoadedUser->statusId);
		$this->assertEquals('inactive', $databaseLoadedUser->status);
	}

	/**
	 * @test
	 */
	public function toArray()
	{
		$type = new Type(1);
		$status = new Status(1);
		$user = new User(1);

		$typeExpected = array
		(
			'id' => '1',
			'title' => 'none',
			'global' => '1'
		);

		$statusExpected = array
		(
			'id' => '1',
			'title' => 'active',
			'global' => '1'
		);

		$userExpected = array
		(
			'id' => '1',
			'firstName' => 'Ryan',
			'lastName' => 'Zec',
			'username' => 'ryan.zec',
			'password' => 'password',
			'email' => 'test@test.com',
			'typeId' => '1',
			'statusId' => '1',
			'status' => 'active'
		);

		$this->assertEquals($typeExpected, $type->toArray());
		$this->assertEquals($statusExpected, $status->toArray());
		$this->assertEquals($userExpected, $user->toArray());
	}

	/**
	 * @test
	 */
	public function delete()
	{
		$type = new Type(2);

		$this->assertEquals('2', $type->id);
		$this->assertEquals('some', $type->title);
		$this->assertEquals('0', $type->global);

		$type->delete();

		$this->assertEquals(null, $type->id);
		$this->assertEquals(null, $type->title);
		$this->assertEquals(null, $type->global);

		$status = new Status(2);

		$this->assertEquals(2, $status->getId());
		$this->assertEquals('inactive', $status->getTitle());
		$this->assertEquals(0, $status->getGlobal());

		$status->delete();

		$this->assertEquals(null, $status->getId());
		$this->assertEquals(null, $status->getTitle());
		$this->assertEquals(null, $status->getGlobal());

		$user = new User(1);

		$this->assertEquals(1, $user->id);
		$this->assertEquals('Ryan', $user->firstName);
		$this->assertEquals('Zec', $user->lastName);
		$this->assertEquals('ryan.zec', $user->username);
		$this->assertEquals('password', $user->password);
		$this->assertEquals('test@test.com', $user->email);
		$this->assertEquals(1, $user->typeId);
		$this->assertEquals(1, $user->statusId);
		$this->assertEquals('active', $user->status);

		$user->delete();

		$this->assertEquals(null, $user->id);
		$this->assertEquals(null, $user->firstName);
		$this->assertEquals(null, $user->lastName);
		$this->assertEquals(null, $user->username);
		$this->assertEquals(null, $user->password);
		$this->assertEquals(null, $user->email);
		$this->assertEquals(null, $user->typeId);
		$this->assertEquals(null, $user->statusId);
	}

	/**
	 * @test
	 */
	public function getReferenceObjects()
	{
		$user = new User(1);
		$type = $user->getReferenceObjects('typeId', '\SalvoTests\Barrage\ActiveRecord\RelationalMapper', 'Type');
		$status = $user->getReferenceObjects('statusId', '\SalvoTests\Barrage\ActiveRecord\RelationalMapper', 'Status');

		$this->assertEquals('1', $type->id);
		$this->assertEquals('none', $type->title);
		$this->assertEquals('1', $type->global);

		$this->assertEquals(1, $status->getId());
		$this->assertEquals('active', $status->getTitle());
		$this->assertEquals(1, $status->getGlobal());
	}

	/**
	 * @test
	 */
	public function testFieldParsing()
	{
		$user = new User();
		$userResults = $user->getFields();

		$userExcepted = array
		(
			'id' => array
			(
				'name' => 'id'
			),
			'firstName' => array
			(
				'name' => 'first_name'
			),
			'lastName' => array
			(
				'name' => 'last_name'
			),
			'email' => array
			(
				'name' => 'email'
			),
			'username' => array
			(
				'name' => 'username',
				'required' => true
			),
			'password' => array
			(
				'name' => 'password'
			),
			'typeId' => array
			(
				'name' => 'type_id'
			),
			'statusId' => array
			(
				'name' => 'status_id'
			),
			'status' => array
			(
				'name' => 'title',
				'join_table' => 'sTaT_useS'
			)
		);

		$type = new Type();
		$typeResults = $type->getFields();

		$typeExcepted = array
		(
			'id' => array
			(
				'name' => 'id'
			),
			'title' => array
			(
				'name' => 'title'
			),
			'global' => array
			(
				'name' => 'global'
			)
		);

		$status = new Status();
		$statusResults = $status->getFields();

		$statusExcepted = array
		(
			'id' => array
			(
				'name' => 'ID'
			),
			'title' => array
			(
				'name' => 'tItLe'
			),
			'global' => array
			(
				'name' => 'GlObAl'
			)
		);

		$this->assertEquals($userExcepted, $userResults);
		$this->assertEquals($typeExcepted, $typeResults);
		$this->assertEquals($statusExcepted, $statusResults);
	}

	/**
	 * @test
	 */
	public function staticGet()
	{
		$types = Type::get(array('id' => 1));
		$statuses = Status::get(array('id' => array('condition' => '<', 'value' => 2)));
		$users = User::get();

		$this->assertEquals(1, count($types));
		$this->assertEquals(1, count($statuses));
		$this->assertEquals(2, count($users));
	}
}
