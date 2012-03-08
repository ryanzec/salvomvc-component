<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\Console\Command\ActiveRecord\Relational;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console;
use Salvo\Barrage\DataSource\Relational\DataSourceFactory;
use Salvo\Barrage\Configuration;

/**
 * Model builder console command
 */
class ModelBuilder extends Console\Command\Command
{
    /**
     * Configurations for the command
     */
    public function configure()
    {
        $definition = array
        (
            new InputArgument('table', InputArgument::OPTIONAL, 'Table to build model for'),
            new InputOption('class', null, null, 'The name of the class'),
            new InputOption('namespace', null, null, 'The name of the namespace'),
            new InputOption('default', 'd', null, 'Automatically selected default values'),
            new InputOption('database', null, null, 'The name of the data the table is in')

        );

        $this->setName('relational:model_builder')
             ->setDescription('Creates/Updates model files based on database table')
             ->setDefinition($definition)
             ->setHelp("\nCreates/Updates model files based on database structure\n");
    }

    /**
     * The implementation of what the command should do when executed
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $builderConfiguration = Configuration::getOption('model_builder');
        $basePath = $builderConfiguration['base_source_path'];

        $basePath = (substr($basePath, 0, 1) === DIRECTORY_SEPARATOR) ? $basePath : __DIR__ . DIRECTORY_SEPARATOR . $basePath;

        /*echo "\n\n";
        var_dump($basePath);
        echo "\n\n";
        exit();*/

        $defaultNamespace = $builderConfiguration['relational']['default_namespace'];
        $dataSource = DataSourceFactory::buildFromConfiguration('default');
        $options = $input->getOptions();
        $databases = ($options['database']) ? array($options['database']) : array();

        if(empty($databases))
        {
            foreach($builderConfiguration['relational']['databases'] as $databaseName => $databaseOptions)
            {
                $databases[] = $databaseName;
            }
        }

        foreach($databases as $database)
        {
            $tableConfiguration = $builderConfiguration['relational']['databases'][$database]['tables'];

            $table = $input->getArgument('table');

            if(!empty($table))
            {
                $tables = array($input->getArgument('table'));
            }
            else
            {
                $tables = $dataSource->getTables($database);
            }

            $dialog = $this->getHelperSet()->get('dialog');

            $output->writeln('');
            $output->writeln("Writing models for {$database} database:");

            foreach($tables as $table)
            {
                if(isset($tableConfiguration[$table]))
                {
                    $classDefault = (!empty($tableConfiguration[$table]['class_name'])) ? $tableConfiguration[$table]['class_name'] : null;
                    $class = ($options['default'] === true) ? $classDefault : $options['class'];

                    if(!empty($class))
                    {
                        $namespace = ($options['default'] === true) ? $builderConfiguration['relational']['default_namespace'] : $options['namespace'];
                        $namespace .= "\\{$database}";

                        while(empty($namespace))
                        {
                            if($namespace === null)
                            {
                                $output->writeln('You must specify a namespace for the class');
                            }

                            $message = "What would you like the namespace name for class {$class} to be";

                            if(!empty($defaultNamespace))
                            {
                                $message .= ' [' . $defaultNamespace . ']';
                            }

                            $namespace = $dialog->ask($output, "{$message}? ");

                            if(!empty($defaultNamespace) && empty($namespace))
                            {
                                $namespace = $defaultNamespace;
                            }
                        }

                        if(empty($database))
                        {
                            $output->writeln("ERROR: Can't build model without database being specified int he command of in the connection\\s configuration");
                            return;
                        }

                        $namespaceParts = explode('\\', $namespace);

                        $extraFilePath = implode('/', $namespaceParts);
                        $filePath = "{$basePath}/{$extraFilePath}/{$class}.php";

                        //make sure that directory exists
                        if(!is_dir("{$basePath}/{$extraFilePath}"))
                        {
                            var_dump("{$basePath}/{$extraFilePath}");
                            die();
                            mkdir("{$basePath}/{$extraFilePath}");
                        }

                        if(file_exists($filePath))
                        {
                            $phpCode = \Salvo\Barrage\Utility\ModelBuilder::updateModelClass($filePath, $database, $table, $class, $namespace);
                        }
                        else
                        {
                            $phpCode = \Salvo\Barrage\Utility\ModelBuilder::buildModelClass($database, $table, $class, $namespace);
                        }

                        $this->writeFile($filePath, $phpCode);
                        $output->writeln("Writing model for the {$table} table as class {$class} to {$filePath}");
                    }
                }
            }

            $output->writeln('');
        }
    }

    /**
     * Completely overwrites a file with the passed data.
     *
     * @param $path
     * @param $data
     * @param string $mode
     */
    private function writeFile($path, $data, $mode = 'w+')
    {
        $fileHandle = fopen($path, $mode);
        fwrite($fileHandle, $data);
        fclose($fileHandle);
    }
}
