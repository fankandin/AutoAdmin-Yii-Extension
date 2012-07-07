<?php
/**
 * Helper for text editing
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAHelperText
{
	/**
	 * Cut a string to $reqlen-3 length and add ... (ellipsis) at the end
	 * @param type $str
	 * @param type $reqlen
	 * @return string 
	 */
	public static function strip($str, $reqlen)
	{
		$str = strip_tags($str); //tags cannot be cut
		if(mb_strlen($str) < $reqlen)
		{
			return $str;
		}
		else
		{
			//mb_internal_encoding('UTF-8');
			$str2  = mb_substr($str, 0, $reqlen-3);
			$str2 .= "&hellip;";
			return $str2;
		}
	}

	/**
	 * Transliteration of russian to english
	 * @param type $st
	 * @return type 
	 */
	public static function translite($st)
	{
		$replace = array(
			"'"=>"",
			"`"=>"",
			"а"=>"a","А"=>"a",
			"б"=>"b","Б"=>"b",
			"в"=>"v","В"=>"v",
			"г"=>"g","Г"=>"g",
			"д"=>"d","Д"=>"d",
			"е"=>"e","Е"=>"e",
			"ж"=>"zh","Ж"=>"zh",
			"з"=>"z","З"=>"z",
			"и"=>"i","И"=>"i",
			"й"=>"y","Й"=>"y",
			"к"=>"k","К"=>"k",
			"л"=>"l","Л"=>"l",
			"м"=>"m","М"=>"m",
			"н"=>"n","Н"=>"n",
			"о"=>"o","О"=>"o",
			"п"=>"p","П"=>"p",
			"р"=>"r","Р"=>"r",
			"с"=>"s","С"=>"s",
			"т"=>"t","Т"=>"t",
			"у"=>"u","У"=>"u",
			"ф"=>"f","Ф"=>"f",
			"х"=>"h","Х"=>"h",
			"ц"=>"c","Ц"=>"c",
			"ч"=>"ch","Ч"=>"ch",
			"ш"=>"sh","Ш"=>"sh",
			"щ"=>"sch","Щ"=>"sch",
			"ъ"=>"","Ъ"=>"",
			"ы"=>"y","Ы"=>"y",
			"ь"=>"","Ь"=>"",
			"э"=>"e","Э"=>"e",
			"ю"=>"yu","Ю"=>"yu",
			"я"=>"ya","Я"=>"ya",
			"і"=>"i","І"=>"i",
			"ї"=>"yi","Ї"=>"yi",
			"є"=>"e","Є"=>"e"
		);
		return iconv("UTF-8","UTF-8//IGNORE",strtr($st,$replace));
	}

	/**
	 * Search the fragment in the text and mark it.
	 * Function is usually used for marking text fragments as founded after searching
	 * @param string $fragment
	 * @param string $text 
	 */
	public static function markFragment($fragment, $text)
	{
		return preg_replace('/('.preg_quote($fragment).')/ui', '<span class="marked">$1</span>', $text);
	}

	/**
	 * mb_ucfirst
	 * @param string $str 
	 */
	public static function ucfirst($str)
	{
		return mb_strtoupper(mb_substr($str, 0, 1)).mb_substr($str, 1);
	}
}
