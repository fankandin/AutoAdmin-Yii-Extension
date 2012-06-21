<?php
/**
 * Text field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldText extends AAFieldString
{
	public $type='text';

	public function printValue()
	{
		return AAHelperText::strip($this->value, 80);
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		if(!Yii::app()->clientScript->isScriptFileRegistered(AutoAdmin::$assetPathJS.'/text-editor.js'))
			Yii::app()->clientScript->registerScriptFile(AutoAdmin::$assetPathJS.'/text-editor.js');

		ob_start();
		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";
		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');
		if($this->allowNull)
			$this->printFormNullCB();
		
		$value = str_replace('<br/>', "\n", $this->value);
		$value = AAHelperForm::prepareTextForForm($value);
		$value = str_replace("<p>", "", $value);
		$value = str_replace("</p>", "", $value);

		$tagOptions['id'] = $inputID;
		if($this->isReadonly)
			$tagOptions['disabled'] = true;
		else
		{
			echo CHtml::button('STRONG');
			echo CHtml::button('EM');
			echo CHtml::button('H3');
			echo CHtml::button('H4');
			echo CHtml::button('UL');
			echo CHtml::button('OL');
			echo CHtml::button('Link');
			echo CHtml::button('MailTo');
			echo CHtml::button('Img');
			echo CHtml::button('<..>');
		}
		echo CHtml::textArea($inputName, $value, $tagOptions);
		
		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		$inputName = $this->formInputName();

		if(!isset($formData[$this->name]))
			return;

		$this->value = str_replace("\r", "", trim($formData[$this->name]));
		if($this->value)
			$this->value = "<p>".str_replace("\n\n", "</p>\n\n<p>", $this->value)."</p>";	//transform line breaks into paragraphs
		$this->value = AAHelperForm::prepareTextForDb($this->value);
		$this->value = preg_replace("/>\n(<|\w)/ui", "><br/>\n\\1", $this->value);
		$this->value = preg_replace("/<p>(<h[0-9]>.*?<\/h[0-9]>)<br\/>\n(\w)/ui", "\\1\n<p>\\2", $this->value);
		$this->value = preg_replace("/<p>(<h[0-9]>.*?<\/h[0-9]>)<\/p>(<br\/>)?/i", "\\1", $this->value);
		$this->value = preg_replace("/<p>\n*((<ol)|(<ul)|(<li)|(<div)|(<table))/i", "\\1", $this->value);
		$this->value = preg_replace("/((<\/li>)|(<\/ol>)|(<\/ul>)|(<\/div>)|(<\/table>)|(<\/h[0-9]>))\n*((<\/p>)|(<br\/>))/i", "\\1", $this->value);
		$this->value = preg_replace("/<p><\/div>/i", "</div>", $this->value);	//crutch :(
		$this->value = preg_replace("/(<div[^>]*>)<\/p>/i", "\\1", $this->value);
	}
}
