<?php
/**
 * String (varchar) field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldPassword extends AAField implements AAIField
{
	public $type='password';

	public function printValue()
	{
		return '*****';
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

		$tagOptions['id'] = $inputID;
		if($this->value)
		{
			$tagOptions['value'] = 1;
			?>
			<div>
				<?=Yii::t('AutoAdmin.form', '<span class="warning">Replace</span> password')?> (<?=Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>):
				<br/><label><?=CHtml::checkBox("{$inputName}[is_new]", null, $tagOptions)?>&nbsp;<?=Yii::t('AutoAdmin.common', 'Yes')?></label>
			</div>
			<?
			echo CHtml::passwordField("{$inputName}[val]", '******', array('disabled'=>true));
		}
		else
		{
			echo CHtml::passwordField("{$inputName}[val]", $this->value, $tagOptions);
		}

		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(!empty($formData[$this->name]['is_new']) || (!empty($formData[$this->name]['val']) && $this->isChanged))	//checking checkbox of password changing
		{
			$this->value = AAUserIdentity::hashPassword($formData[$this->name]['val']);
			$this->isChanged = true;
		}
	}
}