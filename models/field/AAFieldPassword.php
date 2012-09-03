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
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		$tagOptions['id'] = $inputID;
		if($this->value)
		{
			$tagOptions['value'] = 1;
			?>
			<div>
				<?php echo Yii::t('AutoAdmin.form', '<span class="warning">Replace</span> password')?> (<?php echo Yii::t('AutoAdmin.form', 'set checkbox on for confirm')?>):
				<br/><label><?php echo CHtml::checkBox("{$inputName}[is_new]", null, $tagOptions)?>&nbsp;<?php echo Yii::t('AutoAdmin.common', 'Yes')?></label>
			</div>
			<?
			echo CHtml::passwordField("{$inputName}[val]", '******', array('disabled'=>true));
		}
		else
		{
			if(!empty($this->options['pattern']))
				$tagOptions['pattern'] = $this->options['pattern'];
			if(isset($this->options['maxlength']))
				$tagOptions['maxlength'] = $this->options['maxlength'];
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

	public function valueForSql()
	{	//A password theoretically may be an empty string, so we need to hash it
		if(!is_null($this->value) && $this->value==='' && !$this->allowNull)
			$this->value = '';
		else
			return parent::valueForSql();
	}
}
