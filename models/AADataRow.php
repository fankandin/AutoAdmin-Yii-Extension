<?php
/**
 * Description of AADataRow
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AADataRow implements Iterator
{
	public $pk;
	public $fields = array();

	private $fieldsPosition = 0;

	/**
	 *
	 * @param type $field 
	 */
	public function addField($field)
	{
		$this->fields[] = $field;
	}

	/**
	 *
	 * @param type $fieldName
	 * @param type $value 
	 */
	public function addPK($fieldName, $value)
	{
		$this->pk[$fieldName] = $value;
	}

	public function getByName($fieldName)
	{
		foreach($this->fields as $field)
		{
			if($field->name == $fieldName)
				return $field;
		}
		return false;
	}

	/**
	 * Iterator methods
	 */

	public function current()
	{
		return $this->fields[$this->fieldsPosition];
	}

	public function key()
	{
		return $this->fieldsPosition;
	}

	public function next()
	{
		++$this->fieldsPosition;
	}

	public function rewind()
	{
		$this->fieldsPosition = 0;
	}

	public function valid()
	{
		return isset($this->fields[$this->fieldsPosition]);
	}
}
