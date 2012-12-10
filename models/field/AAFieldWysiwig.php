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
		ob_start();

		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";
		$tagOptions['id'] = $inputID;
		echo CHtml::textArea($inputName, $this->value, $tagOptions);

		$tinyMceJsPath = isset($this->options['tinyMCE']['dir']) ? $this->options['tinyMCE']['dir'] : '/js/tinymce';
		if(!Yii::app()->clientScript->isScriptFileRegistered($tinyMceJsPath.'/jscripts/tiny_mce/jquery.tinymce.js'))
		{
			if(!file_exists(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].$tinyMceJsPath.str_replace('/', DIRECTORY_SEPARATOR, '/jscripts/tiny_mce/jquery.tinymce.js')))
				throw new AAException(Yii::t('AutoAdmin.errors', 'You try to use WYSIWIG field, but the TinyMCE directory is not found. Please download the "TinyMCE jQuery package" (from http://www.tinymce.com/download/download.php) and unpack it to [/js/] directory of your DocumentRoot.'));
			Yii::app()->clientScript->registerScriptFile($tinyMceJsPath.'/jscripts/tiny_mce/jquery.tinymce.js');
		}
		//The default TinyMCE options
		$tinyMceOpts = array(
			'script_url'=>$tinyMceJsPath.'/jscripts/tiny_mce/tiny_mce.js',
			//General options
			'theme' => 'advanced',
			'plugins' => 'pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
			//Theme options
			'theme_advanced_buttons1' => 'save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect',
			'theme_advanced_buttons2' => 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor',
			'theme_advanced_buttons3' => 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
			'theme_advanced_buttons4' => 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak',
			'theme_advanced_toolbar_location' => 'top',
			'theme_advanced_toolbar_align' => 'left',
			'theme_advanced_statusbar_location' => 'bottom',
			'theme_advanced_resizing' => true,
		);
		//If the current language is presented in the TinyMCE distributive, use it
		list($lang,) = explode('_', Yii::app()->language);
		if(file_exists(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].$tinyMceJsPath.str_replace('/', DIRECTORY_SEPARATOR, '/jscripts/tiny_mce/langs/'.$lang.'.js')))
			$tinyMceOpts['language'] = $lang;
		//A user can override any TinyMCE options
		if(isset($this->options['tinyMCE']['options']) && is_array(($this->options['tinyMCE']['options'])))
			$tinyMceOpts = array_merge($tinyMceOpts, $this->options['tinyMCE']['options']);

		Yii::app()->clientScript->registerScript("js_{$inputID}", '$(function() {$(\'[id="'.$inputID.'"]\').tinymce('.CJSON::encode($tinyMceOpts).');});');
		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(!isset($formData[$this->name]))
			return;
		$this->value = trim($formData[$this->name]);
	}
}
