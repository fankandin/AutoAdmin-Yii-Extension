<?php
/**
 * File (uploading) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldFile extends AAField implements AAIField
{
	public $type='file';

	public function testOptions()
	{
		if(empty($this->options['directoryPath']))
			return false;
		return true;
	}

	public function printValue()
	{
		if($this->value)
		{
			$ext = mb_substr(mb_strrchr($this->value, '.'), 1);
			if(!$ext)
				$ext = '';
			$spanOptions = array('class'=>'file'.($ext ? " ext-{$ext}" : ''));
			if(in_array($ext, array('jpg', 'gif', 'png')))
				return CHtml::link($this->value, "{$this->options['directoryPath']}/{$this->value}", $spanOptions);
			else
				return CHtml::tag('span', $spanOptions, $this->value);
		}
		else
			return null;
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

		if($this->value)
		{
			echo CHtml::textField("{$inputName}[old]", $this->value, array('readonly'=>true));
			?>
			<label class="delfile">
				<?=Yii::t('AutoAdmin.form', '<b class="warning">Delete</b> the file')?> <span class="tip">(<?=Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>)</span>:
				<?=CHtml::checkBox("{$inputName}[del]", false);?>
			</label>
			<?
		}
		$tagOptions['id'] = $inputID;
		echo CHtml::label(Yii::t('AutoAdmin.form', 'New file').':', $inputID);
		?><div class="tip inline">&lt;a href=<?=$this->options['directoryPath']?>/</div><?
		echo CHtml::fileField("{$inputName}[new]", null, $tagOptions);

		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(is_array($formData[$this->name]) && !empty($formData[$this->name]['del']))
		{
			//AAHelperFile::deleteFile($formData[$this->name]['old']);
			$this->value = null;
		}
		elseif(!empty($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name'][$this->name]['new']))
		{
			$this->value = $this->copyUploadedFile();
		}
	}

	/**
	 * Copy an image uploaded with HTML form to the specified directory.
	 * In view scripts one should use something like <img src="{$fileBaseDir}/{@return}"/>
	 * @return string A part of file path that should to be written in DB.
	 * @throws CException 
	 */
	public function copyUploadedFile()
	{
		if(empty($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name'][$this->name]['new']) || !empty($_FILES[AutoAdmin::INPUT_PREFIX]['error'][$this->name]['new']))
			throw new AAException(Yii::t('AutoAdmin.errors', 'An error occured with uploading of the file for field "{field}"', array('{field}'=>$this->name)));
		$uploadedFileName =& $_FILES[AutoAdmin::INPUT_PREFIX]['name'][$this->name]['new'];
		//A base directory constant for the file (should be used in view scripts as prefix before $filePath from DB table).
		$fileBaseDir = $this->options['directoryPath'];
		//The variable part of file path which is put to DB right as a part.
		$fileCustomDir = date('Ym').DIRECTORY_SEPARATOR.strtolower(preg_replace('/[^a-z0-9\-]/', '-', $this->name));

		$newfname = '';
		$fdir = Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $fileBaseDir);
		$newfname = mb_strtolower(mb_substr($uploadedFileName, 0, mb_strrpos($uploadedFileName, '.')));
		$newfname = AAHelperText::translite($newfname);
		$newfname = str_replace(' ', '_', $newfname);
		$newfname = preg_replace('/[^a-z\-\_0-9]/ui', '', $newfname);
		if(mb_strlen($newfname)>60)
			$newfname = mb_substr($newfname, 0, 60);
		$ext = mb_substr(mb_strrchr($uploadedFileName, '.'), 1);
		$toDir = $fdir.DIRECTORY_SEPARATOR.$fileCustomDir;
		if(!is_dir($toDir))
		{
			mkdir($toDir, 0777, true);
		}
		$i = 0;
		while(file_exists($toDir.DIRECTORY_SEPARATOR.$newfname.'.'.$ext))
		{
			$newfname .= '_'.++$i;
		}
		if(!copy($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name'][$this->name]['new'], $toDir.DIRECTORY_SEPARATOR.$newfname.'.'.$ext))
			throw new AAException(Yii::t('AutoAdmin.errors', 'The file ({filename}) cannot be copied', array('{filename}'=>"{$newfname}.{$ext}")));
		return str_replace('\\', '/', $fileCustomDir.DIRECTORY_SEPARATOR.$newfname.'.'.$ext);
	}

}
