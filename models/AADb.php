<?php
/**
 * Translates AutoAdmin's needs to DataBase queries.
 * Abstracts interaction with DB.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AADb
{
	private $_data;
	public $tableName;
	/**
	 *
	 * @var string An alias of CDbConnection that set up in config. It will be used as Yii::app()->{$dbConnection}.
	 */
	public static $dbConnection = 'db';
	/**
	 *
	 * @var string DB schema that contains tables which are operated by a user.
	 */
	public $dbSchema;

	private $_listQuery;

	public function __construct($AAData)
	{
		$this->_data = $AAData;
	}

	/**
	 * Gets the full composite table name for SQL queries using @link AutoAdmin::dbSchema.
	 * @param string Custom table name that you can use instead of @link AutoAdmin::dbSchema.
	 * @return string Full composite table name ready to use in SQL quiries.
	 */
	public function getFullTableName($tableName=null)
	{
		return ($this->dbSchema ? "{$this->dbSchema}." : '').($tableName ? $tableName : $this->tableName);
	}

	/**
	 *Initializes list query. 
	 */
	public function initList()
	{
		$this->_listQuery = $this->getBaseQuery();
	}

	/**
	 * SQL query for output in a common list of data rows.
	 * @return \CDbCommand DAO built command
	 */
	public function getList($rowsOnPage, $offset)
	{
		$qWhere = $this->_listQuery->getWhere() ? array($this->_listQuery->getWhere()) : array();
		$qParams = $this->_listQuery->params ? $this->_listQuery->params : array();
		
		foreach($this->_data->fields as $field)
		{
			if(!is_null($field->bind))
			{
				if($field->bind == 'NULL')
					$qWhere[] = "{$this->tableName}.{$field->name} IS NULL";
				else
				{
					$qWhere[] = "{$this->tableName}.{$field->name} = :_bind_{$field->name}";
					$qParams[":_bind_{$field->name}"] = $field->bind;
				}
			}
		}
		if($qWhere)
			$this->_listQuery->where(array_merge(array('AND'), $qWhere), $qParams);
		if($this->_data->searchOptions)
			$this->addSearch();

		if(!empty($this->_data->orderBy))
		{
			$qOrder = array();
			foreach($this->_data->orderBy as $order)
			{
				if($order['field']->type == 'foreign')
				{
					$selectNames = array_keys($order['field']->options['select']);
					$orderPiece = "{$order['field']->options['tableAlias']}.{$selectNames[0]}";
				}
				else
					$orderPiece = "{$this->tableName}.{$order['field']->name}";
				if($order['dir'] == -1)
					$orderPiece .= " DESC";
				$qOrder[] = $orderPiece;
			}
			if($qOrder)
				$this->_listQuery->order($qOrder);
		}

		$this->_listQuery->limit($rowsOnPage, $offset);

		$rows = $this->_listQuery->queryAll();
		$resultSet = array();
		foreach($rows as $row)
			$resultSet[] = $this->_data->loadRow($row);

		return $resultSet;
	}

	/**
	 * Builds the base part of query for common list or single row of data.
	 * @param \CDbCommand $q DAO built command
	 */
	private function getBaseQuery($strictShowInList=true)
	{
		$q = Yii::app()->{self::$dbConnection}->createCommand();
		$selectFields = array();
		foreach($this->_data->pk as $pkField=>$pkValue)
			$selectFields[] = "{$this->tableName}.{$pkField}";
		foreach($this->_data->fields as $field)
		{
			if($field->showInList || !$strictShowInList)
			{
				$selectFields[] = "{$this->tableName}.{$field->name}";
				//A field can provide its own parts for query: SELECT and JOIN.
				if(method_exists($field, 'modifySqlQuery'))
				{
					$sqlModifing = $field->modifySqlQuery();
					if($sqlModifing)
					{
						if(!empty($sqlModifing['select']))
							$selectFields = array_merge($selectFields, $sqlModifing['select']);
						if(!empty($sqlModifing['join']))
						{
							if(empty($sqlModifing['join']['type']) || $sqlModifing['join']['type']=='inner')
								$joinF = 'join';
							else
								$joinF = strtolower($sqlModifing['join']['type'])."Join";
							$q->{$joinF}($this->getFullTableName($sqlModifing['join']['table']), $sqlModifing['join']['conditions'], (!empty($sqlModifing['join']['params']) ? $sqlModifing['join']['params'] : array()));
						}
					}
				}
			}
		}

		$q->select($selectFields);
		$q->from($this->getFullTableName());
		return $q;
	}

	/**
	 * Calculate overall count of rows in a current list mode.
	 * @return int Count of rows.
	 * @todo Caching of the query. The problem is in updating key (more precisely - in generating of the key in any mode).
	 */
	public function getListOverallCount()
	{
		//$dependency = new CGlobalStateCacheDependency($this->_listQuery->text);
		//$q = Yii::app()->{self::$dbConnection}->cache(600, $dependency)->createCommand();
		$q = Yii::app()->{self::$dbConnection}->createCommand();
		$q->select(new CDbExpression("COUNT(*)"));
		$q->from($this->tableName);
		if($this->_listQuery->join)
			$q->join = $this->_listQuery->join;
		if($this->_listQuery->where)
			$q->where = $this->_listQuery->where;
		if($this->_listQuery->params)
			$q->params = $this->_listQuery->params;
		return $q->queryScalar();
	}

	/**
	 * Loads data from linked foreign tables as "many to many".
	 * @param array $listData Data from native table as array of AADataRow rows, foreign data extract by.
	 */
	public function getForeignData(&$listData)
	{
		$data = array();

		$nativeKeys = array();
		foreach($listData as $row)
		{
			foreach($row->pk as $pkField=>$pkValue)
			{
				if(!isset($nativeKeys[$pkField]))
					$nativeKeys[$pkField] = array();
				$nativeKeys[$pkField] = $pkValue;
			}
		}
		if(!$nativeKeys)
			return $data;

		$q = Yii::app()->{self::$dbConnection}->createCommand();
		foreach($this->_data->foreignLinks as $outAlias=>$link)
		{
			$q->from($this->getFullTableName($link['linkTable'])." AS t1, ".$this->getFullTableName($link['targetTable'])." AS t2");

			//Collect fields for selection
			$fields = array();
			foreach($link['inKey'] as $inKey=>$nativeKey)
				$fields[] = "t1.{$inKey}";
			if(!empty($link['linkFields']))
			{
				foreach($link['linkFields'] as $field)
					$fields[] = "t1.{$field}";
			}
			if(!empty($link['targetFields']))
			{
				foreach($link['targetFields'] as $field)
					$fields[] = "t2.{$field}";
			}
			$q->select($fields);

			$where = array('AND');
			$whereJoin =& $where[array_push($where, array('AND')) - 1];	//INNER JOIN conditions must have a the separate group
			foreach($link['outKey'] as $outKey=>$targetKey)
				$whereJoin[] = "{$outKey} = {$targetKey}";	//INNER JOIN conditions
			foreach($link['inKey'] as $inKey=>$nativeKey)
				$where[] = array('IN', $inKey, $nativeKeys[$nativeKey]);
			$q->where($where);

			$result = $q->queryAll();
			$data[$outAlias] = array();	//result data
			foreach($result as $r)
			{
				$exportKey = array();
				foreach($this->_data->pk as $pkField=>$pkValue)
					$exportKey[] = $r[array_search($pkField, $link['inKey'])];
				$exportKey = serialize($exportKey);
				if(!isset($data[$outAlias][$exportKey]))
					$data[$outAlias][$exportKey] = array();
				$data[$outAlias][$exportKey][] = $r;
			}
			$q->reset();
		}
		return $data;
	}

	/**
	 * Update conditions of Yii DAO standard.
	 * You don't know the alias of a table when you programme it. So you ought to use original name as prefix (e.g. "table_name.field_name = :param"). You may not use prefix only if you're sure that the field name is a unique one.
	 * @param mixed $condition Yii DAO standart conditions.
	 * @param string $tableName Original table name (function will replace by "{$tableName}.").
	 * @param string $tableAlias New alias instead of $tableName.
	 */
	public static function addTableAliasToCond(&$condition, $tableName, $tableAlias)
	{
		if(is_array($condition))
		{
			foreach($condition as &$cond)
			{
				self::addTableAliasToCond($cond, $tableName, $tableAlias);
			}
		}
		elseif(is_string($condition))
		{
			$condition = str_replace("{$tableName}.", "{$tableAlias}.", $condition);
		}
	}

	/**
	 * Adds a search condition to the query.
	 */
	public function addSearch()
	{
		if($this->_data->searchOptions['field']->type == 'foreign' && $this->_data->searchOptions['field']->options['select'])
		{
			$like = array('OR');
			foreach($this->_data->searchOptions['field']->options['select'] as $fieldName=>$fieldAlias)
			{
				if(is_array($this->_data->searchOptions['query']))
				{
					foreach($this->_data->searchOptions['query'] as $i=>$term)
						$like[] = array('LIKE', "{$this->_data->searchOptions['field']->options['tableAlias']}.{$fieldName}", self::metaToLike($term));
				}
				else
					$like = array('LIKE', "{$this->_data->searchOptions['field']->options['tableAlias']}.{$fieldName}", self::metaToLike($this->_data->searchOptions['query']));
			}
		}
		else
		{
			if(is_array($this->_data->searchOptions['query']))
			{
				$like = array('OR');
				foreach($this->_data->searchOptions['query'] as $i=>$term)
					$like[] = array('LIKE', "{$this->_data->searchOptions['field']->tableName}.{$this->_data->searchOptions['field']->name}", self::metaToLike($term));
			}
			else
				$like = array('LIKE', "{$this->_data->searchOptions['field']->tableName}.{$this->_data->searchOptions['field']->name}", self::metaToLike($this->_data->searchOptions['query']));
		}
		$where = ($this->_listQuery->getWhere() ? array('AND', $this->_listQuery->getWhere(), $like) : $like);
		$this->_listQuery->where($where, $this->_listQuery->params);
	}

	/**
	 * Gets the the data row by initialized (in AAData) with GET-params primary keys.
	 * @return AADataRow
	 */
	public function getCurrentRow()
	{
		$q = $this->getBaseQuery(false);
		$qWhere = $q->getWhere() ? array($q->getWhere()) : array();
		$qParams = $q->params ? $q->params : array();

		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$qWhere[] = "{$this->tableName}.{$pkField} = :id_{$pkField}";
			$qParams[":id_{$pkField}"] = $pkValue;
		}
		foreach($this->_data->fields as $field)
		{
			if($field->bind)
			{
				$qWhere[] = "{$this->tableName}.{$field->name} = :bk_{$field->name}";
				$qParams[":bk_{$field->name}"] = $field->bind;
			}
		}
		if($qWhere)
			$q->where(array_merge(array('AND'), $qWhere), $qParams);
		$r = $q->queryRow();
		return $r ? $this->_data->loadRow($r) : false;
	}

	/**
	 * Inserts the data.
	 * @param array $values An array of field=>value to insert.
	 */
	public function insert($values)
	{
		return Yii::app()->{self::$dbConnection}->createCommand()
				->insert($this->getFullTableName(), $values);
	}

	/**
	 * Updates the data.
	 * @param array $values An array of field=>value to update.
	 */
	public function update($values)
	{
		$params = array();
		$where = array('AND');
		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$where[] = "{$this->tableName}.{$pkField} = :_id{$pkField}";
			$params[":_id{$pkField}"] = $pkValue;
		}
		return Yii::app()->{self::$dbConnection}->createCommand()
				->update($this->getFullTableName(), $values, $where, $params);
	}

	/**
	 * Deletes a row from the table.
	 * @return int Affected rows.
	 */
	public function delete()
	{
		$params = array();
		$where = array('AND');
		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$where[] = "{$this->tableName}.{$pkField} = :_id{$pkField}";
			$params[":_id{$pkField}"] = $pkValue;
		}
		return Yii::app()->{self::$dbConnection}->createCommand()
				->delete($this->getFullTableName(), $where, $params);
	}

	/**
	 * Gets the last inserted sequence (primary key).
	 * @param array $values Just inserted values. Used for composite primary keys.
	 * @return mixed|array LastInsertedID value if the PrimaryKey is scalar, and array of inserted values if PK is composite.
	 */
	public function getInsertedPKs(&$values)
	{
		$tableSchema = Yii::app()->{self::$dbConnection}->schema->getTable($this->tableName);
		if(count($this->_data->pk) == 1)	//Can use a sequence (AutoIncrement)
			$pk = array($this->_data->pk[$this->_data->getPKField(0)] => Yii::app()->{self::$dbConnection}->getLastInsertID(($tableSchema->sequenceName ? $tableSchema->sequenceName : null)));
		else
			$pk = $this->_data->rowPK($values);
	}

	/**
	 * Begins a transaction.
	 * @return \CDbTransaction Yii DB Transaction object.
	 */
	public function beginTransaction()
	{
		return Yii::app()->{self::$dbConnection}->beginTransaction();
	}

	/**
	 * Commits the transaction.
	 * @param \CDbTransaction Yii DB Transaction object.
	 */
	public function transactionCommit(&$transaction)
	{
		$transaction->commit();
	}

	/**
	 * Rollbacks the transaction.
	 * @param \CDbTransaction Yii DB Transaction object.
	 */
	public function transactionRollback(&$transaction)
	{
		$transaction->rollBack();
	}

	/**
	 * Converts a string into SQL "LIKE" term using meta-symbols inside it.
	 * The logic:
	 *  1. If you enter simple string $s, SQL search LIKE-condition will be "LIKE $s%" - by beginning of fields.
	 *  2. If you enter "*" among symbols in $s then also "LIKE $s%", but your "*" will be replaced with SQL-analogs "%".
	 *  3. If you enter "$" in the end of $s then (and only then) SQL-meta "%" will not be added to "LIKE $s". But "*" will be replaced as it should be done with case 2.
	 * @param string $term String to search by, with meta-symbols.
	 */
	public static function metaToLike($term)
	{
		$term = str_replace('%', '\%', $term);
		if(false !== mb_strpos($term, '*'))
			$term = str_replace('*', '%', $term);
		if(mb_substr($term, -1) == '$')
			$term = mb_substr($term, 0, -1);
		elseif(mb_substr($term, -1) != '%')
			$term = $term.'%';

		return $term;
	}
}
