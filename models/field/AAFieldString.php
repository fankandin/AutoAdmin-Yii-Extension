<?php
/**
 * String (varchar) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldString extends AAField implements AAIField
{
	public $type='string';
	public $defaultValue='';

	public function printValue()
	{
		return AAHelperText::strip($this->value, 80);
	}

	public function loadFromForm($formData)
	{
		$this->value = AAHelperForm::prepareTextForDb((string)$formData[$this->name]);
	}
	
	public function valueForSql()
	{
		if($this->value==='' && $this->allowNull)
			return new CDbExpression('NULL');
		else
			return parent::valueForSql();
	}

}
