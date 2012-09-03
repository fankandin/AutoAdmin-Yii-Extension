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

		$tagOptions['id'] = $inputID;
		if($this->isReadonly)
			$tagOptions['disabled'] = true;
		if(isset($this->options['min']))
			$tagOptions['min'] = $this->options['min'];
		if(isset($this->options['max']))
			$tagOptions['max'] = $this->options['max'];
		if(isset($this->options['pattern']))
			$tagOptions['pattern'] = $this->options['pattern'];
		if(!isset($this->options['max']) && !isset($this->options['pattern']))
			$tagOptions['maxlength'] = 10;

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
		?><div class="num-tip"><?php echo CHtml::dropDownList(null, (!is_null($this->value) ? $this->value : $this->defaultValue), $numOptions);?></div><?php

		return ob_get_clean();
	}

	public function valueForSql()
	{
		$value = parent::valueForSql();
		if(!is_numeric($this->value))
			$this->throwErrorValue();
		return $value;
	}

	public function validateValue($value)
	{
		if(!parent::validateValue($value))
			return false;
		if(isset($this->options['min']))
		{
			if(!is_numeric($this->options['min']))
				throw new AAException;
			if($value < $this->options['min'])
				return false;
		}
		if(isset($this->options['max']))
		{
			if(!is_numeric($this->options['max']))
				throw new AAException;
			if($value > $this->options['max'])
				return false;
		}
		return true;
	}
}
