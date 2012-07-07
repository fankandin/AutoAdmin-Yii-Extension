<?php
/**
 * Description of AABreadcrumbsQuery
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AABreadcrumbsQuery
{
	public $connection;
	public $command;

	public function __construct($connection)
	{
		$this->connection = $connection;
		$this->command = $this->connection->createCommand();
	}

	/**
	 * Adds the from-clause.
	 * @param mixed $from The table to be selected from.
	 * @return \AABreadcrumbsQuery
	 */
	public function from($from)
	{
		$this->command->from($from);
		return $this;
	}

	/**
	 * Adds the select-clause.
	 * @param string $selectField The field to select as title.
	 * @return \AABreadcrumbsQuery
	 */
	public function select($selectField)
	{
		$this->command->select($selectField);
		return $this;
	}

	/**
	 * Adds the where-clause.
	 * @param mixed $conditions
	 * @param array $params
	 * @return \AABreadcrumbsQuery 
	 */
	public function where($conditions, $params=null)
	{
		$this->command->where($conditions, ($params ? $params : null));
		return $this;
	}

}
