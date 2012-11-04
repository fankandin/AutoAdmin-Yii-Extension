<?php
/**
 * AAGenerator busines logic.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAGenerator
{
	public $dbSchema;

	public function __construct($schema=null)
	{
		if($schema)
			$this->dbSchema = $schema;
	}

	public function getTableNames()
	{
		return Yii::app()->db->schema->getTableNames(($this->dbSchema ? $this->dbSchema : ''));
	}

	public function getTables()
	{
		return Yii::app()->db->schema->getTables(($this->dbSchema ? $this->dbSchema : ''));
	}

	public static function getTable($table)
	{
		return Yii::app()->db->schema->getTable($table, true);
	}

	public function generate($table)
	{
		$constructor = new AAGeneratorConstructor(self::getTable(($this->dbSchema ? "{$this->dbSchema}." : '').$table));
		$constructor->loadBase();
		$constructor->loadDefaultColumns();
		$constructor->loadDefaultSorting();
		return $constructor;
	}
}
