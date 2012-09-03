<?php
/**
 * Time field.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldTime extends AAField implements AAIField
{
	public $type='time';

	public function printValue()
	{
		return $this->value;
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		echo CHtml::label($this->label, "{$inputName}[d]");
		echo CHtml::tag('br');
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		list($hour, $minute, $second) = explode(':', ($this->value ? $this->value : date("H:i:s")));
		?>
		<table class="time-panel"><tbody>
			<tr>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[h]";
					$tagOptions['tabindex']++;
					$tagOptions['maxlength'] = 4;
					echo CHtml::textField("{$inputName}[h]", $hour, $tagOptions);
					?>
				</td>
				<td>:</td>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[n]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[n]", $minute, $tagOptions);
					?>
				</td>
				<td>:</td>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[s]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[s]", $second, $tagOptions);
					?>
				</td>
			</tr>
		</tbody></table>
		<?php
		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		if(!isset($formData[$this->name])
			|| (!isset($formData[$this->name]['h']) || !isset($formData[$this->name]['n']) || !isset($formData[$this->name]['s']))
			|| ($formData[$this->name]['h']==='' || $formData[$this->name]['n']==='' || $formData[$this->name]['s']===''))
		{
			if($this->allowNull)
				$this->value = null;
			else
				throw new AAException(Yii::t('AutoAdmin.errors', 'The field "{field}" cannot be NULL but it can be passed by the form', array('{field}'=>$this->name)));
		}
		else
		{
			$this->value = sprintf('%02d:%02d:%02d', $formData[$this->name]['h'], $formData[$this->name]['n'], $formData[$this->name]['s']);
			if(!$this->value)
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
		}
	}

	public function valueForSql()
	{
		if(!$this->value)
			return parent::valueForSql();
		return $this->value;
	}

	public function validateValue($value)
	{
		if(!parent::validateValue($value))
			return false;
		list($hour, $minute, $second) = explode(':', $value);
		list($hour, $minute, $second) = array(intval($hour), intval($minute), intval($second));
		$pattern = '/(\-?\d{1,})[\-\:\.\s](\d{1,2})[\-\:\.\s](\d{1,2})/';
		if(!empty($this->options['min']))
		{
			if(!preg_match($pattern, $this->options['min'], $matches))
				throw new AAException;
			if($hour < intval($matches[1])
					|| ($hour == intval($matches[1]) && ($minute < intval($matches[2]) || ($minute == intval($matches[2]) && $second < intval($matches[3])))))
				return false;
		}
		if(!empty($this->options['max']))
		{
			if(!preg_match($pattern, $this->options['max'], $matches))
				throw new AAException;
			if($hour > intval($matches[1])
					|| ($hour == intval($matches[1]) && ($minute > intval($matches[2]) || ($minute == intval($matches[2]) && $second > intval($matches[3])))))
				return false;
		}
		elseif($minute > 59 || $second > 59)
			return false;

		return true;
	}
}