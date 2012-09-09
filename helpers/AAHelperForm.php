<?php
/**
 * Хелпер для работы с формой редактирования
 *
 * @author Alexander Palamarchuk
 */
class AAHelperForm
{
	public static function prepareTextForForm($text)
	{
		if(!$text)
			return $text;
		$text = str_replace('&laquo;', '<<', $text);
		$text = str_replace('&raquo;', '>>', $text);
		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&gt;', '>', $text);
		$text = str_replace('&mdash;', '--', $text);
		$text = str_replace('&hellip;', '...', $text);
		$text = str_replace('&nbsp;', '..', $text);
		$text = str_replace('&prime;', "'", $text);
		$text = str_replace('&quot;', '"', $text);
		return $text;
	}

	public static function prepareTextForDb($text)
	{
		$text = trim($text);
		$text = preg_replace('~<script.+?</script>~iu', '', $text);
		$text = str_replace('<<', '&laquo;', $text);
		$text = str_replace('>>', '&raquo;', $text);
		$text = str_replace('--', '&mdash;', $text);
		$text = str_replace('...', '&hellip;', $text);
		$text = str_replace('..', '&nbsp;', $text);
		$text = preg_replace('~\=\"([^\"]*)\"~iu', '={quot}$1{quot}', $text);
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace('{quot}', '"', $text);
		//$text = str_replace ("'", '&prime;', $text);
		return $text;
	}

	/**
	 * The shell-function for Yii::app()->request->getParam(). Can be used to access easily to multidimensional parameters.
	 * @param string|array $param If string, the function works just as an alias. But an array is considered as a list of parameters by its levels ordered by numeric keys.
	 * @param mixed $defaultValue Default value, if parameter doesn't exists.
	 */
	public static function getParam($param, $defaultValue=null)
	{
		if(is_array($param))
		{
			$nLevels = count($param);
			if($nLevels == 1)
				return Yii::app()->request->getParam($param[0], $defaultValue);
			$ar = Yii::app()->request->getParam($param[0]);
			if(is_null($ar) || !is_array($ar))
				return $defaultValue;
			for($i=1; $i<$nLevels-1; $i++)
			{
				if(is_null($ar[$param[$i]]) || !is_array($ar[$param[$i]]))
					return $defaultValue;
				else
					$ar = $ar[$param[$i]];
			}
			return (is_null($ar[$param[$nLevels-1]]) || !is_array($ar[$param[$nLevels-1]])) ? $defaultValue : $ar[$param[$nLevels-1]];
		}
		else
			return Yii::app()->request->getParam($param, $defaultValue);
	}
}
