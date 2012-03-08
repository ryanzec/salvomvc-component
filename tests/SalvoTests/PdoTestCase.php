<?php
namespace SalvoTests;

use Symfony\Component\Yaml\Yaml;

abstract class PdoTestCase extends \PHPUnit_Framework_TestCase
{
    protected $debug = true;

    private $schema;

    static protected $pdoConnection;

    public function __construct(\PDO $pdoConnection)
    {
        parent::__construct();

        $this->schema = $this->getSchema();

        if(!$pdoConnection instanceof \PDO)
        {
            throw new \Exception('Unit tests extending PdoTestCase must provide an instance of a PDO connection in the constructor');
        }

        self::$pdoConnection = $pdoConnection;
    }

    /**
    * This function is used to setup and tear down the database for the tests in this class
    * Override in the child child class to load data into the database
    *
    * @return string The path to the data file to load for these tests
    */
    public function getDataFileLocations()
    {
        return array();
    }

    public function getSchema()
    {
        return array();
    }

    /**
    * Executed before each test method to setup the environment
    *
    * @return void
    */
    protected function setUp()
    {
        if(self::$pdoConnection instanceof \PDO)
        {
            $this->dropSchema();
            $this->setupSchema();

            //see if we have a data file to load
            if(method_exists($this, 'getDataFileLocations'))
            {
                $test_yml_file_locations = $this->getDataFileLocations();

                //if the data file location is just a string, convert it into an array automatically
                if(!is_array($test_yml_file_locations) && !empty($test_yml_file_locations))
                {
                    $test_yml_file_locations = array($test_yml_file_locations);
                }

                //make sure we have at least one file to parse
                if(!empty($test_yml_file_locations))
                {
                    //let truncate all the tables reference in any of the yml files
                    $this->databaseTearDown();

                    foreach($test_yml_file_locations as $test_yml_file_location)
                    {
                        //make sure the file exists
                        if(file_exists($test_yml_file_location))
                        {
                            if($this->debug)
                            {
                                echo 'loading data file: ' . $test_yml_file_location . "\n";
                            }

                            $yml_configurations = Yaml::parse($test_yml_file_location);

                            //make sure there is some data in the yml file
                            if(!empty($yml_configurations))
                            {
                                foreach($yml_configurations as $database_name => $table_info)
                                {
                                    foreach($table_info as $table_name => $table_data)
                                    {
                                        if(!empty($table_data))
                                        {
                                            foreach($table_data as $table_row)
                                            {
                                                $this->internalInsert($table_name, $table_row, $database_name);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            //if we can't find the file, kill the unit tests and echo out the file we couldn't find
                            die('Unable to data find file ' . $test_yml_file_location);
                        }
                    }
                }
            }
        }
    }

    /**
    * Executed after each test method to clean up the environment
    *
    * @return void
    */
    protected function tearDown()
    {
        $this->databaseTearDown();

        if(self::$pdoConnection instanceof \PDO)
        {
            $this->dropSchema();
        }
    }

    private function databaseTearDown()
    {
        if(self::$pdoConnection instanceof \PDO)
        {
            //see if we had a data file that we loaded to see what table to need to truncate since the tests are down
            if(method_exists($this, 'getDataFileLocations'))
            {
                $test_yml_file_locations = $this->getDataFileLocations();

                //if the data file location is just a string, convert it into an array automatically
                if(!is_array($test_yml_file_locations) && !empty($test_yml_file_locations))
                {
                    $test_yml_file_locations = array($test_yml_file_locations);
                }

                //make sure we have at least one file to parse
                if(!empty($test_yml_file_locations))
                {
                    foreach($test_yml_file_locations as $test_yml_file_location)
                    {
                        //make sure the file exists
                        if(file_exists($test_yml_file_location))
                        {
                            if($this->debug)
                            {
                                echo 'truncating tables from data file: ' . $test_yml_file_location . "\n";
                            }

                            $yml_configurations = Yaml::parse($test_yml_file_location);

                            //make sure there is some data in the yml file
                            if(!empty($yml_configurations))
                            {
                                foreach($yml_configurations as $database_name => $table_info)
                                {
                                    foreach($table_info as $table_name => $table_data)
                                    {
                                        $query = "TRUNCATE `{$database_name}`.`{$table_name}`;";
                                        self::$pdoConnection->query($query);
                                    }
                                }
                            }
                        }
                        else
                        {
                            //if we can't find the file, kill the unit tests and echo out the file we couldn't find
                            die('Unable to find data file ' . $test_yml_file_location);
                        }
                    }
                }
            }
        }
    }

    protected function setupSchema()
    {
        if(!empty($this->schema))
        {
            foreach($this->schema as $database => $tables)
            {
                //create and select the database
                $sql = "CREATE DATABASE IF NOT EXISTS {$database};USE $database;";
                self::$pdoConnection->query($sql);

                if(!empty($tables))
                {
                    foreach($tables as $table)
                    {
                        $sql = file_get_contents(__DIR__ . '/schema/' . $database . '/' . $table . '.sql');
                        self::$pdoConnection->query($sql);
                    }
                }
            }
        }
    }

    protected function dropSchema()
    {
        if(!empty($this->schema))
        {
            foreach($this->schema as $database => $tables)
            {
                $sql = "DROP DATABASE IF EXISTS {$database}";
                self::$pdoConnection->query($sql);
            }
        }
    }

    private function internalInsert($table, $data, $database = null)
    {
        $table = (!empty($database)) ? "`{$database}`.`{$table}`" : "`{$table}`";
        $fields = null;
        $values = null;

        foreach($data as $field => $value)
        {
            $value = self::$pdoConnection->quote($value);
            $fields .= (empty($fields)) ? "`{$field}`" : ", `{$field}`";
            $values .= (empty($values)) ? "{$value}" : ", {$value}";
        }

        self::$pdoConnection->query("INSERT INTO {$table}({$fields}) VALUES({$values})");
    }
}
