<?php
namespace SalvoTests\Barrage\DataSource\Relational\Drivers\Mysql;

use \SalvoTests\Barrage\BaseTestCase;
use \Salvo\Barrage\DataSource\Relational\Driver\Mysql;
use Salvo\Barrage\DataSource\Relational\Exception\RelationalSqlException;
use Salvo\Barrage\DataSource\Relational\Exception\ConnectionException;
use Salvo\Barrage\Configuration;

/**
 * DataSource test suite
 */
class DataSourceTest extends BaseTestCase
{
    private $database1 = 'barrage';
    private $database2 = 'barrage_test';
    private $trueDatabase1 = null;
    private $trueDatabase2 = null;

    /**
     * @var Mysql\DataSource
     */
    private $databaseConnection;
    private $databaseConnectionData;
    private $databaseConnectionData2;

    public function __construct()
    {
        parent::__construct();

        $this->trueDatabase1 = Configuration::getRealDatabaseName($this->database1);
        $this->trueDatabase2 = Configuration::getRealDatabaseName($this->database2);
    }

    public function setup()
    {


        parent::setUp();
        
        //lets initially connect to the mysql data since that is guaranteed to exist
        $host = '127.0.0.1';
        $username = 'root';
        $password = '';
        $database = $this->database1;

        $this->databaseConnectionData = new Mysql\ConnectionData($host, $username, $password, $database);
        $this->databaseConnectionData2 = new Mysql\ConnectionData('localhost', $username, $password, $database);
        $this->databaseConnection = Mysql\DataSource::getInstance($this->databaseConnectionData);
    }
    
    /**
     * This function is used to setup and tear down the database for the tests in this class
     * Override in the child child class to load data into the database
     *
     * @return string The path to the data file to load for these tests
     */
    public function getDataFileLocations()
    {
        return array(__DIR__ . '/yml/DataSourceTest.yml');
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
            ),
            'ut_barrage_test' => array
            (
                'test'
            )
        );
    }

    /**
     * @test
     */
    public function cleanQuotes()
    {
        $value1 = "it's";
        $value2 = "3\" 2'";
        $value3 = "hello world";

        $this->assertEquals("'it\\'s'", $this->databaseConnection->cleanQuote($value1));
        $this->assertEquals("'3\\\" 2\\''", $this->databaseConnection->cleanQuote($value2));
        $this->assertEquals("'hello world'", $this->databaseConnection->cleanQuote($value3));

        $this->assertEquals("it\\'s", $this->databaseConnection->cleanQuote($value1, false));
        $this->assertEquals("3\\\" 2\\'", $this->databaseConnection->cleanQuote($value2, false));
        $this->assertEquals("hello world", $this->databaseConnection->cleanQuote($value3, false));
    }

    /**
     * @test
     */
    public function getAll()
    {
        $sql = "SELECT id, title, global
                FROM `types`
                ORDER BY id";
        $results = $this->databaseConnection->getAll($sql);

        $excepted = array
        (
            array
            (
                'id' => '1',
                'title' => 'none',
                'global' => '1'
            ),
            array
            (
                'id' => '2',
                'title' => 'some',
                'global' => '0'
            )
        );

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function getColumn()
    {
        $sql = "SELECT title, global
                FROM `types`
                ORDER BY id";
        $results = $this->databaseConnection->getColumn($sql);

        $excepted = array
        (
            'none',
            'some'
        );

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function getOne()
    {
        $sql = "SELECT title, global
                FROM `types`
                ORDER BY id";
        $results = $this->databaseConnection->getOne($sql);

        $this->assertEquals('none', $results);
    }

    /**
     * @test
     */
    public function getRow()
    {
        $sql = "SELECT id, title, global
                FROM `types`
                ORDER BY id";
        $results = $this->databaseConnection->getRow($sql);

        $excepted = array
        (
            'id' => '1',
            'title' => 'none',
            'global' => '1'
        );

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function getServerVendorName()
    {
        $this->assertEquals('mysql', $this->databaseConnection->getServerVendorName());
    }

    /**
     * @test
     */
    public function query()
    {
        $sql = "SELECT *
                FROM types";
        $results = $this->databaseConnection->query($sql);

        $this->assertEquals('PDOStatement', get_class($results));
    }

    /**
     * @test
     */
    public function insert()
    {
        $table = 'types';
        $data = array
        (
            'title' => 'admin',
            'global' => 0
        );

        $newId = $this->databaseConnection->insert($table, $data);

        $this->assertEquals('3', $newId);

        $sql = "SELECT *
                FROM {$table}
                WHERE id = '{$newId}'";
        $newRecord = $this->databaseConnection->getRow($sql);

        $excepted = array
        (
            'id' => '3',
            'title' => 'admin',
            'global' => '0',
            'enum' => null,
            'set' => null
        );

        $this->assertEquals($excepted, $newRecord);
    }

    /**
     * @test
     */
    public function insertOnNonDefaultDatabase()
    {
        $table = 'test';
        $data = array
        (
            'title' => 'a test record'
        );

        $newId = $this->databaseConnection->insert($table, $data, $this->database2);

        $this->assertEquals('3', $newId);


        $sql = "SELECT *
                FROM {$this->trueDatabase2}.{$table}
                WHERE id = '{$newId}'";
        $newRecord = $this->databaseConnection->getRow($sql);

        $excepted = array
        (
            'id' => '3',
            'title' => 'a test record',
        );

        $this->assertEquals($excepted, $newRecord);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderFromOnly()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from);
        $excepted = "SELECT `users`.`id`, `users`.`username`, `users`.`password` FROM `{$this->trueDatabase1}`.`users` AS `users`";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderFromOnlyWithFieldAlias()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users`";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithJoin()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password' => 'users',
            'types.title as type_title' => 'types',
            'stt.title AS status_title' => 'statuses'
        );
        $from = array
        (
            'name' => 'users',
            'alias' => 'usr',
            'database' => $this->database1

        );
        $join = array
        (
            'types' => array
            (
                'on' => '`types`.`id` = `usr`.`type_id`',
                'type' => 'left'
            ),
            'statuses' => array
            (
                'alias' => 'stt',
                'on' => '`stt`.`id` = `usr`.`status_id`',
                'database' => 'test'
            )
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, $join);
        $excepted = "SELECT `usr`.`id`, `usr`.`username`, `usr`.`password`, types.title AS `type_title`, stt.title AS `status_title` FROM `{$this->trueDatabase1}`.`users` AS `usr` LEFT JOIN `{$this->trueDatabase1}`.`types` AS `types` ON `types`.`id` = `usr`.`type_id` INNER JOIN `test`.`statuses` AS `stt` ON `stt`.`id` = `usr`.`status_id`";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithSimpleWhere()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $where = array
        (
            'id' => 1,
            'username' => 'test'
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, null, $where);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` WHERE (`users`.`id` = '1') AND (`users`.`username` = 'test')";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithComplexWhere()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $where = array
        (
            'username' => 'test',
            'id' => array
            (
                'value' => array(1,2,3)
            ),
            'id2' => array
            (
                'value' => '123',
                'condition' => '!='
            ),
            'id3' => array
            (
                'value' => '%test%',
                'condition' => 'like'
            ),
            'id4' => array
            (
                'value' => '10',
                'condition' => '>='
            ),
            'id5' => array
            (
                'value' => array(1,100),
                'condition' => 'between'
            ),
            'id6' => array
            (
                'condition' => '!=',
                'value' => null
            ),
            'id7' => null
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, null, $where);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` WHERE (`users`.`username` = 'test') AND (`users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123') AND (`users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10') AND (`users`.`id5` BETWEEN '1' AND '100') AND (`users`.`id6` IS NOT NULL) AND (`users`.`id7` IS NULL)";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithVeryComplexWhere()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $where = array
        (
            array
            (
                'and' => array
                (
                    'username' => 'test',
                    'id' => array
                    (
                        'value' => array(1,2,3)
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id2' => array
                    (
                        'value' => '123',
                        'condition' => '!='
                    ),
                    'id3' => array
                    (
                        'value' => '%test%',
                        'condition' => 'like'
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id4' => array
                    (
                        'value' => '10',
                        'condition' => '>='
                    ),
                    'id5' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id6' => array
                    (
                        'value' => array(1,100),
                        'condition' => '!='
                    ),
                    'id6_2' => null
                )
            ),
            array
            (
                'or' => array
                (
                    'id7' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8_2' => array
                    (
                        'condition' => '!=',
                        'value' => null
                    )
                )
            )
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, null, $where);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` WHERE (`users`.`username` = 'test' AND `users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123' OR `users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10' OR `users`.`id5` BETWEEN '1' AND '100' OR `users`.`id6` NOT IN('1', '100') OR `users`.`id6_2` IS NULL) AND (`users`.`id7` BETWEEN '1' AND '100' OR `users`.`id8` BETWEEN '1' AND '100' OR `users`.`id8_2` IS NOT NULL)";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithJoinVeryComplexWhere()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $join = array
        (
            'types' => array
            (
                'on' => '`types`.`id` = `usr`.`type_id`',
                'type' => 'left'
            ),
            'statuses' => array
            (
                'alias' => 'stt',
                'on' => '`stt`.`id` = `usr`.`status_id`',
                'database' => 'test'
            )
        );
        $where = array
        (
            array
            (
                'and' => array
                (
                    'username' => 'test',
                    'id' => array
                    (
                        'value' => array(1,2,3)
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id2' => array
                    (
                        'value' => '123',
                        'condition' => '!='
                    ),
                    'id3' => array
                    (
                        'value' => '%test%',
                        'condition' => 'like'
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id4' => array
                    (
                        'value' => '10',
                        'condition' => '>='
                    ),
                    'id5' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id6' => array
                    (
                        'value' => array(1,100),
                        'condition' => '!='
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id7' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    )
                )
            )
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, $join, $where);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` LEFT JOIN `{$this->trueDatabase1}`.`types` AS `types` ON `types`.`id` = `usr`.`type_id` INNER JOIN `test`.`statuses` AS `stt` ON `stt`.`id` = `usr`.`status_id` WHERE (`users`.`username` = 'test' AND `users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123' OR `users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10' OR `users`.`id5` BETWEEN '1' AND '100' OR `users`.`id6` NOT IN('1', '100')) AND (`users`.`id7` BETWEEN '1' AND '100' OR `users`.`id8` BETWEEN '1' AND '100')";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithJoinVeryComplexWhereGroup()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $join = array
        (
            'types' => array
            (
                'on' => '`types`.`id` = `usr`.`type_id`',
                'type' => 'left'
            ),
            'statuses' => array
            (
                'alias' => 'stt',
                'on' => '`stt`.`id` = `usr`.`status_id`',
                'database' => 'test'
            )
        );
        $where = array
        (
            array
            (
                'and' => array
                (
                    'username' => 'test',
                    'id' => array
                    (
                        'value' => array(1,2,3)
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id2' => array
                    (
                        'value' => '123',
                        'condition' => '!='
                    ),
                    'id3' => array
                    (
                        'value' => '%test%',
                        'condition' => 'like'
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id4' => array
                    (
                        'value' => '10',
                        'condition' => '>='
                    ),
                    'id5' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id6' => array
                    (
                        'value' => array(1,100),
                        'condition' => '!='
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id7' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    )
                )
            )
        );
        $group = array
        (
            'id1',
            'id2' => 'types',
            'id3',
            'id4' => 'types'
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, $join, $where, $group);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` LEFT JOIN `{$this->trueDatabase1}`.`types` AS `types` ON `types`.`id` = `usr`.`type_id` INNER JOIN `test`.`statuses` AS `stt` ON `stt`.`id` = `usr`.`status_id` WHERE (`users`.`username` = 'test' AND `users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123' OR `users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10' OR `users`.`id5` BETWEEN '1' AND '100' OR `users`.`id6` NOT IN('1', '100')) AND (`users`.`id7` BETWEEN '1' AND '100' OR `users`.`id8` BETWEEN '1' AND '100') GROUP BY `users`.`id1`, `types`.`id2`, `users`.`id3`, `types`.`id4`";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithJoinVeryComplexWhereGroupOrder()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $join = array
        (
            'types' => array
            (
                'on' => '`types`.`id` = `usr`.`type_id`',
                'type' => 'left'
            ),
            'statuses' => array
            (
                'alias' => 'stt',
                'on' => '`stt`.`id` = `usr`.`status_id`',
                'database' => 'test'
            )
        );
        $where = array
        (
            array
            (
                'and' => array
                (
                    'username' => 'test',
                    'id' => array
                    (
                        'value' => array(1,2,3)
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id2' => array
                    (
                        'value' => '123',
                        'condition' => '!='
                    ),
                    'id3' => array
                    (
                        'value' => '%test%',
                        'condition' => 'like'
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id4' => array
                    (
                        'value' => '10',
                        'condition' => '>='
                    ),
                    'id5' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id6' => array
                    (
                        'value' => array(1,100),
                        'condition' => '!='
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id7' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    )
                )
            )
        );
        $group = array
        (
            'id1',
            'id2' => 'types',
            'id3',
            'id4' => 'types'
        );
        $order = array
        (
            'id1',
            'id2' => 'types',
            'id3 desc',
            'id4 DESC' => 'types'
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, $join, $where, $group, $order);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` LEFT JOIN `{$this->trueDatabase1}`.`types` AS `types` ON `types`.`id` = `usr`.`type_id` INNER JOIN `test`.`statuses` AS `stt` ON `stt`.`id` = `usr`.`status_id` WHERE (`users`.`username` = 'test' AND `users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123' OR `users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10' OR `users`.`id5` BETWEEN '1' AND '100' OR `users`.`id6` NOT IN('1', '100')) AND (`users`.`id7` BETWEEN '1' AND '100' OR `users`.`id8` BETWEEN '1' AND '100') GROUP BY `users`.`id1`, `types`.`id2`, `users`.`id3`, `types`.`id4` ORDER BY `users`.`id1` ASC, `types`.`id2` ASC, `users`.`id3` DESC, `types`.`id4` DESC";
        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleSelectBuilderWithJoinVeryComplexWhereGroupOrderLimit()
    {
        $select = array
        (
            'id' => 'users',
            'username' => 'users',
            'password AS user_password' => 'users'
        );
        $from = array
        (
            'name' => 'users',
            'database' => $this->database1

        );
        $join = array
        (
            'types' => array
            (
                'on' => '`types`.`id` = `usr`.`type_id`',
                'type' => 'left'
            ),
            'statuses' => array
            (
                'alias' => 'stt',
                'on' => '`stt`.`id` = `usr`.`status_id`',
                'database' => 'test'
            )
        );
        $where = array
        (
            array
            (
                'and' => array
                (
                    'username' => 'test',
                    'id' => array
                    (
                        'value' => array(1,2,3)
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id2' => array
                    (
                        'value' => '123',
                        'condition' => '!='
                    ),
                    'id3' => array
                    (
                        'value' => '%test%',
                        'condition' => 'like'
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id4' => array
                    (
                        'value' => '10',
                        'condition' => '>='
                    ),
                    'id5' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id6' => array
                    (
                        'value' => array(1,100),
                        'condition' => '!='
                    )
                )
            ),
            array
            (
                'or' => array
                (
                    'id7' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    ),
                    'id8' => array
                    (
                        'value' => array(1,100),
                        'condition' => 'between'
                    )
                )
            )
        );
        $group = array
        (
            'id1',
            'id2' => 'types',
            'id3',
            'id4' => 'types'
        );
        $order = array
        (
            'id1',
            'id2' => 'types',
            'id3 desc',
            'id4 DESC' => 'types'
        );

        $results = $this->databaseConnection->simpleSelectBuilder($select, $from, $join, $where, $group, $order, 10, 10);
        $excepted = "SELECT `users`.`id`, `users`.`username`, password AS `user_password` FROM `{$this->trueDatabase1}`.`users` AS `users` LEFT JOIN `{$this->trueDatabase1}`.`types` AS `types` ON `types`.`id` = `usr`.`type_id` INNER JOIN `test`.`statuses` AS `stt` ON `stt`.`id` = `usr`.`status_id` WHERE (`users`.`username` = 'test' AND `users`.`id` IN('1', '2', '3')) AND (`users`.`id2` != '123' OR `users`.`id3` LIKE '%test%') AND (`users`.`id4` >= '10' OR `users`.`id5` BETWEEN '1' AND '100' OR `users`.`id6` NOT IN('1', '100')) AND (`users`.`id7` BETWEEN '1' AND '100' OR `users`.`id8` BETWEEN '1' AND '100') GROUP BY `users`.`id1`, `types`.`id2`, `users`.`id3`, `types`.`id4` ORDER BY `users`.`id1` ASC, `types`.`id2` ASC, `users`.`id3` DESC, `types`.`id4` DESC LIMIT 10, 10";

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleInsertBuilder()
    {
        $table = 'types';
        $data = array
        (
            'id' => '123',
            'title' => 'my-status'
        );

        $excepted = "INSERT INTO `{$this->trueDatabase1}`.`types`(`id`, `title`) VALUES('123', 'my-status')";
        $results = $this->databaseConnection->simpleInsertBuilder($table, $data);

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function simpleUpdateBuilder()
    {
        $table = 'types';
        $data = array
        (
            'title' => 'my-status',
            'global' => 0
        );
        $where = "`id` = '2'";

        $excepted = "UPDATE `{$this->trueDatabase1}`.`types` SET `title` = 'my-status', `global` = '0' WHERE `id` = '2'";
        $results = $this->databaseConnection->simpleUpdateBuilder($table, $data, $where);

        $this->assertEquals($excepted, $results);
    }

    /**
     * @test
     */
    public function update()
    {
        $table = 'types';
        $data = array
        (
            'title' => 'inactive-updated'
        );
        $where = "id = '2'";

        $sql = "SELECT title
                FROM {$table}
                WHERE id = '2'";
        $loadedRecord = $this->databaseConnection->getRow($sql);

        $this->assertEquals('some', $loadedRecord['title']);

        $this->databaseConnection->update($table, $data, $where);

        $sql = "SELECT title
                FROM {$table}
                WHERE id = '2'";
        $updatedRecord = $this->databaseConnection->getRow($sql);

        $this->assertEquals('inactive-updated', $updatedRecord['title']);
    }

    /**
     * @test
     */
    public function updateOnNonDefaultDatabase()
    {
        $table = 'test';
        $data = array
        (
            'title' => 'yml file updated'
        );
        $where = "id = '2'";

        $sql = "SELECT title
                FROM {$this->trueDatabase2}.{$table}
                WHERE id = '2'";
        $loadedRecord = $this->databaseConnection->getRow($sql);

        $this->assertEquals('yml file', $loadedRecord['title']);

        $this->databaseConnection->update($table, $data, $where, $this->database2);

        $sql = "SELECT title
                FROM {$this->trueDatabase2}.{$table}
                WHERE id = '2'";
        $updatedRecord = $this->databaseConnection->getRow($sql);

        $this->assertEquals('yml file updated', $updatedRecord['title'], $this->database2);
    }

    /**
     * @test
     */
    public function transactionCommit()
    {
        $updateSql = "UPDATE types SET title = 'transaction-test' WHERE id = 1";
        $selectSql = "SELECT title FROM types WHERE id = 1";

        $this->databaseConnection->startTransaction();
        $this->databaseConnection->query($updateSql);

        $secondTestDatabaseConnection = Mysql\DataSource::getInstance($this->databaseConnectionData2);

        $record = $secondTestDatabaseConnection->getRow($selectSql);
        $this->assertEquals('none', $record['title']);

        $this->databaseConnection->commitTransaction();

        $record = $secondTestDatabaseConnection->getRow($selectSql);
        $this->assertEquals('transaction-test', $record['title']);
    }

    /**
     * @test
     */
    public function delete()
    {
        $selectSql = "SELECT * FROM types";
        $results = $this->databaseConnection->getAll($selectSql);

        $this->assertEquals(2, count($results));

        $table = 'types';
        $where = 'id > 0';
        $this->databaseConnection->delete($table, $where);
        $results = $this->databaseConnection->getAll($selectSql);

        $this->assertEquals(0, count($results));
    }

    /**
     * @test
     */
    public function getCreateStatement()
    {
        $createStatementString = $this->databaseConnection->getCreateStatement('users');
        $createStatementArray = $this->databaseConnection->getCreateStatement('users', true);

        $exceptedString = "CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `last_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `email` varchar(256) COLLATE utf8_bin NOT NULL,
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `status_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `users_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `sTaT_useS` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
        $exceptedArray = array
        (
            "CREATE TABLE `users` (",
            "`id` int(10) unsigned NOT NULL AUTO_INCREMENT,",
            "`first_name` varchar(64) COLLATE utf8_bin NOT NULL,",
            "`last_name` varchar(64) COLLATE utf8_bin NOT NULL,",
            "`email` varchar(256) COLLATE utf8_bin NOT NULL,",
            "`username` varchar(64) COLLATE utf8_bin NOT NULL,",
            "`password` varchar(32) COLLATE utf8_bin NOT NULL,",
            "`type_id` int(10) unsigned DEFAULT NULL,",
            "`status_id` int(10) unsigned DEFAULT NULL,",
            "PRIMARY KEY (`id`),",
            "KEY `type_id` (`type_id`),",
            "KEY `status_id` (`status_id`),",
            "CONSTRAINT `users_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,",
            "CONSTRAINT `users_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `sTaT_useS` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE",
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
        );

        $this->assertSame($exceptedString, $createStatementString);
        $this->assertSame($exceptedArray, $createStatementArray);
    }

    /**
     * @test
     */
    public function getForeignKeys()
    {
        $foreignKeys = $this->databaseConnection->getForeignKeys('users');

        $excepted = array
        (
            'type_id' => 'types.id',
            'status_id' => 'sTaT_useS.ID'
        );

        $this->assertSame($excepted, $foreignKeys);
    }

    /**
     * @test
     */
    public function getTableFieldsDetails()
    {
        $details = $this->databaseConnection->getTableFieldsDetails('users');

        $excepted = array
        (
            array
            (
                'field' => 'id',
                'field_type' => 'int',
                'field_type_detailed' => 'int(10) unsigned',
                'key_type' => 'primary',
                'foreign_table' => NULL,
                'unique' => true,
                'required' => true,
                'auto_increment' => true
            ),
            array
            (
                'field' => 'first_name',
                'field_type' => 'varchar',
                'field_type_detailed' => 'varchar(64)',
                'key_type' => 'none',
                'foreign_table' => NULL,
                'unique' => false,
                'required' => true,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'last_name',
                'field_type' => 'varchar',
                'field_type_detailed' => 'varchar(64)',
                'key_type' => 'none',
                'foreign_table' => NULL,
                'unique' => false,
                'required' => true,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'email',
                'field_type' => 'varchar',
                'field_type_detailed' => 'varchar(256)',
                'key_type' => 'none',
                'foreign_table' => NULL,
                'unique' => false,
                'required' => true,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'username',
                'field_type' => 'varchar',
                'field_type_detailed' => 'varchar(64)',
                'key_type' => 'none',
                'foreign_table' => NULL,
                'unique' => false,
                'required' => true,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'password',
                'field_type' => 'varchar',
                'field_type_detailed' => 'varchar(32)',
                'key_type' => 'none',
                'foreign_table' => NULL,
                'unique' => false,
                'required' => true,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'type_id',
                'field_type' => 'int',
                'field_type_detailed' => 'int(10) unsigned',
                'key_type' => 'foreign',
                'foreign_table' => 'types',
                'unique' => false,
                'required' => false,
                'auto_increment' => false
            ),
            array
            (
                'field' => 'status_id',
                'field_type' => 'int',
                'field_type_detailed' => 'int(10) unsigned',
                'key_type' => 'foreign',
                'foreign_table' => 'sTaT_useS',
                'unique' => false,
                'required' => false,
                'auto_increment' => false
            )
        );

        $this->assertSame($excepted, $details);
    }

    /**
     * @todo
     */
    public function getIndexesForTable()
    {
        $excepted = array
        (

        );
    }

    /**
     * @todo
     */
    public function getIndexesForField()
    {

    }

    /**
     * @test
     */
    public function getFieldValues()
    {
        $enumValues = $this->databaseConnection->getFieldValues('types', 'enum');
        $setValues = $this->databaseConnection->getFieldValues('types', 'set');

        $exceptedEnum = array('one', 'two', 'no_value');
        $exceptedSet = array('some', 'value_here', 'hello');

        $this->assertEquals($exceptedEnum, $enumValues);
        $this->assertEquals($exceptedSet, $setValues);
    }

    /**
     * @test
     */
    public function getTables()
    {
        $tables = $this->databaseConnection->getTables();
        $excepted = array('sTaT_useS', 'types', 'users');

        $this->assertEquals($excepted, $tables);
    }

    /**
     * @test
     */
    public function getTablesNonDefaultDatabase()
    {
        $tables = $this->databaseConnection->getTables($this->database2);
        $excepted = array('test');

        $this->assertEquals($excepted, $tables);
    }

    /*****************************************************************************************************************/
    /* EXCEPTION TESTS ***********************************************************************************************/
    /*****************************************************************************************************************/

    /**
     * @test
     */
    public function UnableToConnectException()
    {
        $exceptionCaught = false;

        try
        {
            $host = '127.0.0.1';
            $username = 'roo';
            $password = 'password';

            $connectionData = new Mysql\ConnectionData($host, $username, $password, $this->database1);
            Mysql\DataSource::getInstance($connectionData);
        }
        catch(ConnectionException $exception)
        {
            $exceptionCaught = true;
        }

        $this->assertEquals(true, $exceptionCaught);
    }

    /**
     * @test
     */
    public function sqlErrorException()
    {
        $exceptionCaught = false;

        try
        {
            $sql = "bad sql statement";
            $this->databaseConnection->query($sql);
        }
        catch(RelationalSqlException $exception)
        {
            $exceptionCaught = true;
        }

        $this->assertEquals(true, $exceptionCaught);
    }

    /**
     * @test
     */
    public function transactionAlreadyStartedException()
    {
        $exceptionCaught = false;
        $exceptionMessage = null;

        try
        {
            $this->databaseConnection->startTransaction();
            $this->databaseConnection->startTransaction();
        }
        catch(RelationalSqlException $exception)
        {
            $exceptionCaught = true;
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertEquals(true, $exceptionCaught);
        $this->assertEquals('Unable to start a new transaction as another one is already in progress', $exceptionMessage);
    }

    /**
     * @test
     */
    public function clearQuotesArrayException()
    {
        $exceptionCaught = false;
        $exceptionMessage = null;

        try
        {
            $this->databaseConnection->cleanQuote(array('test'));
        }
        catch(RelationalSqlException $exception)
        {
            $exceptionCaught = true;
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertEquals(true, $exceptionCaught);
        $this->assertEquals("Can't clean array variable", $exceptionMessage);

    }
}
