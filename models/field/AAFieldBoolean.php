<?php
/**
 * Boolean (checkbox) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldBoolean extends AAField implements AAIField
{
	public $type='boolean';

	public function printValue()
	{
		return CHtml::checkBox('cb[]', ($this->value ? true : null), array('disabled'=>true));
	}

	public function completeOptions()
	{
		if(!isset($this->options['strictType']))
			$this->options['strictType'] = true;	//If set true, [TRUE/FALSE] will be used directly in queries. Some DB don't understand such syntax.
		else
			$this->options['strictType'] = (bool)$this->options['strictType'];
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";
		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		$tagOptions['id'] = $inputID;
		echo CHtml::checkBox($inputName, (bool)(isset($this->value) ? $this->value : $this->defaultValue), $tagOptions);

		return ob_get_clean();
	}


	public function loadFromForm($formData)
	{
		$this->value = (bool)(!empty($formData[$this->name]));
	}

	public function valueForSql()
	{
		if($this->options['strictType'])
			return new CDbExpression(($this->value ? 'TRUE' : 'FALSE'));
		else
			return ($this->value ? 1 : 0);
	}
}
