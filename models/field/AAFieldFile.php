<?php
/**
 * File (uploading) field
 *
 * Options:
 *	[directoryPath]		Relative (from wwwDir) path to a directory where files must be uploaded to.
 *	[subDirectoryPath]	Relative from [directoryPath] path to a directory where files must be uploaded to. Unlike [directoryPath] the [subDirectoryPath] will be added to the value. It's may be useful for dynamicaly created directories.
 *		So in views you should use [directoryPath]/[$this->value] in src="".
 * 
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldFile extends AAField implements AAIField
{
	public $type='file';

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
		if($this->value)
		{
			$ext = mb_substr(mb_strrchr($this->value, '.'), 1);
			if(!$ext)
				$ext = '';
			$linkOpts = array('class'=>'file'.($ext ? " ext-{$ext}" : ''));
			$pubDir = $this->options['directoryPath'];
			if($this->options['subDirectoryPath'])
				$pubDir .= '/'.$this->options['subDirectoryPath'];
			$html = CHtml::link("{$pubDir}/{$this->value}", "{$pubDir}/{$this->value}", $linkOpts);
			$html .= CHtml::tag('span', array('class'=>'select', 'title'=>Yii::t('AutoAdmin.form', 'Press <Ctrl-C> / <Cmd-C> to copy')), '');
			$fileSrc = AAHelperFile::srcToPath("{$pubDir}/{$this->value}");
			if(!file_exists($fileSrc))
				$html .= CHtml::tag('span', array('class'=>'error', 'title'=>Yii::t('AutoAdmin.form', 'The file does not exist')));
			else
				$html .= CHtml::tag('span', array('class'=>'size'), sprintf('(%s&nbsp;MB)', round(filesize($fileSrc)/1024/1024, 2)));
			return $html;
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
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		if($this->value)
		{
			$oldOptions = array('readonly'=>true);
			if($this->isReadonly)
				$tagOptions['disabled'] = true;
			echo CHtml::textField("{$inputName}[old]", $this->value, $oldOptions);
			unset($oldOptions['readonly']);
			?>
			<label class="delfile">
				<?php echo Yii::t('AutoAdmin.form', '<b class="warning">Delete</b> the file')?> <span class="tip">(<?php echo Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>)</span>:
				<?php echo CHtml::checkBox("{$inputName}[del]", false, $oldOptions);?>
			</label>
			<?php
		}
		$tagOptions['id'] = $inputID;
		echo CHtml::label(Yii::t('AutoAdmin.form', 'New file').':', $inputID);
		?>
		<div class="tip inline">&lt;a href=<?php echo $this->options['directoryPath']?>/</div>
		<?php
		echo CHtml::fileField(AutoAdmin::INPUT_PREFIX."[{$this->name}_new]", null, $tagOptions);

		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(!empty($formData[$this->name]) && is_array($formData[$this->name]) && !empty($formData[$this->name]['del']))
		{
			$this->value = null;
		}
		elseif(!empty($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name']["{$this->name}_new"]))
		{
			$uploadDir = $this->options['directoryPath'];
			if($this->options['subDirectoryPath'])
				$uploadDir .= '/'.$this->options['subDirectoryPath'];
			$this->value = ($this->options['subDirectoryPath'] ? $this->options['subDirectoryPath'].'/' : '').AAHelperFile::uploadFile("{$this->name}_new", $uploadDir);
		}
	}
}
