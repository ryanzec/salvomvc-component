<?php
namespace SalvoTests\Barrage\ActiveRecord\RelationalMapper;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Salvo\Barrage\Console\Command\ActiveRecord\Relational\ModelBuilder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use SalvoTests\Barrage\BaseTestCase;
use Salvo\Barrage\Console;

/**
 * Console test suite
 */
class ConsoleTest extends BaseTestCase
{
    private $modelDirectory;

    private function deleteUnitTestModelFiles()
    {
        if(is_dir($this->modelDirectory))
        {
            $directory = opendir($this->modelDirectory);

            while(false !== ($file = readdir($directory)))
            {
                if(substr($file, -4) === '.php')
                {
                    unlink($this->modelDirectory . '/' . $file);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return array
        (
            'ut_barrage' => array
            (
                'sTaT_uteS',
                'types',
                'users',
                'UsersTwo'
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->modelDirectory =  __DIR__ . '/../Model/UnitTest/Model/barrage';
    }

    /**
     * @test
     */
    public function relationalModelBuilder()
    {
        $this->deleteUnitTestModelFiles();

        /*$application = new Application();
        $application->add(new ModelBuilder());

        $command = $application->find('relational:model_builder --database="Security"');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/.../', $commandTester->getDisplay());*/

        //not sure if this is the best way to test the command but I can't figure out how to use the CommandTester object
        $application = new Console\Console();
        $command = $command = $application->find('relational:model_builder');
        $output = new NullOutput();
        $arguments = array
        (
            'table' => null,
            '-d' => true,
            '--database' => 'barrage'
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);

        $statusExceptedClass =
"<?php
namespace UnitTest\\Model\\barrage;

use Salvo\\Barrage\\ActiveRecord\\RelationalMapper\\ActiveRecord;

class Status extends ActiveRecord
{
    /**
     * IMPORTANT: Code in-between @ActiveRecordStart and @ActiveRecordEnd will be overwritten when using console to update model class, DON'T modify code
     * in-between these two PHPDoc tags or certain console functionality will not work properly.
     *
     * @ActiveRecordStart
     */
    protected static \$database = 'barrage';
    protected static \$primaryKey = array('ID');
    protected static \$autoIncrementedField = 'ID';
    protected static \$table = array('name'=>'sTaT_useS','alias'=>'stt');
    protected static \$joins = array();
    protected static \$fields = array('id'=>array('name'=>'ID'),'tItLe'=>array('name'=>'tItLe'),'glObAl'=>array('name'=>'GlObAl'));
    protected static \$dataSourceConfiguration = 'default';
    protected static \$skipSaveMembers = array();

    protected \$id;
    protected \$tItLe;
    protected \$glObAl;
    /**
     * @ActiveRecordEnd
     */
}
";

        $typeExceptedClass =
"<?php
namespace UnitTest\\Model\\barrage;

use Salvo\\Barrage\\ActiveRecord\\RelationalMapper\\ActiveRecord;

class Type extends ActiveRecord
{
    /**
     * IMPORTANT: Code in-between @ActiveRecordStart and @ActiveRecordEnd will be overwritten when using console to update model class, DON'T modify code
     * in-between these two PHPDoc tags or certain console functionality will not work properly.
     *
     * @ActiveRecordStart
     */
    protected static \$database = 'barrage';
    protected static \$primaryKey = array('id');
    protected static \$autoIncrementedField = 'id';
    protected static \$table = array('name'=>'types','alias'=>'typ');
    protected static \$joins = array();
    protected static \$fields = array('id'=>array('name'=>'id'),'title'=>array('name'=>'title'),'global'=>array('name'=>'global'),'enum'=>array('name'=>'enum','values'=>array('one','two','no_value')),'set'=>array('name'=>'set','values'=>array('some','value_here','hello')));
    protected static \$dataSourceConfiguration = 'default';
    protected static \$skipSaveMembers = array();

    protected \$id;
    protected \$title;
    protected \$global;
    protected \$enum;
    protected \$set;
    /**
     * @ActiveRecordEnd
     */
}
";

        $userExceptedClass =
"<?php
namespace UnitTest\\Model\\barrage;

use Salvo\\Barrage\\ActiveRecord\\RelationalMapper\\ActiveRecord;

class User extends ActiveRecord
{
    /**
     * IMPORTANT: Code in-between @ActiveRecordStart and @ActiveRecordEnd will be overwritten when using console to update model class, DON'T modify code
     * in-between these two PHPDoc tags or certain console functionality will not work properly.
     *
     * @ActiveRecordStart
     */
    protected static \$database = 'barrage';
    protected static \$primaryKey = array('id');
    protected static \$autoIncrementedField = 'id';
    protected static \$table = array('name'=>'users','alias'=>'usr');
    protected static \$joins = array();
    protected static \$fields = array('id'=>array('name'=>'id'),'firstName'=>array('name'=>'first_name'),'lastName'=>array('name'=>'last_name'),'email'=>array('name'=>'email'),'username'=>array('name'=>'username'),'password'=>array('name'=>'password'),'typeId'=>array('name'=>'type_id'),'statusId'=>array('name'=>'status_id'));
    protected static \$dataSourceConfiguration = 'default';
    protected static \$skipSaveMembers = array();

    protected \$id;
    protected \$firstName;
    protected \$lastName;
    protected \$email;
    protected \$username;
    protected \$password;
    protected \$typeId;
    protected \$statusId;
    /**
     * @ActiveRecordEnd
     */
}
";

        $userTwoExceptedClass =
"<?php
namespace UnitTest\\Model\\barrage;

use Salvo\\Barrage\\ActiveRecord\\RelationalMapper\\ActiveRecord;

class UserTwo extends ActiveRecord
{
    /**
     * IMPORTANT: Code in-between @ActiveRecordStart and @ActiveRecordEnd will be overwritten when using console to update model class, DON'T modify code
     * in-between these two PHPDoc tags or certain console functionality will not work properly.
     *
     * @ActiveRecordStart
     */
    protected static \$database = 'barrage';
    protected static \$primaryKey = array('id');
    protected static \$autoIncrementedField = 'id';
    protected static \$table = array('name'=>'UsersTwo','alias'=>'usr2');
    protected static \$joins = array();
    protected static \$fields = array('id'=>array('name'=>'id'),'firstName'=>array('name'=>'firstName'),'lastName'=>array('name'=>'lastName'),'email'=>array('name'=>'email'),'username'=>array('name'=>'username'),'password'=>array('name'=>'password'),'typeId'=>array('name'=>'typeId'),'statusId'=>array('name'=>'statusId'));
    protected static \$dataSourceConfiguration = 'default';
    protected static \$skipSaveMembers = array();

    protected \$id;
    protected \$firstName;
    protected \$lastName;
    protected \$email;
    protected \$username;
    protected \$password;
    protected \$typeId;
    protected \$statusId;
    /**
     * @ActiveRecordEnd
     */
}
";

        $statusFileClass = file_get_contents($this->modelDirectory . '/' . 'Status.php');
        $typeFileClass = file_get_contents($this->modelDirectory . '/' . 'Type.php');
        $userFileClass = file_get_contents($this->modelDirectory . '/' . 'User.php');
        $userTwoFileClass = file_get_contents($this->modelDirectory . '/' . 'UserTwo.php');

        $this->assertEquals($statusExceptedClass, $statusFileClass);
        $this->assertEquals($typeExceptedClass, $typeFileClass);
        $this->assertEquals($userExceptedClass, $userFileClass);
        $this->assertEquals($userTwoExceptedClass, $userTwoFileClass);

        $this->deleteUnitTestModelFiles();
    }
}
