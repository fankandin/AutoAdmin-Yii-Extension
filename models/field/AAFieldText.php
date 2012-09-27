<?php
/**
 * Text field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldText extends AAFieldString
{
	public $type='text';

	public function completeOptions()
	{
		if(!isset($this->options['directoryPath']))
			throw new AAException(Yii::t('AutoAdmin.errors', 'The parameter "{paramName}" must be set for the field {fieldName}', array('{paramName}'=>'directoryPath', '{fieldName}'=>$this->name)));
		$this->options['directoryPath'] = rtrim($this->options['directoryPath'], '/');
		$this->options['subDirectoryPath'] = isset($this->options['subDirectoryPath']) ? rtrim($this->options['subDirectoryPath'], '/') : '';
	}

	public function testOptions()
	{
		if(empty($this->options['directoryPath']))
			return false;
		return true;
	}

	public function printValue()
	{
		return AAHelperText::strip($this->value, 80);
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		if(!Yii::app()->clientScript->isScriptFileRegistered(AutoAdmin::$assetPath.'/js/text-editor.js'))
			Yii::app()->clientScript->registerScriptFile(AutoAdmin::$assetPath.'/js/text-editor.js');

		ob_start();
		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";
		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');
		
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
		if(!isset($formData[$this->name]))
			return;

		$this->value = str_replace("\r", "", trim($formData[$this->name]));
		if($this->value)
			$this->value = "<p>".str_replace("\n\n", "</p>\n\n<p>", $this->value)."</p>";	//transform line breaks into paragraphs
		$this->value = AAHelperForm::prepareTextForDb($this->value);
		$this->value = preg_replace("~>\n(<|\w)~ui", "><br/>\n\\1", $this->value);

		$notInParagraphTags = array('ol', 'ul', 'li', 'div', 'table', 'code', 'cite', 'thead', 'tbody', 'tr', 'td', 'pre', 'h[0-9]');
		$this->value = preg_replace("~<p>(<h[0-9]>.*?</h[0-9]>)<br\s*/?>\n(\w)~usi", "\\1\n<p>\\2", $this->value);
		$this->value = preg_replace("~<p>(<h[0-9]>.*?</h[0-9]>)</p>(<br\s*/?>)?~is", "\\1", $this->value);
		$this->value = preg_replace("~<p>\n*((<".implode(')|(<', $notInParagraphTags)."))~is", "\\1", $this->value);
		$this->value = preg_replace("~((</".implode('>)|(</', $notInParagraphTags).">)|(</h[0-9]>))\n*((</p>)|(<br\s*/?>))~is", "\\1", $this->value);
		$this->value = preg_replace("~<p>((</".implode('>)|(</', $notInParagraphTags)."))~i", "\\1", $this->value);	//crutch :(
		$this->value = preg_replace("~((<".implode('[^>]*>)|(<', $notInParagraphTags)."[^>]*>))</p>~i", "\\1", $this->value);
		$this->value = preg_replace("~((<".implode('[^>]*>)|(<', $notInParagraphTags)."[^>]*>))<br\s*/?>~i", "\\1", $this->value);
		$this->value = preg_replace_callback(
				'~(<(code)|(pre)[^>]*>)(.*?)(</(code)|(pre)>)~is', 
				create_function(
						'$matches',
						'return $matches[1].preg_replace("~(</?p>)|(<br\s*/?>)~i", "", $matches[4]).$matches[5];'
				),
				$this->value
		);
	}
	
	public function valueForSql()
	{
		if(!is_null($this->value) && $this->value==='' && !$this->allowNull)
			$this->value = '';	//In case of string we do not throw an exception, we can use '' as value
		else
			return parent::valueForSql();
	}
}
