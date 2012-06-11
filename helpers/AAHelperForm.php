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
		//$text = str_replace('&quot;", '"', $text);
		return $text;
	}

	public static function prepareTextForDb($text)
	{
		$text = trim($text);
		$text = str_replace('<<', '&laquo;', $text);
		$text = str_replace('>>', '&raquo;', $text);
		$text = str_replace('--', '&mdash;', $text);
		$text = str_replace('...', '&hellip;', $text);
		$text = str_replace('..', '&nbsp;', $text);
		$text = preg_replace('/\=\"([^\"\s]*)\"/i', '={quot}$1{quot}', $text);
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace('{quot}', '"', $text);
		//$text = str_replace ("'", '&prime;', $text);
		return $text;
	}
}
