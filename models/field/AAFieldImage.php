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
			return CHtml::image("{$this->options['directoryPath']}/{$this->value}");
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
				<?=Yii::t('AutoAdmin.form', '<b class="warning">Delete</b> the image')?> <span class="tip">(<?=Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>)</span>:
				<?=CHtml::checkBox("{$inputName}[del]", false, $oldOptions);?>
			</label>
			<?
		}
		$tagOptions['id'] = $inputID;
		echo CHtml::label(Yii::t('AutoAdmin.form', 'New image').':', $inputID);
		?><div class="tip inline">&lt;img src=<?=$this->options['directoryPath']?>/</div><?
		echo CHtml::fileField(AutoAdmin::INPUT_PREFIX."[{$this->name}_new]", null, $tagOptions);

		return ob_get_clean();
	}
}
