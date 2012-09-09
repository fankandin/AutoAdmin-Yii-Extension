<?php
/**
 * Date field
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAFieldDate extends AAField implements AAIField
{
	public $type='date';

	public function printValue()
	{
		if($this->value->format('Y') >= 1970)	//We can use Yii datetime formatter
			return Yii::app()->dateFormatter->formatDateTime($this->value->getTimestamp(), 'long', null);
		else
			return $this->value->format('Y.m.d');
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		echo CHtml::label($this->label, "{$inputName}[d]");
		echo CHtml::tag('br');
		if($this->isReadonly)
			$tagOptions['disabled'] = true;

		$d = $this->value ? $this->value : new DateTime();	//If not defined take current date
		list($year, $month, $day) = explode('.', $d->format('Y.m.d'));
		?>
		<table class="time-panel"><tbody>
			<tr>
				<td class="calendar"><input type="text"/>
				<?php
				if(!empty($this->options['min']))
					echo CHtml::tag('span', array('class'=>'mindate'), $this->options['min']);
				if(!empty($this->options['max']))
					echo CHtml::tag('span', array('class'=>'maxdate'), $this->options['max']);
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
					$tagOptions['id'] = "{$inputName}[y]";
					$tagOptions['tabindex']++;
					echo CHtml::textField("{$inputName}[y]", $year, $tagOptions, array_merge($tagOptions, array('maxlength'=>4, 'class'=>'i-year')));
					?>
				</td>
			</tr>
		</tbody></table>
		<?php

		return ob_get_clean();
	}
	
	public function loadFromForm($formData)
	{
		if(!isset($formData[$this->name]))
		{
			if($this->allowNull)
				$this->value = null;
			else
				throw new AAException(Yii::t('AutoAdmin.errors', 'The field "{field}" cannot be NULL but it can be passed by the form', array('{field}'=>$this->name)));
		}
		else
		{
			if(!isset($formData[$this->name]['y']) || !isset($formData[$this->name]['m']) || !isset($formData[$this->name]['d']))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
			try
			{
				$this->value = new DateTime(sprintf("%04d-%02d-%02d", $formData[$this->name]['y'], $formData[$this->name]['m'], $formData[$this->name]['d']));
			}
			catch(AAException $e)
			{
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
			}
		}
	}

	public function loadFromSql($queryValue)
	{
		if(isset($queryValue[$this->name]))
			$this->value = new DateTime($queryValue[$this->name]);
	}

	public function valueForSql()
	{
		if(!$this->value)
			return parent::valueForSql();
		return $this->value->format('Y-m-d');
	}

	public function validateValue($value)
	{
		if(!parent::validateValue($value))
			return false;
		if(!empty($this->options['min']))
		{	
			try
			{
				$dMin = new DateTime($this->options['min']);
			}
			catch (AAException $e)
			{
				throw new AAException;
			}
			if($value < $dMin)
				return false;
		}
		if(!empty($this->options['max']))
		{
			try
			{
				$dMax = new DateTime($this->options['max']);
			}
			catch (AAException $e)
			{
				throw new AAException;
			}
			if($value > $dMax)
				return false;
		}
		return true;
	}

}
