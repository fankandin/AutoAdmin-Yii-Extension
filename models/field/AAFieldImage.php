<?php
/**
 * Image (uploading) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldImage extends AAFieldFile
{
	public $type='image';

	public function testOptions()
	{
		if(empty($this->options['directoryPath']))
			return false;
		return true;
	}

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

		if($this->value)
		{
			echo CHtml::image("{$this->options['directoryPath']}/{$this->value}", $this->label, array('title'=>$this->label));
			echo CHtml::textField($inputName, $this->value, array('readonly'=>true));
			?>
			<label class="delfile">
				<?=Yii::t('AutoAdmin.form', '<b class="warning">Delete</b> the image')?> <span class="tip">(<?=Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>)</span>:
				<?=CHtml::checkBox("{$inputName}[del]", false);?>
			</label>
			<?
		}
		else
		{
			echo CHtml::label(Yii::t('AutoAdmin.form', 'Replace the image').':', $inputID);
		}
		$tagOptions['id'] = $inputID;
		?><div class="tip inline">&lt;img src=<?=$this->options['directoryPath']?>/</div><?
		echo CHtml::fileField("{$inputName}[new]", null, $tagOptions);

		return ob_get_clean();
	}
}
