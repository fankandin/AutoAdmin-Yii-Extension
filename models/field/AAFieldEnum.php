<?php
/**
 * Enum field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldEnum extends AAField implements AAIField
{
	public $type='enum';

	public function completeOptions()
	{
		if(!isset($this->options['enum']))
			throw new AAException(Yii::t('AutoAdmin.errors', 'The parameter "{paramName}" must be set for the field {fieldName}', array('{paramName}'=>'enum', '{fieldName}'=>$this->name)));
		$this->options['enumValues'] = $this->options['enum'];
	}

	public function testOptions()
	{
		if(empty($this->options['enumValues']))
			return false;
		return true;
	}

	public function printValue()
	{
		return $this->options['enumValues'][$this->value];
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		$inputID = "i_{$inputName}";
		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');

		$tagOptions['id'] = $inputID;
		$valueOptions = $this->options['enumValues'];
		if($this->allowNull)
			$valueOptions = array(''=>'') + $valueOptions;
		echo CHtml::dropDownList($inputName, ($this->value ? $this->value : $this->defaultValue), $valueOptions, $tagOptions);

		return ob_get_clean();
	}
	
	public function valueForSql()
	{
		$value = parent::valueForSql();
		if(is_string($value) && !isset($this->options['enumValues'][$value]))
			$this->throwErrorValue();
		return $value;
	}
}
