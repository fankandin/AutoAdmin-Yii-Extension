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
		return Yii::app()->dateFormatter->formatDateTime($this->value, 'long', null);
	}

	public function formInput(&$controller, $tagOptions=array())
	{
		ob_start();
		$inputName = $this->formInputName();

		echo CHtml::label($this->label, "{$inputName}[d]");
		echo CHtml::tag('br');
		if($this->allowNull)
			$this->printFormNullCB();

		$d = $this->value ? $this->value : time();	//If not defined take current date
		list($year, $month, $day) = explode('.', date('Y.m.d', $d));
		?>
		<table class="time-panel"><tbody>
			<tr>
				<td>
					<?
					$days = array();
					for($j = 1; $j <= 31; $j++)
						$days[$j] = $j;
					$tagOptions['id'] = "{$inputName}[d]";
					$tagOptions['tabindex'] = $tabindex++;
					echo CHtml::dropDownList("{$inputName}[d]", (int)$day, $days, $tagOptions);
					?>
				</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex'] = $tabindex++;
					echo CHtml::dropDownList("{$inputName}[m]", (int)$month, Yii::app()->locale->getMonthNames(), $tagOptions);
					?>
				</td>
				<td>
					<?
					$tagOptions['id'] = "{$inputName}[m]";
					$tagOptions['tabindex'] = $tabindex++;
					CHtml::textField("{$inputName}[y]", $year, $tagOptions, array_merge($tagOptions, array('maxlength'=>4, 'class'=>'i-year')));
					?>
				</td>
			</tr>
		</tbody></table>
		<?

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
			$this->value = strtotime(sprintf("%04d-%02d-%02d", $formData[$this->name]['y'], $formData[$this->name]['m'], $formData[$this->name]['d']));
			if(!$this->value)
				throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data was passed for the field "{field}"', array('{field}'=>$this->name)));
		}
	}

	public function loadFromSql($queryValue)
	{
		if(isset($queryValue[$this->name]))
			$this->value = strtotime($queryValue[$this->name]);
	}

	public function valueForSql()
	{
		if(!$this->value)
			return parent::valueForSql();
		return date('Y-m-d', $this->value);
	}
}
