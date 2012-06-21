<?php
/**
 * Numeric field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldNum extends AAField implements AAIField
{
	public $type='num';

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();
		$inputID = "i_{$inputName}";
		echo CHtml::label($this->label, $inputID);
		echo CHtml::tag('br');
		if($this->allowNull)
			$this->printFormNullCB();

		$tagOptions['id'] = $inputID;
		$tagOptions['maxlength'] = 10;
		if($this->isReadonly)
			$tagOptions['disabled'] = true;
		echo CHtml::textField($inputName, $this->value, $tagOptions);

		$numOptions = array();
		for($j=0; $j<35; $j++)
		{
			$numOptions[] = $j;
		}
		?><div class="num-tip"><?=CHtml::dropDownList(null, ($this->value ? $this->value : $this->defaultValue), $numOptions);?></div><?

		return ob_get_clean();
	}
}
