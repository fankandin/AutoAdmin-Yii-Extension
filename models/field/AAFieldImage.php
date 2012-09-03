<?php
/**
 * Image (uploading) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldImage extends AAFieldFile
{
	public $type='image';

	public function printValue()
	{
		if($this->value)
		{
			if(!empty($this->options['popup']))
				return CHtml::link(Yii::t('AutoAdmin.form', 'Popup image'), "{$this->options['directoryPath']}/{$this->value}", array('rel'=>"lightbox[{$this->name}]"));
			else
				return CHtml::image("{$this->options['directoryPath']}/{$this->value}");
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
			echo CHtml::image("{$this->options['directoryPath']}/{$this->value}", $this->label, array('title'=>$this->label));
			echo CHtml::textField($inputName, $this->value, $oldOptions);
			unset($oldOptions['readonly']);
			?>
			<label class="delfile">
				<?php echo Yii::t('AutoAdmin.form', '<b class="warning">Delete</b> the image')?> <span class="tip">(<?php echo Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>)</span>:
				<?php echo CHtml::checkBox("{$inputName}[del]", false, $oldOptions)?>
			</label>
			<?php
		}
		$tagOptions['id'] = $inputID;
		echo CHtml::label(Yii::t('AutoAdmin.form', 'New image').':', $inputID);
		?>
		<div class="tip inline">&lt;img src=<?php echo $this->options['directoryPath']?>/</div>
		<?php
		echo CHtml::fileField(AutoAdmin::INPUT_PREFIX."[{$this->name}_new]", null, $tagOptions);

		return ob_get_clean();
	}
}
