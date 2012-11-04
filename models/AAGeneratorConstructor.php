<?php
/**
 * Constructor of AutoAdmin interfaces.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAGeneratorConstructor
{
	/**
	 *
	 * @var string Table service data
	 */
	protected $_table;
	/**
	 *
	 * @var string Table name
	 */
	public $tableName;
	/**
	 *
	 * @var string|array Primary Key 
	 */
	public $primaryKey;
	/**
	 *
	 * @var array Sorting settings
	 */
	public $sorting;
	/**
	 *
	 * @var array Fields configurations
	 */
	public $fieldsConf;
	/**
	 *
	 * @var string Interface (Yii action) title
	 */
	public $title;
	/**
	 *
	 * @var string Yii action name
	 */
	public $actionName;

	/**
	 *
	 * @param mixed $tableData Table service data loaded through Yii::CDbSchema component
	 */
	public function __construct($tableData)
	{
		$this->_table = $tableData;
	}

	/**
	 * Loads obligatory (for generated interface) base parameters.
	 */
	public function loadBase()
	{
		$this->tableName = $this->_table->name;
		$this->primaryKey = $this->_table->primaryKey;
		$this->actionName = $this->loadDefaultActionName();
		$this->title = $this->actionName;
	}

	/**
	 * Generates default (befor user's editing) columns settings
	 */
	public function loadDefaultColumns()
	{
		$this->fieldsConf = array();
		$fieldRowI = 0;
		foreach($this->_table->columns as $columnName=>$column)
		{
			if($column->isPrimaryKey)
				continue;
			$fieldRowI++;
			$field = array($columnName, 'string', ucfirst(str_replace('_', ' ', $columnName)), array());	//default type is string
			$ftype =& $field[1];
			$foptions =& $field[3];
			if($column->allowNull)
				$foptions[] = 'null';
			if($fieldRowI <= 4)	//in the List mode show the first 4 columns
				$foptions[] = 'show';
			if(!is_null($column->defaultValue))
				$foptions['default'] = $column->defaultValue;
			if($column->isForeignKey)
			{
				if(isset($this->_table->foreignKeys[$column->name]))
				{	//we can find out the ForeignKey data
					$ftype = 'foreign';
					$fkey =& $this->_table->foreignKeys[$column->name];
					$foptions['foreign'] = array(
						'table'=>$fkey[0],
						'pk'=>$fkey[1],
						'select'=>array(),
					);
					$foreignTableData = AAGenerator::getTable($fkey[0]);
					$j = 0;
					foreach($foreignTableData->columns as $fColumnName=>$fColumn)
					{
						if($j <= 1 && !$fColumn->isPrimaryKey && !$fColumn->isForeignKey)
							$foptions['foreign']['select'][$j++] = $fColumnName;
					}
				}
			}
			else
			{
				if(preg_match('/char/i', $column->dbType))
				{
					$ftype = 'string';
					$foptions['maxlength'] = $column->size;
				}
				elseif(preg_match('/(int)|(numeric)|(decimal)|(double)|(float)/i', $column->dbType))
				{	//numeric
					$ftype = 'num';
				}
				elseif(preg_match('/text/i', $column->dbType))
				{
					$ftype = 'tinytext';
				}
				elseif(preg_match('/^date$/i', $column->dbType))
				{
					$ftype = 'date';
				}
				elseif(preg_match('/^time$/i', $column->dbType))
				{
					$ftype = 'time';
				}
				elseif(preg_match('/(timestamp)|(datetime)/i', $column->dbType))
				{
					$ftype = 'datetime';
				}
				elseif(preg_match('/enum\(([^\)]*)\)/i', $column->dbType, $matches))
				{
					$ftype = 'enum';
					$row = trim($matches[1], "'");
					$foptions['enum'] = array();
					if($row)
					{
						$values = explode("','", $row);
						if($values)
							$foptions['enum'] = array_combine($values, $values);
					}
				}
				elseif(preg_match('/boolean/', $column->dbType))
				{
					$ftype = 'boolean';
					$foptions['default'] = (bool)$column->defaultValue;
				}
			}
			$this->fieldsConf[] = $field;
		}
	}

	/**
	 * Generates default sorting settings.
	 * @return void 
	 */
	public function loadDefaultSorting()
	{
		$this->sorting = array();
		foreach($this->_table->columns as $columnName=>$column)
		{
			if(!$column->isPrimaryKey && !$column->isForeignKey 
					&& preg_match('/(timestamp)|(date)/i', $column->dbType)
			)
			{
				$this->sorting[$columnName] = -1;
				return;
			}
		}
		foreach($this->_table->columns as $columnName=>$column)
		{
			if(!$column->isPrimaryKey)
			{
				$this->sorting = array($columnName=>1);
				return;
			}
		}
	}

	/**
	 * Generates a default Yii action name.
	 * @return string Yii action name
	 */
	public function loadDefaultActionName()
	{
		$action = preg_replace_callback('/_([a-z])/', function($matches) {
				return ucfirst(strtolower($matches[1]));
			}, ucfirst($this->tableName));
		$action = preg_replace('/[^a-z\d]/i', '', $action);
		return $action;
	}

	/**
	 * Generates formatted PHP code which is adopted for using in AutoAdmin controllers.
	 * @param mixed $var Data to export
	 * @return string Formatted string like var_export() but specifically formatted.
	 */
	protected static function exportToCode($var)
	{
		if(is_array($var))
		{
			$level = 0;
			$exportRow = function($row) use(&$exportRow, &$level)
			{
				$s = '';
				$isCanonical = null;
				foreach($row as $key=>$value)
				{
					if(is_int($key))
					{
						if(is_null($isCanonical))
							$isCanonical = ($key==0);
						if(!$isCanonical)
							$s .= "{$key}=>";
					}
					else
						$s .= "'{$key}'=>";
					if(is_numeric($value))
						$s .= "{$value}";
					elseif(is_bool($value))
						$s .= $value ? 'true' : 'false';
					elseif(!is_array($value))
						$s .= "'{$value}'";
					else
					{	//is array
						$level++;
						if($level==1)
							$s .= "\n\t\t\tarray(";
						elseif($level==2)
							$s .= "array(";
						elseif($level>=3)
							$s .= "array(\n\t\t".str_repeat("\t", $level);
						$s .= "".$exportRow($value);
						if($level>=3)
							$s .= "\n\t".str_repeat("\t", $level).")";
						else
							$s .= ")";
						$level--;
					}
					$s .= ', ';
				}
				$s = str_replace(')))', "))\n\t\t\t)", $s);
				$s = preg_replace('/array\(\s+\)/s', 'array()', $s);
				$s = preg_replace('/\,\s$/s', '', $s);
				return $s;
			};
			$s = "array(".$exportRow($var).")";
		}
		else
		{
			$s = var_export($var, true);
		}
		return $s;
	}

	/**
	 * Magic method to convert all loaded and generated data into formatted PHP source code to use in AutoAdmin controllers.
	 * @return string Code of an Yii action method generated from the class.
	 */
	public function __toString()
	{
		$s = <<< 'action'
	public function action%s()
	{
		$this->module->tableName('%s');
		$this->module->setPK(%s);
		$this->module->fieldsConf(%s);
		$this->module->sortDefault(%s);

		$this->pageTitle = '%s';
		$this->module->process();
	}

action;
		$s = sprintf($s,
				$this->actionName,
				$this->tableName,
				self::exportToCode($this->primaryKey),
				preg_replace('/\)$/', "\n\t\t)", self::exportToCode($this->fieldsConf)),
				self::exportToCode($this->sorting),
				$this->title
		);
		return $s;
	}
}
