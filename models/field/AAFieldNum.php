<?php
/**
 * Numeric field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldNum extends AAField implements AAIField
{
	public $type='num';

	public function completeOptions()
	{
		if(!isset($this->options['numType']))
			$this->options['numType'] = 'number';
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

		$tagOptions['id'] = $inputID;
		$tagOptions['maxlength'] = 10;
		if($this->isReadonly)
			$tagOptions['disabled'] = true;
		echo CHtml::textField($inputName, (!is_null($this->value) ? $this->value : $this->defaultValue), $tagOptions);

		switch($this->options['numType'])
		{
			case 'year':
				$nTo = (int)date('Y');
				$nFrom = $nTo-40;
				if(!isset($this->defaultValue))
					$this->defaultValue = $nTo;
				break;
			case 'tempCelsius':
				$nFrom = -15;
				$nTo = 40;
				if(!isset($this->defaultValue))
					$this->defaultValue = 0;
				break;
			case 'tempFarengeit':
				$nFrom = 14;
				$nTo = 100;
				if(!isset($this->defaultValue))
					$this->defaultValue = 50;
				break;
			case 'digit':
				$nFrom = 0;
				$nTo = 10;
				if(!isset($this->defaultValue))
					$this->defaultValue = 0;
				break;
			default:
				$nFrom = 0;
				$nTo = 36;
				if(!isset($this->defaultValue))
					$this->defaultValue = 0;
				break;
		}
		$numOptions = array();
		for($j=$nFrom; $j<=$nTo; $j++)
		{
			$numOptions[$j] = $j;
		}
		?><div class="num-tip"><?=CHtml::dropDownList(null, (!is_null($this->value) ? $this->value : $this->defaultValue), $numOptions);?></div><?

		return ob_get_clean();
	}
}
