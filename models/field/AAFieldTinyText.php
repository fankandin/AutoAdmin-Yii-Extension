<?php
/**
 * TinyText field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldTinyText extends AAFieldString
{
	public $type='tinytext';

	public function printValue()
	{
		return AAHelperText::strip($this->value, 80);
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
			
		$value = str_replace("<p>", "", $this->value);
		$value = str_replace("</p>", "", $value);
		$value = str_replace('<br/>', "\n", $value);
		$value = AAHelperForm::prepareTextForForm($value);

		$tagOptions['id'] = $inputID;
		echo CHtml::textArea($inputName, $value, $tagOptions);

		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(!isset($formData[$this->name]))
			return;
		$this->value = str_replace("\r", "", trim($formData[$this->name]));
		$this->value = str_replace("\n", "<br/>", $this->value);
		$this->value = AAHelperForm::prepareTextForDb($this->value);
	}
}
