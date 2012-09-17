<?php
/**
 * Helper for working with URL-addresses
 *
 * @author Palamarchuk A.V. <a@palamarchuk.info>
 */
class AAHelperUrl
{
	/**
	 * Encode free data into string to transmit as value of GET or POST parameter.
	 * @param mixed $data Any structure of data to encode.
	 * @return string Encoded into string data.
	 */
	public static function encodeParam($data)
	{
		return rawurlencode(str_rot13(base64_encode(serialize($data).Yii::app()->request->csrfToken)));
	}

	/**
	 * Decode data that was encoded by @method encodeParam().
	 * @param string $encoded Encoded data
	 * @return mixed Decoded data
	 */
	public static function decodeParam($encoded)
	{
		if(!$encoded)
			return null;
		try
		{
			$decoded = base64_decode(str_rot13(rawurldecode($encoded)));
			$decoded = str_replace(Yii::app()->request->csrfToken, '', $decoded);
			$decoded = unserialize($decoded);
		}
		catch(Exception $e)
		{
			throw new CHttpException(400);
		}
		return $decoded ? $decoded : false;
	}

	/**
	 * Delete the $param parameter from the $url URL
	 * @param string $url
	 * @param string|array $param
	 * @return string Updated url
	 */
	public static function stripParam($url, $param)
	{
		if(!is_array($param))
			$param = array($param);
		return self::update($url, $param);
	}
 
	/**
	 * Add the param into url
	 * @param string $url
	 * @param string $param
	 * @param mixed $value
	 * @return string Updated url
	 */
	public static function addParam($url, $param, $value)
	{
		return self::update($url, array(), array($param=>$value));
	}

	/**
	 * Replace the value of parameter with new $value in $url
	 * @param string $url
	 * @param string $param
	 * @param mixed $value
	 * @return string Updated url
	 */
	public static function replaceParam($url, $param, $value)
	{
		$url = self::stripParam($url, $param);
		$url = self::addParam($url, $param, $value);
		return $url;
	}

	/**
	 * Multi-action URL update
	 * @param string $url
	 * @param array $skipParams Параметры, от которых надо избавиться
	 * @param array $addParams
	 * @return string Updated url
	 */
	public static function update($url, $skipParams = array(), $addParams=array())
	{
		$href = explode('?', str_replace('index.php', '', $url));
		$paramPairs = !empty($href[1]) ? explode('&', $href[1]) : array();
		$qs = '';
		foreach($paramPairs as $i=>$paramPair)
		{
			$pair = explode('=', $paramPair);
			if($skipParams)
			{
				if(in_array($pair[0], $skipParams) || (preg_match('/^([^\[]+)\[/', $pair[0], $matches) && in_array($matches[1], $skipParams)))
					unset($paramPairs[$i]);
			}
		}
		if($addParams)
		{
			foreach($addParams as $param=>$values)
			{
				$pairs = array();
				self::uriParamPairs($pairs, $param, $values);
				$paramPairs = array_merge($paramPairs, $pairs);
			}
		}
		return $href[0].($paramPairs ? '?'.implode('&', $paramPairs) : '');
	}

	/**
	 * Transofrm the string of parameters into an array of kind [parameter]=>[value].
	 * @param string $uri The full URI of the current page
	 * @return array An array of kind [parameter]=>[value] extracted from the URI.
	 */
	public static function uriToParamsArray($uri)
	{
		$params = array();

		$ar = explode('?', $uri);
		if(!empty($ar[1]))
		{
			$pairs = explode('&', $ar[1]);
			foreach($pairs as $pair)
			{
				$pairAr = explode('=', $pair);
				$params[$pairAr[0]] = $pairAr[1];
			}
		}
		return $params;
	}

	/**
	 * Recursively generates an array of URI pairs 'param=value' for any type of $value including multi-dimensial arrays.
	 * @param array $pairs An array for storing result.
	 * @param string $key Param name
	 * @param mixed $value Value of free type incling array.
	 */
	private static function uriParamPairs(&$pairs, $key, $value)
	{
		if(is_array($value))
		{
			foreach($value as $ikey=>$ivalue)
				self::uriParamPairs($pairs, "{$key}[{$ikey}]", $ivalue);
		}
		else
			$pairs[] = "{$key}={$value}";
	}
}