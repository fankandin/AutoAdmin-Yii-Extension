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
		if($this->value->format('Y') >= 1970)	//We can use Yii datetime formatter
			return Yii::app()->dateFormatter->formatDateTime($this->value->getTimestamp(), 'long', null);
		else
			return $this->value->format('Y.m.d H:i:s');
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		echo CHtml::label($this->label, "{$inputName}[d]");
		echo CHtml::tag('br');
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		$d = $this->value ? $this->value : new DateTime();	//If not defined take current datetime
		list($year, $month, $day) = explode('.', $d->format('Y.m.d'));
		list($hour, $minute, $second) = explode(':', $d->format('H:i:00'));
		?>
		<table class="time-panel"><tbody>
			<tr>
				<td class="calendar"><input type="text"/>
				<?php
				if(!empty($this->options['min']))
					echo CHtml::tag('span', array('class'=>'mindate'), strtotime($this->options['min']));
				if(!empty($this->options['max']))
					echo CHtml::tag('span', array('class'=>'maxdate'), strtotime($this->options['max']));
				?>
				</td>
				<td>
					<?php
					$days = array();
					for($j = 1; $j <= 31; $j++)
						$days[$j] = $j;
					$tagOptions['id'] = "{$inputName}[d]";
					echo CHtml::dropDownList("{$inputName}[d]", (int)$day, $days, $tagOptions);
					?>
				</td>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex']++;
					echo CHtml::dropDownList("{$inputName}[m]", (int)$month, Yii::app()->locale->getMonthNames(), $tagOptions);
					?>
				</td>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[y]", $year, $tagOptions, array_merge($tagOptions, array('maxlength'=>4, 'class'=>'i-year')));
					?>
				</td>
				<td></td>
				<td>
					<?php
					$tagOptions['id'] = "{$inputName}[h]";
					$tagOptions['tabindex']++;
					$tagOptions['maxlength'] = 2;
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
		parent::loadFromForm($formData);
		//Now $this->value already contains the date part. All appropriate checks were also done.
		if(!is_null($this->value))
		{
			if(!isset($formData[$this->name]['h']) || !isset($formData[$this->name]['n']) || !isset($formData[$this->name]['s']))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
			try
			{
				$this->value = new DateTime(sprintf("%04d-%02d-%02d", $formData[$this->name]['y'], $formData[$this->name]['m'], $formData[$this->name]['d']));
				$this->value->setTime($formData[$this->name]['h'], $formData[$this->name]['n'], $formData[$this->name]['s']);
			}
			catch(AAException $e)
			{
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
			}
		}
	}

	public function valueForSql()
	{
		if(!$this->value)
			return parent::valueForSql();
		return $this->value->format('Y-m-d H:s:i');
	}
}