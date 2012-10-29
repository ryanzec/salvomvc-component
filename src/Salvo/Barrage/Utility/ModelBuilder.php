<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\Utility;

use Salvo\Barrage\DataSource\Relational\DataSourceFactory;
use Salvo\Barrage\Configuration;
use Salvo\Utility\RegexHelper;

/**
 * Helper class for generating model files for active record
 */
class ModelBuilder
{
	/**
	 * @var string
	 */
	private static $tokenWrapper = '%%';

	/**
	 * Token in template actually has the token wrapper before and after the token listed here
	 *
	 * @var array
	 */
	private static $templateTokens = array
	(
		'full' => array
		(
			'namespace',
			'class'
		),
		'active_record' => array
		(
			'database',
			'primaryKeyArray',
			'autoIncrementField',
			'tableArray',
			'joinsArray',
			'fieldsArray',
			'dataSourceConfiguration',
			'skipSaveMembersArray',
			'fields'
		)
	);

	/**
	 * @var string
	 */
	private static $fullTemplate =
"<?php
namespace %%namespace%%;

use Salvo\\Barrage\\ActiveRecord\\RelationalMapper\\ActiveRecord;

class %%class%% extends ActiveRecord
{
	/**
	 * IMPORTANT: Code in-between @ActiveRecordStart and @ActiveRecordEnd will be overwritten when using console to update model class, DON'T modify code
	 * in-between these two PHPDoc tags or certain console functionality will not work properly.
	 *
	 * @ActiveRecordStart
	 */
	protected static \$database = '%%database%%';
	protected static \$primaryKey = %%primaryKeyArray%%;
	protected static \$autoIncrementedField = '%%autoIncrementField%%';
	protected static \$table = %%tableArray%%;
	protected static \$joins = %%joinsArray%%;
	protected static \$fields = %%fieldsArray%%;
	protected static \$dataSourceConfiguration = '%%dataSourceConfiguration%%';
	protected static \$skipSaveMembers = %%skipSaveMembersArray%%;%%fields%%
	/**
	 * @ActiveRecordEnd
	 */
}
";

	/**
	 * @var string
	 */
	private static $activeRecordTemplate =
"    protected static \$database = '%%database%%';
	protected static \$primaryKey = %%primaryKeyArray%%;
	protected static \$autoIncrementedField = '%%autoIncrementField%%';
	protected static \$table = %%tableArray%%;
	protected static \$joins = %%joinsArray%%;
	protected static \$fields = %%fieldsArray%%;
	protected static \$dataSourceConfiguration = '%%dataSourceConfiguration%%';
	protected static \$skipSaveMembers = %%skipSaveMembersArray%%;%%fields%%";

	/**
	 * Generates the php code for a model file
	 *
	 * @static
	 *
	 * @param $database
	 * @param $table
	 * @param $class
	 * @param $namespace
	 * @return mixed
	 */
	public static function buildModelClass($database, $table, $class, $namespace)
	{
		$array = self::buildSearchReplaceArray('full', $database, $table, $class, $namespace);
		return str_replace(array_keys($array), array_values($array), self::$fullTemplate);
	}

	/**
	 * Updates the php code for a model file
	 *
	 * @static
	 *
	 * @param $filePath
	 * @param $database
	 * @param $table
	 * @param $class
	 * @param $namespace
	 * @return mixed
	 */
	public static function updateModelClass($filePath, $database, $table, $class, $namespace)
	{
		$array = self::buildSearchReplaceArray('active_record', $database, $table, $class, $namespace);
		$replace = array();
		$replace['ActiveRecord'] = explode("\n", str_replace(array_keys($array), array_values($array), self::$activeRecordTemplate));
		return self::updateCodeByDocBlocks($filePath, $replace);
	}

	/**
	 * Create an array will the valid items for search and replacing with templates
	 *
	 * @static
	 *
	 * @param $type
	 * @param $database
	 * @param $table
	 * @param $class
	 * @param $namespace
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	private static function buildSearchReplaceArray($type, $database, $table, $class, $namespace)
	{
		$database = Configuration::getTrueDatabaseName($database);
		$builderConfiguration = Configuration::getOption('model_builder');
		$tableConfiguration = $builderConfiguration['relational']['databases'][$database]['tables'];
		$tableAlias = (!empty($tableConfiguration[$table]['alias'])) ? $tableConfiguration[$table]['alias'] : $table;
		$dataSource = DataSourceFactory::buildFromConfiguration('default');
		$parsingTokens = array();
		$stringReplaceArray = array();

		if($type == 'full')
		{
			foreach(self::$templateTokens as $tokens)
			{
				$parsingTokens = array_merge($parsingTokens, $tokens);
			}
		}
		else if(array_key_exists($type, self::$templateTokens))
		{
			$parsingTokens = self::$templateTokens[$type];
		}
		else
		{
			throw new \Exception("Invalid type ({$type}) for building token list");
		}

		$autoIncrementField = null;
		$primaryKeyArray = array();
		$tableArray = array
		(
			'name' => $table,
			'alias' => $tableAlias
		);

		$fieldsArray = array();
		$skipSaveMembersArray = (!empty($tableConfiguration[$table]['skip_save_members'])) ? $tableConfiguration[$table]['skip_save_members'] : array();

		$tableFieldDetails = $dataSource->getTableFieldsDetails($table, $database);

		foreach($tableFieldDetails as $fieldDetails)
		{
			if($fieldDetails['auto_increment'] === true)
			{
				$autoIncrementField = $fieldDetails['field'];
			}

			if($fieldDetails['key_type'] == 'primary')
			{
				$primaryKeyArray[] = $fieldDetails['field'];
			}

			$memberNameParts = explode('_', RegexHelper::toUnderscore($fieldDetails['field']));
			$memberName = '';

			foreach($memberNameParts as $part)
			{
				$memberName .= ucwords(strtolower($part));
			}

			$memberName = lcfirst($memberName);

			$fieldsArray[$memberName] = array
			(
				'name' => $fieldDetails['field']
			);

			if(in_array($fieldDetails['field_type'], array('enum', 'set')))
			{
				$fieldsArray[$memberName]['values'] = $dataSource->getFieldValues($table, $fieldDetails['field'], $database);
			}
		}

		//add in join fields
		if(!empty($tableConfiguration[$table]['join_fields']))
		{
			foreach($tableConfiguration[$table]['join_fields'] as $joinTable => $fields)
			{
				foreach($fields as $options)
				{
					$memberName = (!empty($options['member_name'])) ? $options['member_name'] : $options['field'];
					$field = $options['field'];
					$as = $options['field'];

					if(!empty($options['as']))
					{
						$as = $options['as'];
					}
					else if(!empty($options['member_name']))
					{
						$as = $options['member_name'];
					}

					$joinFieldData = array
					(
						'name' => $field,
						'join_table' => $joinTable
					);

					if($as != $options['field'])
					{
						$joinFieldData['name'] .= ' AS ' . $as;
					}

					$fieldsArray[$memberName] = $joinFieldData;
				}
			}
		}

		foreach($parsingTokens as $token)
		{
			$trueValue = null;

			switch($token)
			{
				case 'namespace':
					$trueValue = $namespace;
					break;

				case 'class':
					$trueValue = $class;
					break;

				case 'database':
					$trueValue = $database;
					break;

				case 'primaryKeyArray':
					$trueValue = self::arrayToPhpCodeString($primaryKeyArray);
					break;

				case 'autoIncrementField':
					$trueValue = $autoIncrementField;
					break;

				case 'tableArray':
					$trueValue = self::arrayToPhpCodeString($tableArray);
					break;

				case 'joinsArray':
					$joinsArray = array();

					if(!empty($tableConfiguration[$table]['joins']))
					{
						foreach($tableConfiguration[$table]['joins'] as $joinTable => $config)
						{
							$temp['alias'] = (!empty($tableConfiguration[$joinTable]['alias'])) ? $tableConfiguration[$joinTable]['alias'] : $joinTable;
							$temp['type'] = (!empty($config['type'])) ? $config['type'] : 'inner';
							$temp['on'] = "`{$temp['alias']}`.`{$config['join_field']}` = `{$tableAlias}`.`{$config['field']}`";

							if(!empty($config['database']))
							{
								$temp['database'] = $config['database'];
							}

							$joinsArray[$joinTable] = $temp;
						}
					}

					$trueValue = self::arrayToPhpCodeString($joinsArray);
					break;

				case 'fieldsArray':
					$trueValue = self::arrayToPhpCodeString($fieldsArray);
					break;

				case 'dataSourceConfiguration':
					$trueValue = 'default';
					break;

				case 'skipSaveMembersArray':
					$trueValue = self::arrayToPhpCodeString($skipSaveMembersArray);
					break;

				case 'fields':
					$trueValue = '';

					foreach($fieldsArray as $member => $options)
					{
						if(!empty($trueValue))
						{
							$trueValue .= "\n";
						}
						else
						{
							$trueValue .= "\n\n";
						}

						$trueValue .= "    protected \${$member};";
					}
					break;

				default:
					throw new \Exception("Token passed ({$token}) is not a valid token");
					break;
			}

			$stringReplaceArray[self::$tokenWrapper . $token . self::$tokenWrapper] = $trueValue;
		}

		return $stringReplaceArray;
	}

	/**
	 * Convert an array into a PHP string that can be places as code
	 *
	 * @static
	 *
	 * @param $array
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	private static function arrayToPhpCodeString($array)
	{
		if(!is_array($array))
		{
			throw new \Exception("Array must be given to convert an array to a php code string");
		}

		if(empty($array))
		{
			return 'array()';
		}

		$string = 'array(';
		$keyCount = 0;

		foreach($array as $key => $value)
		{
			if($keyCount > 0)
			{
				$string .= ',';
			}

			if(!is_numeric($key))
			{
				$string .= "'{$key}'=>";
			}

			if(is_array($value))
			{
				$string .= self::arrayToPhpCodeString($value);
			}
			else if(is_bool($value))
			{
				$string .= ($value) ? 'true' : 'false';
			}
			else
			{
				$string .= (is_numeric($value)) ? $value : "'{$value}'";
			}

			$keyCount++;
		}

		return $string . ')';
	}

	/**
	 * This will take a file and allow you to be able to replace code within any doc blocks that have @[DocBlockName]Start and @[DocBlockName]End
	 *
	 * @static
	 *
	 * @param $filePath
	 * @param $replaceData
	 *
	 * @return string
	 */
	private static function updateCodeByDocBlocks($filePath, $replaceData)
	{

		$switch = false;
		$lines = file($filePath);
		$code = '';
		$replaceTextAdded = false;

		foreach($replaceData as $docBlock => $data)
		{
			$replaceCount = 0;
			foreach($lines as $k => $v)
			{
				if(preg_match('/@(.*)' . $docBlock . 'End$/', $v))
				{
					$switch = false;
				}

				if($switch == true && substr(trim($v), 0, 1) != '*' && substr(trim($v), 0, 2) != '/*')
				{
					if(!$replaceTextAdded)
					{
						$code .= '%%%replaceme%%%';
						$replaceTextAdded = true;
					}
				}
				else
				{
					$code .= $v;
				}

				if(preg_match('/@(.*)' . $docBlock . 'Start$/', $v))
				{
					$switch = true;
				}
			}
		}

		$replaceText = null;

		foreach($replaceData['ActiveRecord'] as $data)
		{
			$replaceText .= $data . "\n";
		}

		$code = str_replace('%%%replaceme%%%', $replaceText, $code);

		return $code;
	}
}
