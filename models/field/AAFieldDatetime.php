<?php
/**
 * Datetime field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldDatetime extends AAFieldDate
{
	public $type='date';

	public function printValue()
	{
		return Yii::app()->dateFormatter->formatDateTime($this->value, 'long');
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		echo CHtml::label($this->label, "{$inputName}[d]");
		echo CHtml::tag('br');
		if($this->allowNull)
			$this->printFormNullCB();
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		$d = $this->value ? $this->value : time();	//If not defined take current date
		list($year, $month, $day) = explode('.', date('Y.m.d', $d));
		list($hour, $minute, $second) = explode(':', date("H:i:00", $d));
		?>
		<table class="time-panel"><tbody>
			<tr>
				<td class="calendar"><input type="text"/></td>
				<td>
					<?
					$days = array();
					for($j = 1; $j <= 31; $j++)
						$days[$j] = $j;
					$tagOptions['id'] = "{$inputName}[d]";
					echo CHtml::dropDownList("{$inputName}[d]", (int)$day, $days, $tagOptions);
					?>
				</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex']++;
					echo CHtml::dropDownList("{$inputName}[m]", (int)$month, Yii::app()->locale->getMonthNames(), $tagOptions);
					?>
				</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[y]", $year, $tagOptions, array_merge($tagOptions, array('maxlength'=>4, 'class'=>'i-year')));
					?>
				</td>
				<td></td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[h]";
					$tagOptions['tabindex']++;
					$tagOptions['maxlength'] = 2;
					echo CHtml::textField("{$inputName}[h]", $hour, $tagOptions);
					?>
				</td>
				<td>:</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[n]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[n]", $minute, $tagOptions);
					?>
				</td>
				<td>:</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[s]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[s]", $second, $tagOptions);
					?>
				</td>
			</tr>
		</tbody></table>
		<?

		return ob_get_clean();
	}

	public function loadFromForm($formData)
	{
		parent::loadFromForm($formData);
		//Now $this->value already contains the date part. All appropriate checks were also done.
		if(!is_null($this->value))
		{
			if(!isset($formData[$this->name]['h']) || !isset($formData[$this->name]['n']) || !isset($formData[$this->name]['s']))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
			$this->value = strtotime(date('Y-m-d', $this->value).' '.sprintf('%02d:%02d:%02d', $formData[$this->name]['h'], $formData[$this->name]['n'], $formData[$this->name]['s']));
			if(!$this->value)
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
		}
	}

	public function valueForSql()
	{
		if(!$this->value)
			return parent::valueForSql();
		return date('Y-m-d H:s:i', $this->value);
	}
}