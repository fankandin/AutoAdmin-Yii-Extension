<?php
/**
 * WYSIWIG editor field.
 * Based on the TinyMCE application.
 * 
 * ATTENTION!
 * To use this class you need to install the ETinyMCE extension:
 * http://www.yiiframework.com/extension/tinymce
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldWysiwig extends AAFieldText
{
	public $type='wysiwig';

	public function formInput(&$controller, $tagOptions=array())
	{
		if(!is_dir(Yii::getPathOfAlias('application.extensions.tinymce')))
			throw new AAException(Yii::t('AutoAdmin.errors', 'The TinyMCE extension not found. Please download it from http://www.yiiframework.com/extension/tinymce and install.'));
		return $controller->widget('application.extensions.tinymce.ETinyMce', array(
				'name'				=> $this->formInputName(),
				'useSwitch'			=> false,
				'useCompression'	=>false,
				'editorTemplate'	=>'full',
				'value'				=> $this->value,
			), true);
	}

	public function loadFromForm($formData)
	{
		if(!isset($formData[$this->name]))
			return;
		$this->value = trim($formData[$this->name]);
	}
}
