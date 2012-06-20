<?php
/**
 * String (varchar) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldForeign extends AAField implements AAIField
{
	const defaultSelectLimit = 100;
	public $type = 'foreign';
	public $value;

	public function completeOptions()
	{
		$this->options['foreignValues'] = array();

		if(empty($this->options['tableAlias']))
		{
			$tableNameParts = explode('.', $this->options['table']);
			$this->options['tableAlias'] = $tableNameParts[count($tableNameParts)-1].'_'.mb_substr(md5(serialize($this)), 0, 5);
		}
		if(empty($this->options['select']))
			$this->options['select'] = array();
		else
		{	//Setting aliases for fields
			$select = array();
			foreach($this->options['select'] as $fieldName)
			{
				$select[$fieldName] = "{$this->options['tableAlias']}_$fieldName";
			}
			$this->options['select'] = $select;
		}
		if(!isset($this->options['limit']))
			$this->options['limit'] = self::defaultSelectLimit;
	}

	public function testOptions()
	{
		if(empty($this->options['table']) || empty($this->options['pk']))
			return false;
		return true;
	}

	public function printValue()
	{
		if(empty($this->options['select']))
			return '';

		if(is_array($this->options['foreignValues']))
		{
			$pValues = array();
			foreach($this->options['foreignValues'] as $value)
			{
				$pValues[] = CHtml::tag('span', array(), $value);
			}
			return implode(' ', $pValues);
		}
		else
			return $this->value;
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";

		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');
		if($this->allowNull)
			$this->printFormNullCB();
		$tagOptions['id'] = $inputID;
		$value = (!is_null($this->value) ? $this->value : $this->defaultValue);

		if(!$this->isReadonly && $this->getPossibleValuesCount() <= $this->options['limit'])
		{
			echo CHtml::dropDownList($inputName, $value, $this->getOptValues(), $tagOptions);
		}
		else
		{
			echo CHtml::dropDownList('', $value, array($this->getTitleByFields($this->selectDefault())), array('disabled'=>true));
			echo CHtml::hiddenField($inputName, $value);
			if(!$this->isReadonly && !empty($this->options['searchBy']))
			{
				$options = array();
				foreach($this->options['searchBy'] as $field=>$label)
				{
					$options[$field] = $label;
				}
				?>
				<div class="foreign-search">
					<?=CHtml::label('', "foreignSearchQ_{$this->name}");?>
					<?=CHtml::dropDownList(null, null, $options, array('id'=>"foreignSearchBy_{$this->name}"));?>:
					<?
					//Searching the ForeignKey value
					$controller->widget('zii.widgets.jui.CJuiAutoComplete', array(
							'name'		=> '',
							'id'		=> "foreignSearchQ_{$this->name}",
							'source'	=> 'js: foreignKeyQuery',
							'value'		=> '',
							'options'	=> array(
								'minLength'=>1,
								'delay'	=> 700,
								'select'=>'js: foreignKeySelected',
								//these are the user defined variables for multi-params query
								'sourceUrl'	=> Yii::app()->request->baseUrl.'/aaajax/foreignkey/',
								'extraParams' => array('keyData'=>AAHelperUrl::encodeParam($this)),
							),
						));
					?>
				</div>
				<?
			}
		}
		
		return ob_get_clean();
	}

	public function loadFromSql($queryValue)
	{
		if(isset($queryValue[$this->name]))
			$this->value = $queryValue[$this->name];
		foreach($this->options['select'] as $selectField)
		{
			if(isset($queryValue[$selectField]))
				$this->options['foreignValues'][] = $queryValue[$selectField];
		}
	}

	/**
	 * Prepares SELECT and JOIN parts for the base SQL query.
	 * @return array|false Data in internal format. Will be used to modificate SQL query.
	 */
	public function modifySqlQuery()
	{
		//Joining tables by foreign key (one to many relation)
		if($this->options['select'])
		{	//There is no sense to join anything for overall data list if select fields of foreign table was not set
			$modifySql = array(
				'select' => array(),
				'join' => array(),
			);
			foreach($this->options['select'] as $fieldName=>$fieldAlias)
			{
				$modifySql['select'][] = "{$this->options['tableAlias']}.{$fieldName} AS {$fieldAlias}";
			}
			$modifySql['join']['table'] = "{$this->options['table']} AS {$this->options['tableAlias']}";
			if(!empty($this->options['dbName']))	//Foreign table is in another DB
				$modifySql['join']['table'] = "{$this->options['dbName']}.{$modifySql['join']['table']}";
			$modifySql['join']['type'] = $this->allowNull ? 'left' : 'inner';
			$modifySql['join']['conditions'] = array('AND', "{$this->tableName}.{$this->name} = {$this->options['tableAlias']}.{$this->options['pk']}");
			if(!empty($this->options['conditions']))
			{
				$conditions = $this->options['conditions'];
				AutoAdmin::addTableAliasToCond($conditions, $this->options['table'], $this->options['tableAlias']);
				$modifySql['join']['conditions'][] = $conditions;
				if(!empty($this->options['params']))
					$modifySql['join']['params'] = $this->options['params'];
			}
			return $modifySql;
		}
		return false;
	}

	/**
	 * Gets the number of possible values to select.
	 * @return int The number of possible values.
	 */
	private function getPossibleValuesCount()
	{
		$q = Yii::app()->db->createCommand();
		$q->from($this->options['table']);
		$q->select(new CDbExpression('COUNT(*)'));
		$qWhere = array();
		$qParams = array();
		if(!empty($this->options['conditions']))
		{
			$qWhere = array('AND', $this->options['conditions']);
			if(!empty($this->options['params']))
				$qParams = array_merge($qParams, $this->options['params']);
			$q->where($qWhere, $qParams);
		}
		return (int)$q->queryScalar();
	}

	/**
	 * Selects data by $this->value as PK.
	 * @return array A row of data.
	 */
	private function selectDefault()
	{
		$q = Yii::app()->db->createCommand();
		$q->from($this->options['table']);
		if($this->options['select'])
			$q->select(array_merge(array($this->options['pk']), array_keys($this->options['select'])));
		$q->where("{$this->options['pk']} = :pk", array(':pk'=>(!is_null($this->value) ? $this->value : $this->defaultValue)));
		return $q->queryRow();
	}

	/**
	 * Converts the select row to a title.
	 * @param array $row Select row.
	 * @return string Title for <OPTION> or any alike.
	 */
	private function getTitleByFields($row)
	{
		return ($row && is_array($row) ? implode(' - ', $row) : '');
	}

	/**
	 * Gets a set of values for <select> from DB directly.
	 * @return array An array of prepared values, ready for dropDownList().
	 */
	private function getOptValues()
	{
		$optValues = array();
		$q = Yii::app()->db->createCommand();
		$q->from($this->options['table']);
		if($this->options['select'])
			$q->select(array_merge(array($this->options['pk']), array_keys($this->options['select'])));

		$qWhere = array();
		$qParams = array();
		if(!empty($this->options['conditions']))
		{
			$qWhere = array('AND', $this->options['conditions']);
			if(!empty($this->options['params']))
				$qParams = array_merge($qParams, $this->options['params']);
		}
		if($qWhere)
			$q->where($qWhere, $qParams);

		if(!empty($this->options['order']))
			$q->order($this->options['order']);
		elseif($this->options['select'])
		{
			$fieldNames = array_keys($this->options['select']);
			$q->order($fieldNames[0]);
		}
		$result = $q->queryAll();
		foreach($result as $r)
		{
			if($this->options['select'])
			{
				$values = $r;
				unset($values[$this->options['pk']]);
			}
			elseif(count($r) == 1)
			{
				$values = $r;
			}
			else
			{
				$i = 0;
				$values = array();
				foreach($r as $field=>$value)
				{
					if($field == $this->options['pk'])
						continue;
					$values[$field] = $value;
					if(++$i > 2)
						break;
				}
			}
			$optValues[$r[$this->options['pk']]] = $this->getTitleByFields($values);
		}
		return $optValues;
	}
}
