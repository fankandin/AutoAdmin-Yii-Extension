<?php
/**
 * Keeps and operates by the fields configuration.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAData
{
	public $fields = array();

	public $orderBy = array();
	public $searchOptions = array();

	/**
	 *
	 * @var array PrimaryKeys. Format: [pkName]=>[pkValue|null]
	 */
	public $pk;
	/**
	 * 
	 * @var array An array of binding params to select data by them.
	 */
	public $binding;
	/**
	 *
	 * @var array For operating with foreign keys, which are linked through a table of links.
	 */
	public $foreignLinks = array();

	/**
	 * To cache fields' indexes of $this->fields by name.
	 * @var type 
	 */
	private $cacheFieldsIndexes = array();

	/**
	 * Loads columns configuration.
	 * @param array $columns
	 * @param string $tableName
	 * @throws AAException 
	 */
	public function loadColumnsConf($columns, $tableName)
	{
		foreach($columns as $k=>$column)
		{
			$fieldType = $column[1];
			$fieldClass = "AAField".ucfirst(strtolower($fieldType));
			if(!class_exists($fieldClass))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Unknown field type for column {column}', array('{column}'=>$column[2])));
			$this->fields[$k] = new $fieldClass;
			$field =& $this->fields[$k];

			$field->tableName = $tableName;
			$field->name = $column[0];
			$field->label = $column[2];

			$options = $column[3];
			if(in_array('null', $options, true))
				$field->allowNull = true;
			if(in_array('readonly', $options, true))
				$field->isReadonly = true;
			if(isset($options['default']))
			{
				if(preg_match('/^\[(.+?)\]$/', $options['default'], $matches))
					$field->defaultValue = new CDbExpression($matches[1]);
				else
					$field->defaultValue = $options['default'];
			}
			if(in_array('show', $options, true))
				$field->showInList = true;
			elseif(isset($options['show']))
				$field->showInList = (int)$options['show'];

			if(isset($options['description']))
				$field->description = $options['description'];
			elseif(isset($options['desc']))	//An alias
				$field->description = $options['desc'];

			if(in_array('search', $options, true))
				$field->options['inSearch'] = true;

			//Binding settings
			if(!empty($options['bind']))
				$field->bind = $options['bind'];
			elseif(isset($options['bindBy']))	//'bindBy' has a lesser priority than 'bind' does
			{
				if(isset($this->binding[$options['bindBy']]))
					$field->bind = $this->binding[$options['bindBy']];
			}
			if($field->bind == '[NULL]')
				$field->bind = new CDbExpression('NULL');
			if(!is_null($field->bind))
				$field->value = $field->defaultValue = $field->bind;

			//You can use custom options
			foreach($options as $optName=>$optValue)
			{
				if(is_string($optName) && !isset($field->options[$optName]) && !in_array($optName, array('bind', 'bindBy', 'show', 'search', 'description', 'null', 'default')))
					$field->options[$optName] = $optValue;
			}

			$field->completeOptions();	//Set default options if they haven't been set by user
			if(!$field->testOptions())	//Testing the configuration
				throw new AAException(Yii::t('AutoAdmin.errors', 'Incorrect options configuration of the field {fieldName}', array('{fieldName}'=>$field->name)));
		}
	}

	/**
	 * Ordering or reordering the associative massive $this->orderBy that will be used in ORDER BY query in accordance with.
	 * Function can be used to add new fields to order by instead of old ones or just as new ones.
	 * @param array $orderBy An array of fields ordered by sorting priority. Descending direction of the sorting can be set as value, either "-1" or "DESC" (or "desc"). The fields can be set as keys, or values if without keys.
	 * <pre>
	 * $a->sortDefault(array('title', 'url'));	//Fields will be used in a query as "ORDER BY title ASC, url ASC";
	 * $a->sortDefault(array('title', 'url'=>-1, 'when_added'=>'desc'));	//Fields will be used in a query as "ORDER BY title ASC, url DESC, when_added DESC";
	 * </pre>
	 *  @throws CException 
	 */
	public function setSortOrder($orderBy)
	{
		$newOrderBy = array();
		foreach($orderBy as $key=>$value)
		{
			$direction = 1;	//defaut order by direction is ascending
			if(is_string($key))
			{
				$fieldName = $key;
				if((is_numeric($value) && $value < 0) || (is_string($value) && !strcasecmp($value, 'desc')))
					$direction = -1;
			}
			else
			{
				$fieldName = $value;
			}
			if(false === ($field = $this->getFieldByName($fieldName)))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong field name "{fieldName}" for sorting', array('{fieldName}'=>$fieldName)));
			if($field->type == 'foreign' && empty($field->options['select']))
				throw new AAException(Yii::t('AutoAdmin.errors', 'You should specify fields names in "select" option for foreign field "{fieldName}" to sort by it', array('{fieldName}'=>$field->name)));
			foreach($this->orderBy as $k=>$order)
			{
				if($fieldName == $order['field']->name)
				{
					unset($this->orderBy[$k]);
					break;
				}
			}
			$newOrderBy[] = array('field'=>$field, 'dir'=>$direction);
		}
		$this->orderBy = array_merge($newOrderBy, $this->orderBy);
	}

	/**
	 * For example
	 * <pre>
	 * $a->setForeignKeysLink('brands', array(
	 *			'label'			=> 'Brands',
	 *			'show'			=> true,
	 *			'linkTable'		=> 'stores_brands',
	 *			'inKey'			=> array('store_id'=>'id'),	//for single PK also correct: array('store_id')
	 *			'outKey'		=> array('brand_id'=>'id'),	//"id" is brands.id
	 *			'targetTable'	=> 'brands',
	 *			'targetFields'	=> array('title'),
	 *		));
	 * </pre>
	 * @param string $outAlias
	 * @param array $linkConf 
	 * @todo $linkConf format checking.
	 */
	public function setForeignLink($outAlias, $linkConf)
	{
		//Upgrading for possibility to work with composite keys
		if(!is_array($linkConf['inKey']))
			$linkConf['inKey'] = array($linkConf['inKey']=>$this->getPKField(0));
		if(!is_array($linkConf['outKey']) || count($this->pk) != count($linkConf['inKey']) || empty($linkConf['targetTable']) || empty($linkConf['linkTable']))
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for ForeignKey configuration'));
		$this->foreignLinks[$outAlias] = $linkConf;
	}

	/**
	 * Sets the PrimaryKey configuration from a user's script.
	 * Must be called at the beginning of a user's controller.
	 * @param string|array $pk PrimaryKey field name (or an array of name for composite PK).
	 * @return boolean
	 * @throws CException 
	 */
	public function setPK($pk)
	{
		$this->pk = array();
		if(is_string($pk))
			$this->pk[$pk] = null;
		elseif(is_array($pk))
		{
			foreach($pk as $cpk)
			{
				if(!is_string($cpk))
					throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for PrimaryKey'));
				$this->pk[$cpk] = null;
			}
		}
		else
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for PrimaryKey'));
		if($this->pk)
			return true;
	}

	/**
	 * Loads PrimaryKey values passed as an argument.
	 * Allows excess keys and values in $pkValues in order to be a more flexible tool.
	 * @param type $pkValues 
	 */
	public function loadPK($pkValues)
	{
		foreach($pkValues as $key=>$value)
		{
			if(isset($this->pk[$key]) || is_null($this->pk[$key]))
				$this->pk[$key] = $value;
		}
	}

	/**
	 * Loads PrimaryKey values passed as an argument in local copy and returns this copy. Doesn't modify $this->pk.
	 * Allows excess keys and values in $pkValues in order to be a more flexible tool.
	 * @param type $pkValues 
	 */
	public function rowPK($pkValues)
	{
		$pk = $this->pk;
		foreach($pkValues as $key=>$value)
		{
			if(isset($pk[$key]))
				$pk[$key] = $value;
		}
		return $pk;
	}

	/**
	 *
	 * @param type $k
	 * @return type 
	 */
	public function getPKField($k)
	{
		$pkFields = array_keys($this->pk);
		return isset($pkFields[$k]) ? $pkFields[$k] : null;
	}

	/**
	 * Finds a field by its name.
	 * Some types can have a value of array type. So in queries all selectable fields gets unique aliases. We may be need to search by this aliases also.
	 * Direct search by native names (AAField->name) is priority.
	 * Automatically uses caching to optimize multiple calling.
	 * @param string $fieldAlias Field name in native table.
	 * @param bool $incSelectMode Search by aliases of selectable fields.
	 * @return \AAField|bool 
	 */
	public function getFieldByName($fieldAlias, $incSelectMode=false)
	{
		if(isset($this->cacheFieldsIndexes[$fieldAlias]))
			return $this->fields[$this->cacheFieldsIndexes[$fieldAlias]];
		foreach($this->fields as $k=>$field)
		{
			if($field->name == $fieldAlias)
			{
				$this->cacheFieldsIndexes[$fieldAlias] = $k;
				return $field;
			}
		}
		if($incSelectMode)
		{
			foreach($this->fields as $k=>$field)
			{
				if(!empty($field->options['select']) && in_array($fieldAlias, $field->options['select']))
				{
					$this->cacheFieldsIndexes[$fieldAlias] = $k;
					return $field;
				}
			}
		}
		return false;
	}

	/**
	 * Loads SQL query's result to an object of AADataRow class.
	 * @param array $queryRow An associative array of data as result of the classic Yii method queryRow().
	 * @return \AADataRow An object filled with query's data.
	 * @throws CException 
	 */
	public function loadRow($queryRow)
	{
		$dataRow = new AADataRow;
		foreach($this->fields as $field)
		{
			$newField = clone $field;
			$newField->loadFromSql($queryRow);
			$dataRow->addField($newField);
		}

		//It's better to preserve the order of PKs
		foreach($this->pk as $fieldName=>$value)
		{
			if(!isset($queryRow[$fieldName]))
				throw new AAException(Yii::t('AutoAdmin.errors', 'The PrimaryKey "{fieldName}" value absents in the selection row', array('{fieldName}'=>$field->name)));
			$dataRow->addPK($fieldName, $queryRow[$fieldName]);
		}
		return $dataRow;
	}

	/**
	 * Sets the search options.
	 * @param int $byIndex The index of a field.
	 * @param mixed $searchQuery A query to search by.
	 * If an array's passed the function will search by each element of it as scalars, joining them with logical OR.
	 * Array in $searchQuery can be used only as manually set in user-defined controllers.
	 * Meta-symbols * are accepted.
	 */
	public function setSearch($byIndex, $searchQuery)
	{
		if(isset($this->fields[$byIndex]) && $this->fields[$byIndex]->options['inSearch'] && $searchQuery !== '')
		{
			$this->searchOptions = array(
				'field' => &$this->fields[$byIndex],
				'query' => $searchQuery
			);
		}
	}
}
